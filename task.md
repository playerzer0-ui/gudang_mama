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