<?php

require_once __DIR__ . '/database.php';

/**
 * Allowed document types and the references that can identify their database rows.
 * LPB is the employee-facing identifier; no_sj is the orders table's actual key.
 * Supply both invoice number and no_sj/no_moving if an invoice number is ambiguous.
 */
function usersActionDocumentMap(): array
{
    return [
        'slip_in' => ['table' => 'orders', 'references' => ['no_LPB' => 'no_LPB', 'no_sj' => 'nomor_surat_jalan'], 'mode' => 1],
        'slip_out' => ['table' => 'orders', 'references' => ['no_sj' => 'nomor_surat_jalan'], 'mode' => 2],
        'slip_tax' => ['table' => 'orders', 'references' => ['no_sj' => 'nomor_surat_jalan'], 'mode' => 3],
        'invoice' => ['table' => 'invoices', 'references' => ['no_invoice' => 'no_invoice', 'no_sj' => 'nomor_surat_jalan', 'no_moving' => 'no_moving']],
        'payment' => ['table' => 'payments', 'references' => ['payment_id' => 'payment_id']],
        'repack' => ['table' => 'repacks', 'references' => ['no_repack' => 'no_repack']],
        'moving' => ['table' => 'movings', 'references' => ['no_moving' => 'no_moving']],
    ];
}

/** Preserve decimal strings and fail loudly rather than store incomplete JSON. */
function usersActionEncode(?array $data): ?string
{
    return $data === null ? null : json_encode(
        $data,
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION
    );
}

/**
 * Append one audit entry and return its action_id.
 *
 * Identity is taken only from the authenticated session, never form fields.
 * CREATE/UPDATE/DELETE must run in the SAME transaction as the document change.
 * This function does not begin, commit, or roll back a transaction and does not
 * swallow failures. The caller must roll back if recording the audit entry fails.
 *
 * CREATE requires afterData; UPDATE requires both; DELETE requires beforeData.
 * VIEW/EXPORT may supply snapshots, but never claim a failed write succeeded.
 * Only pass document data: never passwords, TOTP secrets, or recovery codes.
 *
 * Related references example: ['no_sj' => 'SUPPLIER-123', 'no_invoice' => 'INV-100'].
 * For renamed references, preserve the old/new values in the snapshots as well.
 */
function createUserAction(
    string $action,
    string $documentType,
    string $referenceType,
    string $referenceValue,
    ?array $beforeData = null,
    ?array $afterData = null,
    array $relatedReferences = []
): string {
    global $db;

    $action = strtoupper(trim($action));
    if (!in_array($action, ['CREATE', 'UPDATE', 'DELETE', 'VIEW', 'EXPORT'], true)) {
        throw new InvalidArgumentException('Unsupported user action.');
    }
    $map = usersActionDocumentMap();
    $isReportView = $action === 'VIEW' && $referenceType === 'page'
        && in_array($documentType, ['storage', 'debts', 'receivables'], true);
    if (!$isReportView && !isset($map[$documentType]['references'][$referenceType])) {
        throw new InvalidArgumentException('Unsupported document type or reference type.');
    }
    if (trim($referenceValue) === '' || $referenceValue === '-' || mb_strlen($referenceValue) > 100) {
        throw new InvalidArgumentException('A valid document reference of at most 100 characters is required.');
    }

    $userId = $_SESSION['userID'] ?? null;
    $username = $_SESSION['username'] ?? null;
    if (!is_string($userId) || trim($userId) === '' || strlen($userId) > 36
        || !is_string($username) || trim($username) === '' || mb_strlen($username) > 100) {
        throw new LogicException('An authenticated user is required to record an action.');
    }
    if (in_array($action, ['CREATE', 'UPDATE', 'DELETE'], true) && !$db->inTransaction()) {
        throw new LogicException('Save the document change and audit entry in one transaction.');
    }
    if (($action === 'CREATE' && ($afterData === null || $beforeData !== null))
        || ($action === 'UPDATE' && ($beforeData === null || $afterData === null))
        || ($action === 'DELETE' && ($beforeData === null || $afterData !== null))) {
        throw new InvalidArgumentException('Snapshots do not match the requested action.');
    }

    $statement = $db->prepare(
        'INSERT INTO users_action
         (user_id, username, action, document_type, reference_type, reference_value,
          related_references, before_data, after_data)
         VALUES (:user_id, :username, :action, :document_type, :reference_type,
                 :reference_value, :related_references, :before_data, :after_data)'
    );
    $statement->execute([
        ':user_id' => $userId,
        ':username' => $username,
        ':action' => $action,
        ':document_type' => $documentType,
        ':reference_type' => $referenceType,
        ':reference_value' => $referenceValue,
        ':related_references' => usersActionEncode($relatedReferences ?: null),
        ':before_data' => usersActionEncode($beforeData),
        ':after_data' => usersActionEncode($afterData),
    ]);
    return $db->lastInsertId();
}

