<?php

require_once "../model/users_action_functions.php";

/** Append readable history to the existing current-record export. */
function appendUserActionSheets(\PhpOffice\PhpSpreadsheet\Spreadsheet $workbook, $userType): void
{
    $admin = (int)$userType === 1;
    $sheets = [];
    $columns = [];
    $nextRows = [];
    $labels = [
        'user_id' => 'User ID', 'userID' => 'Account ID', 'username' => 'Account Username',
        'userType' => 'Account Role', 'password_changed' => 'Password Changed', 'logo_uploaded' => 'Logo Uploaded', 'no_LPB' => 'No LPB',
        'nomor_surat_jalan' => 'No SJ', 'no_sj' => 'No SJ', 'no_invoice' => 'No Invoice',
        'no_faktur' => 'No Faktur', 'no_moving' => 'No Moving', 'no_repack' => 'No Repack',
        'payment_id' => 'Payment ID', 'storageCode' => 'Storage Code',
        'storageCodeSender' => 'Sender Storage', 'storageCodeReceiver' => 'Receiver Storage',
        'vendorCode' => 'Vendor Code', 'customerCode' => 'Customer Code',
        'no_truk' => 'Truck Number', 'order_date' => 'Slip Date', 'invoice_date' => 'Invoice Date',
        'payment_date' => 'Payment Date', 'moving_date' => 'Moving Date', 'repack_date' => 'Repack Date',
        'purchase_order' => 'Purchase Order', 'status_mode' => 'Slip Mode',
        'productCode' => 'Product Code', 'productName' => 'Product Name', 'qty' => 'Quantity',
        'UOM' => 'UOM', 'price_per_UOM' => 'Price per UOM', 'payment_amount' => 'Payment Amount',
        'tax' => 'Tax (%)', 'note' => 'Note', 'product_status' => 'Product Group',
        'moving_no_moving' => 'No Moving', 'repack_no_repack' => 'No Repack',
    ];
    $label = static function (string $key) use ($labels): string {
        return $labels[$key] ?? ucwords(str_replace('_', ' ', preg_replace('/([a-z])([A-Z])/', '$1 $2', $key)));
    };
    // Explicit text cells preserve IDs, leading zeroes and literal text beginning with '='.
    $append = static function (string $name, array $row) use ($workbook, &$sheets, &$columns, &$nextRows): void {
        if (!isset($sheets[$name])) {
            $sheets[$name] = $workbook->createSheet()->setTitle($name);
            $columns[$name] = [];
            $nextRows[$name] = 2;
        }
        $sheet = $sheets[$name];
        $number = $nextRows[$name]++;
        if ($number > 1048576) throw new RuntimeException('Action history exceeds the Excel row limit.');
        foreach ($row as $heading => $value) {
            if (!isset($columns[$name][$heading])) {
                $index = count($columns[$name]) + 1;
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
                $columns[$name][$heading] = $column;
                $sheet->setCellValueExplicit($column . '1', $heading, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->getColumnDimension($column)->setWidth(in_array($heading, ['Time', 'Reference', 'Performed By ID', 'Account ID', 'Payment ID'], true) ? 30 : 22);
            }
            $cell = $columns[$name][$heading] . $number;
            $sheet->setCellValueExplicit($cell, (string)($value ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
    };
    $append('History Guide', [
        'Topic' => 'Current records', 'Explanation' => 'The original sheets contain current database records. History sheets contain saved user actions.'
    ]);
    $append('History Guide', ['Topic' => 'Before / After', 'Explanation' => 'CREATE has After; UPDATE has Before and After; DELETE has Before. Match rows using Action ID and Snapshot.']);
    $append('History Guide', ['Topic' => 'Products and related records', 'Explanation' => 'Product History contains product rows. Related History retains linked records and says whether they were removed or only captured as context.']);
    $append('History Guide', ['Topic' => 'Amounts', 'Explanation' => 'Amounts are exported as exact text to preserve database precision. Before/After history is not a financial total; do not sum it as a balance.']);
    $append('History Guide', ['Topic' => 'Views', 'Explanation' => 'Page opened and Report generated are separate events. Filters are shown as ordinary columns.']);
    $append('History Guide', ['Topic' => 'Master Data', 'Explanation' => 'Vendor, Customer, Master Product, Storage and User Account History contain CREATE, UPDATE and DELETE snapshots. Master Changes lists changed fields side by side. Performed By identifies the employee; Account Username identifies the affected account. Password values are never exported.']);
    $append('History Guide', ['Topic' => 'Coverage', 'Explanation' => 'History starts when logging was enabled. Earlier actions cannot be reconstructed from current records.']);
    $append('User Actions', ['Action ID' => '', 'Time' => '', 'Performed By ID' => '', 'Performed By' => '', 'Action' => '', 'Document Type' => '', 'Reference Type' => '', 'Reference' => '', 'Event' => '', 'Month' => '', 'Year' => '', 'Storage Code' => '']);
    // Leave a headers-only overview when there are no actions.
    $sheets['User Actions']->removeRow(2);
    $nextRows['User Actions'] = 2;

    $lastId = '0';
    // A fixed upper bound avoids chasing actions arriving while the file is built.
    $maximum = usersActionFetchRows('SELECT COALESCE(MAX(action_id), 0) AS id FROM users_action')[0]['id'];
    do {
        $events = usersActionFetchRows('SELECT * FROM users_action WHERE action_id > ? AND action_id <= ? ORDER BY action_id LIMIT 500', [$lastId, $maximum]);
        foreach ($events as $raw) {
            $lastId = $raw['action_id'];
            if (!$admin && !in_array($raw['document_type'], ['slip_in', 'slip_out', 'slip_tax', 'repack', 'moving', 'storage', 'master_vendor', 'master_customer', 'master_product', 'master_storage'], true)) continue;
            $event = decodeUserAction($raw);
            if ($event['document_type'] === 'master_user') {
                foreach (['before_data', 'after_data'] as $phaseKey) {
                    if (isset($event[$phaseKey]['header'])) {
                        $event[$phaseKey]['header'] = array_intersect_key($event[$phaseKey]['header'], array_flip([
                            'userID', 'username', 'userType', 'password_changed'
                        ]));
                    }
                }
            }
            $context = [
                'Action ID' => $event['action_id'], 'Time' => $event['performed_at'],
                'Performed By ID' => $event['user_id'], 'Performed By' => $event['username'],
                'Action' => $event['action'], 'Document Type' => $event['document_type'],
                'Reference Type' => $label($event['reference_type']), 'Reference' => $event['reference_value'],
            ];
            $view = $event['action'] === 'VIEW' ? ($event['after_data'] ?? []) : [];
            $filters = $view['filters'] ?? [];
            $append('User Actions', $context + [
                'Event' => ['page_open' => 'Page opened', 'report_generated' => 'Report generated'][$view['event'] ?? ''] ?? '',
                'Month' => $filters['month'] ?? '', 'Year' => $filters['year'] ?? '', 'Storage Code' => $filters['storageCode'] ?? '',
            ]);
            if (str_starts_with($event['document_type'], 'master_')) {
                $oldHeader = $event['before_data']['header'] ?? [];
                $newHeader = $event['after_data']['header'] ?? [];
                $changed = false;
                foreach (array_unique(array_merge(array_keys($oldHeader), array_keys($newHeader))) as $key) {
                    $hasOld = array_key_exists($key, $oldHeader);
                    $hasNew = array_key_exists($key, $newHeader);
                    $oldValue = $oldHeader[$key] ?? null;
                    $newValue = $newHeader[$key] ?? null;
                    if ($event['action'] === 'UPDATE' && $hasOld === $hasNew && $oldValue === $newValue) continue;
                    $append('Master Changes', $context + [
                        'Field' => $label($key),
                        'Before' => !$hasOld ? '(not present)' : ($oldValue === null ? '(null)' : $oldValue),
                        'After' => !$hasNew ? '(not present)' : ($newValue === null ? '(null)' : $newValue),
                    ]);
                    $changed = true;
                }
                if (!$changed) {
                    $append('Master Changes', $context + ['Field' => '(no recorded field changes)', 'Before' => '', 'After' => '']);
                }
            }
            foreach (['before_data' => 'Before', 'after_data' => 'After'] as $field => $phase) {
                $snapshot = $event[$field] ?? null;
                if (!is_array($snapshot) || !isset($snapshot['header'])) continue;
                $base = $context + ['Snapshot' => $phase];
                $sheetName = [
                    'master_vendor' => 'Vendor History', 'master_customer' => 'Customer History',
                    'master_product' => 'Master Product History', 'master_storage' => 'Storage History',
                    'master_user' => 'User Account History',
                    'slip_in' => 'Slip History', 'slip_out' => 'Slip History', 'slip_tax' => 'Slip History',
                    'invoice' => 'Invoice History', 'payment' => 'Payment History',
                    'repack' => 'Repack History', 'moving' => 'Moving History',
                ][$event['document_type']] ?? 'Document History';
                $header = [];
                foreach ($snapshot['header'] as $key => $value) $header[$label($key)] = $value;
                $append($sheetName, $base + $header);
                foreach ($snapshot['products'] ?? [] as $product) {
                    $row = [];
                    foreach ($product as $key => $value) {
                        if (!$admin && $key === 'price_per_UOM') continue;
                        $row[$label($key)] = $value;
                    }
                    $append('Product History', $base + $row);
                }
                if ($admin) {
                    foreach ($snapshot['related_records'] ?? [] as $kind => $records) {
                        foreach ($records as $record) {
                            $row = [];
                            foreach ($record as $key => $value) $row[$label($key)] = $value;
                            $removed = $event['action'] === 'DELETE' && in_array($kind, $snapshot['deleted_related_records'] ?? [], true);
                            $append('Related History', $base + ['Related Type' => ucfirst($kind), 'Relationship' => $removed ? 'Deleted with document' : 'Snapshot context'] + $row);
                        }
                    }
                    if (isset($snapshot['source_document']) && is_array($snapshot['source_document'])) {
                        $row = [];
                        foreach ($snapshot['source_document'] as $key => $value) $row[$label($key)] = $value;
                        $append('Related History', $base + ['Related Type' => 'Source document', 'Relationship' => 'Snapshot context'] + $row);
                    }
                }
            }
        }
    } while (count($events) === 500);
    foreach ($sheets as $name => $sheet) {
        $end = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns[$name]));
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:' . $end . max(1, $nextRows[$name] - 1));
        $sheet->getStyle('A1:' . $end . '1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1:' . $end . '1')->getFill()->setFillType('solid')->getStartColor()->setRGB('24476B');
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setVertical('top')->setWrapText(true);
    }
    $sheets['History Guide']->getColumnDimension('B')->setWidth(100);
    $workbook->setActiveSheetIndex($workbook->getIndex($sheets['User Actions']));
}
