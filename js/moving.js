document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('myForm');

    form.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            return false;
        }
    });
});

function addRow(tableId) {
    var table = document.getElementById(tableId);
    var rowCount = table.rows.length;
    var row = table.insertRow(rowCount);

    row.innerHTML = `<td>${rowCount}</td>
        <td><input name="kd[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="di isi" required/></td>
        <td><input name="productName[]" type="text" placeholder="Otomatis" readonly/></td>
        <td><input name="qty[]" type="text" placeholder="di isi" oninput="calculateNominal(this)" required/></td>
        <td><input name="uom[]" type="text" placeholder="di isi" required/></td>
        <td><input name="price_per_uom[]" type="text" placeholder="otomatis" readonly/></td>
        <td><input type="number" inputmode="numeric" name="nominal[]" placeholder="Otomatis" readonly></td>
        <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>`;

    applyAutocomplete(row.querySelector('.productCode'));
}

function removeRow(button) {
    var row = button.parentNode.parentNode;
    var tableBody = row.parentNode;
    tableBody.removeChild(row);
    reNumberRows(tableBody);
}

function reNumberRows(tableBody) {
    var rows = tableBody.rows;
    for (var i = 0; i < rows.length; i++) {
        rows[i].cells[0].innerText = i + 1;
    }
}

function applyAutocomplete(input) {
    $(input).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '../controller/index.php',
                type: 'GET',
                dataType: 'json',
                data: {
                    action: 'getProductSuggestions',
                    term: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        select: function(event, ui) {
            $(this).val(ui.item.value);
            getProductDetails(this);
            return false;
        }
    });
}

function getProductDetails(input) {
    const productCode = input.value;
    const row = input.parentElement.parentElement;

    $.ajax({
        url: '../controller/index.php',
        type: 'GET',
        dataType: 'json',
        data: {
            action: 'getProductDetails',
            productCode: productCode
        },
        success: function(data) {
            if (data) {
                row.querySelector('input[name="productName[]"]').value = data.productName;
                getHPP(input);
            } else {
                row.querySelector('input[name="productName[]"]').value = "Terisi Otomatis";
            }
        }
    });
}

function getHPP(input){
    const productCode = input.value;
    const row = input.parentElement.parentElement;
    const storageCode = document.getElementById("storageCodeSender").value;

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        dataType: 'json',
        data: {
            action: 'getHPP',
            productCode: productCode,
            storageCode: storageCode
        },
        success: function (data) {
            row.querySelector('input[name="price_per_uom[]"]').value = data;
        }
    });
}

function calculateNominal(priceInput) {
    const row = priceInput.closest('tr'); // Get the closest row to the input
    const qty = parseFloat(row.querySelector('input[name="price_per_uom[]"]').value); // Get the quantity value
    const price = parseFloat(priceInput.value); // Get the price value

    if (!isNaN(qty) && !isNaN(price)) {
        const nominal = qty * price; // Calculate the nominal value
        row.querySelector('input[name="nominal[]"]').value = nominal.toFixed(2); // Update the nominal field
    } else {
        row.querySelector('input[name="nominal[]"]').value = ''; // Clear the nominal field if invalid input
    }
}

function getMovingNO() {
    let storageCodeEl = document.getElementById('storageCodeSender').value;
    let noMovingEl = document.getElementById('no_moving');

    $.ajax({
        type: "get",
        url: "../controller/index.php?action=generate_SJP&storageCode=" + storageCodeEl,
        success: function(response) {
            noMovingEl.value = response;
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}