# website gudang

- in (slip, invoice, payment)

- out (slip, invoice, payment)
- out tax (slip tax, invoice tax, payment tax)

- repack (slip, invoice)
- moving (slip, invoice)

- report stock
- report hutang
- report piutang

(SELECT 
            p.productCode, 
            p.productName,
            o.storageCode, 
            MONTH(i.invoice_date) AS saldoMonth, 
            YEAR(i.invoice_date) AS saldoYear, 
            SUM(op.qty) AS totalQty, 
            AVG(op.price_per_UOM) AS avgPrice,
            op.product_status
        FROM
            products p
        JOIN 
            order_products op ON p.productCode = op.productCode
        JOIN 
            orders o ON op.nomor_surat_jalan = o.nomor_surat_jalan
        JOIN 
            invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
        WHERE 
            o.storageCode = "APA"
            AND MONTH(i.invoice_date) = 8
            AND YEAR(i.invoice_date) = 2024
            AND op.product_status != "out"
        GROUP BY 
            p.productCode, 
            p.productName,
            o.storageCode, 
            saldoMonth, 
            saldoYear,
            op.product_status
    )
    UNION ALL
    (
        SELECT 
            p.productCode, 
            p.productName,
            r.storageCode, 
            MONTH(r.repack_date) AS saldoMonth, 
            YEAR(r.repack_date) AS saldoYear, 
            SUM(op.qty) AS totalQty, 
            AVG(op.price_per_UOM) AS avgPrice,
            op.product_status
        FROM
            products p
        JOIN 
            order_products op ON p.productCode = op.productCode
        JOIN 
            repacks r ON op.repack_no_repack = r.no_repack
        WHERE
            r.storageCode = "APA"
            AND MONTH(r.repack_date) = 8
            AND YEAR(r.repack_date) = 2024
        GROUP BY 
            p.productCode, 
            p.productName,
            r.storageCode, 
            saldoMonth, 
            saldoYear,
            op.product_status
    )
    ORDER BY
        CASE 
            WHEN product_status = "in" THEN 1
            WHEN product_status = "out_tax" THEN 2
            WHEN product_status = "repack_awal" THEN 3
            WHEN product_status = "repack_akhir" THEN 4
            ELSE 5
        END



<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="setHref(this)" value=<?php echo $key["nomor_surat_jalan"]; ?>>
                    DELETE
                    </button>

delete from invoices WHERE nomor_surat_jalan = "h000001"; 
insert INTO invoices VALUES ("h000001", "2024-07-23", "INV01", "11.111.111.100-1221"); 