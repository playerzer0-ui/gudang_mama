# website gudang

- in (slip, invoice, payment)

- out (slip, invoice, payment)
- out tax (slip tax, invoice tax, payment tax)

- repack (slip, invoice)
- moving (slip, invoice)

- report stock
- report hutang
- report piutang

function getLPB(){
    let storageCodeEl = document.getElementById('storage').value;
    let noLPBEl = document.getElementById('no_lpb');

    $.ajax({
        type: "get",
        url: "../controller/index.php?action=generate_LPB&storageCode=" + storageCodeEl,
        success: function (response) {
            noLPBEl.value = response;
            console.log(response);
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}

<?php if($pageState == "in"){ ?>
<?php } else { ?>
<?php } ?>

1/SJK/NON/07/2024
SELECT o.nomor_surat_jalan, o.storageCode, o.no_LPB, no_truk, o.vendorCode, o.customerCode, c.customerName, c.customerAddress, c.customerNPWP, o.order_date, o.purchase_order, o.status_mode FROM orders o, customers c
WHERE o.customerCode = c.customerCode
AND o.nomor_surat_jalan = "1/SJK/NON/07/2024";

harga repack untuk stock report:
repack awal = penerimaan
repack akhir = penjualan

h/qty = (total barang masuk + saldo awal) / qty total

moving
h/qty = (total barang masuk + saldo awal) / qty total

SELECT 
    p.productCode, 
    o.storageCode, 
    MONTH(i.invoice_date) AS saldoMonth, 
    YEAR(i.invoice_date) AS saldoYear, 
    op.qty AS totalQty, 
    op.price_per_UOM AS avg_price_per_qty,
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
    AND MONTH(i.invoice_date) = 7
    AND YEAR(i.invoice_date) = 2024
    AND op.product_status != "out"

UNION ALL

SELECT 
    p.productCode, 
    r.storageCode, 
    MONTH(r.repack_date) AS saldoMonth, 
    YEAR(r.repack_date) AS saldoYear, 
    op.qty AS totalQty, 
    op.price_per_UOM AS avg_price_per_qty,
    op.product_status
FROM
    products p
    JOIN order_products op ON p.productCode = op.productCode
    JOIN repacks r ON op.repack_no_repack = r.no_repack
WHERE
    r.storageCode = "APA"
    AND MONTH(r.repack_date) = 7
    AND YEAR(r.repack_date) = 2024

UNION ALL

SELECT 
    p.productCode, 
    m.storageCodeSender AS storageCode, 
    MONTH(m.moving_date) AS saldoMonth, 
    YEAR(m.moving_date) AS saldoYear, 
    op.qty AS totalQty, 
    op.price_per_UOM AS avg_price_per_qty,
    op.product_status
FROM
    products p
JOIN 
    order_products op ON p.productCode = op.productCode
JOIN 
    movings m ON op.moving_no_moving = m.no_moving
JOIN 
    invoices i ON m.no_moving = i.nomor_surat_jalan
WHERE 
    m.storageCodeSender = "APA"
    AND MONTH(m.moving_date) = 7
    AND YEAR(m.moving_date) = 2024

GROUP BY 
    p.productCode, 
    storageCode, 
    saldoMonth, 
    saldoYear,
    op.product_status;

APA: {
    storageCode: "APA",
    month: 7,
    year: 2024,
    RR-100-A: {
        productCode: RR_100-A,
        productName: Reguler Mono 100 Acetatow,
        saldo_awal: {
            totalQty: 300, price_per_qty: 100, nominal: 3000
        },
        penerimaan: {
            pembelian: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            repackIn: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            movingIn: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            totalIn: {totalQty: 300, price_per_qty: 200, nominal: 6000}
        }, 
        pengeluaran: {
            penjualan: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            repackOut: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            movingOut: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            totalOut: {totalQty: 300, price_per_qty: 200, nominal: 6000}
        },
        barang_siap_dijual: {
            totalQty: 600, price_per_qty: 15, nominal: 9000
        },
        saldo_akhir: {
            totalQty: 300, price_per_qty: 10, nominal: 3000
        }
    },
    RR-120-A: {
        productCode: RR_120-A,
        productName: Reguler Mono 120 Acetatow,
        saldo_awal: {
            totalQty: 300, price_per_qty: 100, nominal: 3000
        },
        penerimaan: {
            pembelian: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            repackIn: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            movingIn: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            totalIn: {totalQty: 300, price_per_qty: 200, nominal: 6000}
        }, 
        pengeluaran: {
            penjualan: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            repackOut: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            movingOut: {totalQty: 100, price_per_qty: 200, nominal: 2000},
            totalOut: {totalQty: 300, price_per_qty: 200, nominal: 6000}
        },
        barang_siap_dijual: {
            totalQty: 600, price_per_qty: 15, nominal: 9000
        },
        saldo_akhir: {
            totalQty: 300, price_per_qty: 10, nominal: 3000
        }
    }
}


<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="setHref(this)" value=<?php echo $key["nomor_surat_jalan"]; ?>>
                    DELETE
                    </button>