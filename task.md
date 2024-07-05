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