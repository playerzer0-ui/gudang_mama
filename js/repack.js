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

    if (tableId === "materialAwalTable") {
        row.innerHTML = `<td>${rowCount}</td>
        <td><input name="kd_awal[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="di isi" required/></td>
        <td><input name="material_awal[]" type="text" placeholder="Otomatis" readonly/></td>
        <td><input name="qty_awal[]" type="text" placeholder="di isi" required/></td>
        <td><input name="uom_awal[]" type="text" placeholder="di isi" required/></td>
        <td><input name="note_awal[]" type="text" /></td>
        <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>`;
    } else {
        row.innerHTML = `<td>${rowCount}</td>
        <td><input name="kd_akhir[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="di isi" required/></td>
        <td><input name="material_akhir[]" type="text" placeholder="Otomatis" readonly/></td>
        <td><input name="qty_akhir[]" type="text" placeholder="di isi" required/></td>
        <td><input name="uom_akhir[]" type="text" placeholder="di isi" required/></td>
        <td><input name="note_akhir[]" type="text" /></td>
        <td><button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button></td>`;
    }

    applyAutocomplete(row.querySelector('.productCode'));
}

function removeRow(button) {
    // Get the row to be removed
    var row = button.parentNode.parentNode;
    // Get the table body
    var tableBody = row.parentNode;
    // Remove the row
    tableBody.removeChild(row);

    // Re-number the rows
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
    const tableId = row.parentElement.parentElement.id;  // Get the table ID

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
                if (tableId === "materialAwalTable") {
                    row.querySelector('input[name="material_awal[]"]').value = data.productName;
                } else if (tableId === "materialBaruTable") {
                    row.querySelector('input[name="material_akhir[]"]').value = data.productName;
                }
            } else {
                if (tableId === "materialAwalTable") {
                    row.querySelector('input[name="material_awal[]"]').value = "Terisi Otomatis";
                } else if (tableId === "materialBaruTable") {
                    row.querySelector('input[name="material_akhir[]"]').value = "Terisi Otomatis";
                }
            }
        }
    });
}

function getRepackNO() {
    let storageCodeEl = document.getElementById('storageCode').value;
    let noRepackEl = document.getElementById('no_repack');

    $.ajax({
        type: "get",
        url: "../controller/index.php?action=generate_SJR&storageCode=" + storageCodeEl,
        success: function(response) {
            noRepackEl.value = response;
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}

window.onload = function() {
    getRepackNO();
};