/**
 * Internal query helper. SQL identifiers used by snapshot queries are chosen
 * only from the fixed map above; supplied reference values are always bound.
 */
function usersActionFetchRows(string $sql, array $parameters = [], bool $forUpdate = false): array
{
    global $db;
    if ($forUpdate && !$db->inTransaction()) {
        throw new LogicException('Locking a snapshot requires an active transaction.');
    }
    $statement = $db->prepare($sql . ($forUpdate ? ' FOR UPDATE' : ''));
    $statement->execute($parameters);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Capture a document while it still exists, including its current product
 * names, quantities, UOM, prices, notes, and product_status (awal/akhir).
 *
 * Examples:
 * getUserActionSnapshot('slip_in', ['no_sj' => $supplierSJ], true);
 * getUserActionSnapshot('invoice', ['no_sj' => $noSJ, 'no_invoice' => $invoice], true);
 * getUserActionSnapshot('payment', ['payment_id' => $paymentId], true);
 *
 * Use forUpdate=true inside the write transaction BEFORE changing/deleting.
 * Take the AFTER snapshot after all header and product changes, before commit.
 * Returns null for a missing document; refuses an ambiguous match.
 *
 * related_records preserves dependent invoices/payments as context, including
 * those a slip/moving deletion may remove. Their presence alone does not mean
 * they were changed: the later operation integration determines what changed.
 */
function getUserActionSnapshot(string $documentType, array $references, bool $forUpdate = false): ?array
{
    $map = usersActionDocumentMap();
    if (!isset($map[$documentType]) || !$references) {
        throw new InvalidArgumentException('A supported document type and reference are required.');
    }
    $definition = $map[$documentType];
    $where = [];
    $values = [];
    foreach ($references as $type => $value) {
        if (!isset($definition['references'][$type]) || !is_string($value)
            || trim($value) === '' || $value === '-') {
            throw new InvalidArgumentException('Invalid document reference.');
        }
        $where[] = $definition['references'][$type] . ' = ?';
        $values[] = $value;
    }
    if (isset($definition['mode'])) {
        $where[] = 'status_mode = ?';
        $values[] = $definition['mode'];
    }
    $rows = usersActionFetchRows(
        'SELECT * FROM ' . $definition['table'] . ' WHERE ' . implode(' AND ', $where),
        $values, $forUpdate
    );
    if (!$rows) return null;
    if (count($rows) !== 1) {
        throw new LogicException('Reference matches multiple records. Supply an additional document reference.');
    }
    $header = $rows[0];
    $snapshot = [
        'schema_version' => 1,
        'document_type' => $documentType,
        'references' => [],
        'header' => $header,
        'products' => [],
        'related_records' => [],
    ];
    foreach ($definition['references'] as $type => $column) {
        if (isset($header[$column]) && $header[$column] !== '-' && $header[$column] !== '') {
            $snapshot['references'][$type] = $header[$column];
        }
    }

    if ($documentType === 'repack') {
        $productColumn = 'repack_no_repack';
        $documentColumn = null;
        $number = $header['no_repack'];
    } elseif ($documentType === 'moving' || (!empty($header['no_moving']) && $header['no_moving'] !== '-')) {
        $productColumn = 'moving_no_moving';
        $documentColumn = 'no_moving';
        $number = $header['no_moving'];
    } else {
        $productColumn = 'nomor_surat_jalan';
        $documentColumn = 'nomor_surat_jalan';
        $number = $header['nomor_surat_jalan'] ?? null;
    }
    // Never load the placeholder "-" rows as if they belonged to a document.
    if (!is_string($number) || $number === '' || $number === '-') {
        throw new LogicException('The document has no valid product reference.');
    }
    $snapshot['products'] = usersActionFetchRows(
        'SELECT op.*, p.productName FROM order_products op
         LEFT JOIN products p ON p.productCode = op.productCode
         WHERE op.' . $productColumn . ' = ?
         ORDER BY op.product_status, op.productCode',
        [$number], $forUpdate
    );

    if ($documentColumn !== null) {
        // Slip/moving snapshots retain every dependent financial record.
        if ($documentType !== 'invoice') {
            $snapshot['related_records']['invoices'] = usersActionFetchRows(
                'SELECT * FROM invoices WHERE ' . $documentColumn . ' = ? ORDER BY no_invoice, invoice_date',
                [$number], $forUpdate
            );
        }
        if ($documentType !== 'payment') {
            $snapshot['related_records']['payments'] = usersActionFetchRows(
                'SELECT * FROM payments WHERE ' . $documentColumn . ' = ? ORDER BY payment_date, payment_id',
                [$number], $forUpdate
            );
        }
        // Financial events also retain the source slip/moving header.
        if (in_array($documentType, ['invoice', 'payment'], true)) {
            $sourceTable = $documentColumn === 'no_moving' ? 'movings' : 'orders';
            $sourceRows = usersActionFetchRows(
                'SELECT * FROM ' . $sourceTable . ' WHERE ' . $documentColumn . ' = ?',
                [$number], $forUpdate
            );
            $snapshot['source_document'] = $sourceRows[0] ?? null;
        }
    }
    return $snapshot;
}

/** Record the persisted CREATE snapshot; the controller owns the transaction. */
function recordCreatedUserAction(string $documentType, array $references, string $referenceType): string
{
    $snapshot = getUserActionSnapshot($documentType, $references, true);
    if ($snapshot === null || !isset($snapshot['references'][$referenceType])) {
        throw new LogicException('Cannot log a missing created document.');
    }
    $related = $snapshot['references'];
    unset($related[$referenceType]);
    foreach (['nomor_surat_jalan' => 'no_sj', 'no_moving' => 'no_moving'] as $column => $type) {
        $value = $snapshot['header'][$column] ?? null;
        if (is_string($value) && $value !== '' && $value !== '-' && $type !== $referenceType) {
            $related[$type] = $value;
        }
    }
    return createUserAction('CREATE', $documentType, $referenceType,
        $snapshot['references'][$referenceType], null, $snapshot, $related);
}

/** Decode saved JSON for use by PHP/Excel without querying today's documents. */
function decodeUserAction(array $row): array
{
    foreach (['related_references', 'before_data', 'after_data'] as $column) {
        if (isset($row[$column]) && is_string($row[$column])) {
            $row[$column] = json_decode($row[$column], true, 512, JSON_THROW_ON_ERROR);
        }
    }
    return $row;
}

/**
 * Internal filter builder shared by history listing and counting.
 * from/until are action timestamps: from is inclusive, until is exclusive.
 * Example: from=2026-09-01 00:00:00, until=2026-10-01 00:00:00.
 * reference filters the MAIN reference; other identifiers remain in snapshots.
 */
function usersActionFilter(array $filters): array
{
    $columns = ['user_id', 'action', 'document_type', 'reference_type', 'reference_value'];
    $where = [];
    $values = [];
    foreach ($filters as $field => $value) {
        if (!in_array($field, array_merge($columns, ['from', 'until']), true)) {
            throw new InvalidArgumentException('Unsupported history filter: ' . $field);
        }
        if ($value === null || $value === '') continue;
        if (!is_string($value)) throw new InvalidArgumentException('History filters must be strings.');
        if ($field === 'from' || $field === 'until') {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);
            if (!$date || $date->format('Y-m-d H:i:s') !== $value) {
                throw new InvalidArgumentException('Use Y-m-d H:i:s for action timestamps.');
            }
            $where[] = 'performed_at ' . ($field === 'from' ? '>=' : '<') . ' ?';
        } else {
            $where[] = $field . ' = ?';
            if ($field === 'action') $value = strtoupper($value);
        }
        $values[] = $value;
    }
    return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $values];
}

