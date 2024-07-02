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

function validateForm() {
    let isValid = true;
    const rows = document.querySelectorAll('#productTable tbody tr');
    rows.forEach(row => {
        const productCode = row.querySelector('input[name="kd[]"]').value;
        const qty = row.querySelector('input[name="qty[]"]').value;

        if (!productCode || !qty || isNaN(qty) || qty <= 0) {
            alert('Please fill out all required fields correctly.');
            isValid = false;
            return false;
        }
    });

    return isValid;
}