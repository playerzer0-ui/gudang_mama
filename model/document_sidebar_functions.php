<?php

/**
 * Document numbers for the invoice/payment picker, matching inventory-system.
 */
function getDocumentSidebarNumbers(string $state, string $page): array
{
    global $db;
    $modes = ['in' => 1, 'out' => 2, 'out_tax' => 3];
    if (!isset($modes[$state])) return [];

    if ($page === 'invoice') {
        $sql = "SELECT o.nomor_surat_jalan FROM orders o
                WHERE o.status_mode = :mode AND o.nomor_surat_jalan <> '-'";
        // Incoming invoices retain the reference application's full slip list.
        if ($state !== 'in') {
            $sql .= " AND NOT EXISTS (
                SELECT 1 FROM invoices i WHERE i.nomor_surat_jalan = o.nomor_surat_jalan
            )";
        }
        $sql .= ' ORDER BY o.order_date DESC, o.nomor_surat_jalan DESC';
        $statement = $db->prepare($sql);
        $statement->execute([':mode' => $modes[$state]]);
    } elseif ($page === 'payment') {
        $filters = [
            'in' => "nomor_surat_jalan NOT LIKE '%SJK%' AND nomor_surat_jalan NOT LIKE '%SJT%'",
            'out' => "nomor_surat_jalan LIKE '%SJK%'",
            'out_tax' => "nomor_surat_jalan LIKE '%SJT%'",
        ];
        $statement = $db->prepare("SELECT DISTINCT nomor_surat_jalan FROM invoices
            WHERE nomor_surat_jalan <> '-' AND " . $filters[$state] . "
            ORDER BY nomor_surat_jalan DESC");
        $statement->execute();
    } else {
        return [];
    }
    return $statement->fetchAll(PDO::FETCH_COLUMN);
}