/**
 * Read a page of history, newest first. The controller must authorize access
 * before calling this function (including any financial-data restrictions).
 * The export can request successive pages rather than load all history at once.
 */
function getUserActions(array $filters = [], int $limit = 100, int $offset = 0): array
{
    if ($limit < 1 || $limit > 1000 || $offset < 0) {
        throw new InvalidArgumentException('Use a limit from 1 to 1000 and a nonnegative offset.');
    }
    [$where, $values] = usersActionFilter($filters);
    $rows = usersActionFetchRows(
        'SELECT * FROM users_action' . $where . ' ORDER BY performed_at DESC, action_id DESC'
        . ' LIMIT ' . $limit . ' OFFSET ' . $offset,
        $values
    );
    return array_map('decodeUserAction', $rows);
}

function countUserActions(array $filters = []): int
{
    [$where, $values] = usersActionFilter($filters);
    $rows = usersActionFetchRows('SELECT COUNT(*) AS total FROM users_action' . $where, $values);
    return (int)$rows[0]['total'];
}

/** Read one saved event, including snapshots of records that no longer exist. */
function getUserActionById(string $actionId): ?array
{
    if (!ctype_digit($actionId) || trim($actionId, '0') === '') {
        throw new InvalidArgumentException('A positive action ID is required.');
    }
    $rows = usersActionFetchRows('SELECT * FROM users_action WHERE action_id = ?', [$actionId]);
    return $rows ? decodeUserAction($rows[0]) : null;
}

/** Log a page opening or generated report, without copying the entire report. */
function recordReportView(string $type, string $route, array $filters = [], string $event = 'page_open'): string
{
    return createUserAction('VIEW', $type, 'page', $route, null, [
        'schema_version' => 1,
        'event' => $event,
        'filters' => $filters,
    ]);
}
