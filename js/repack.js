let pageState = document.getElementById("pageState").value;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('myForm');
    syncRepackRows('materialAwalTable');
    syncRepackRows('materialBaruTable');

    form.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' && event.target.tagName === 'INPUT') {
            event.preventDefault();
            return false;
        }
    });
});

if (!pageState.includes("amend")){
    document.addEventListener("DOMContentLoaded", function() {
        let invoice_dateEl = document.getElementById("repack_date");
    
        // Get today's date
        let today = new Date();
    
        // Format the date to YYYY-MM-DD
        let year = today.getFullYear();
        let month = String(today.getMonth() + 1).padStart(2, '0'); // Months are zero-based, so add 1 and pad with zero if needed
        let day = String(today.getDate()).padStart(2, '0'); // Pad day with zero if needed
    
        let formattedDate = `${year}-${month}-${day}`;
    
        // Set the value of the date input to today's date
        invoice_dateEl.value = formattedDate;
        getRepackNO();
    });
}

function syncRepackRows(tableId) {
    const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
    const group = tableId === 'materialAwalTable' ? 'Material Awal' : 'Material Baru';
    document.getElementById(tableId + 'Count').textContent = rows.length + (rows.length === 1 ? ' row' : ' rows');
    document.getElementById(tableId + 'Empty').hidden = rows.length > 0;
    const labels = ['Product code', 'Material', 'Quantity', 'Unit', 'Note'];
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
        row.querySelectorAll('input').forEach((input, column) => input.setAttribute('aria-label', group + ', ' + labels[column] + ', row ' + (index + 1)));
        row.querySelector('button').setAttribute('aria-label', 'Remove ' + group + ', row ' + (index + 1));
    });
}

function addRow(tableId) {
    const suffix = tableId === 'materialAwalTable' ? 'awal' : 'akhir';
    const row = document.querySelector('#' + tableId + ' tbody').insertRow();
    row.innerHTML = `<td></td>
        <td><input name="kd_${suffix}[]" class="productCode" oninput="applyAutocomplete(this)" type="text" placeholder="Product code" required></td>
        <td><input name="material_${suffix}[]" type="text" placeholder="Terisi otomatis" readonly></td>
        <td><input name="qty_${suffix}[]" type="text" placeholder="0" required></td>
        <td><input name="uom_${suffix}[]" type="text" placeholder="UOM" required></td>
        <td><input name="note_${suffix}[]" type="text" placeholder="Optional"></td>
        <td><button type="button" class="gm-slip-remove" onclick="removeRow(this)">Remove</button></td>`;
    syncRepackRows(tableId);
    applyAutocomplete(row.querySelector('.productCode'));
    row.querySelector('.productCode').focus();
}

function removeRow(button) {
    const tableId = button.closest('table').id;
    button.closest('tr').remove();
    syncRepackRows(tableId);
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
    let order_date = document.getElementById("repack_date").value;
    let date = new Date(order_date);

    let month = date.getMonth() + 1;
    let year = date.getFullYear();

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: "generate_SJR",
            storageCode: storageCodeEl,
            month: month,
            year: year
        },
        success: function(response) {
            let arr = response.split("/");
            if(pageState == "amend_repack"){
                let old_repack = document.getElementById("old_rpeack").value.split("/");
                if(old_repack[2] == arr[2] && parseInt(old_repack[3]) === parseInt(arr[3]) && parseInt(old_repack[4]) === parseInt(arr[4])){
                    noRepackEl.value = document.getElementById("old_rpeack").value;
                }
                else{
                    noRepackEl.value = response;
                }
            }
            else{
                noRepackEl.value = response;
            }
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}
