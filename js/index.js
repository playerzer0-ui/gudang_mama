let rowCount = document.querySelectorAll('#productTable tbody tr').length;
let pageState = document.getElementById("pageState").value;
if (pageState.includes("slip_in")) {
    var NO_LPB = document.getElementById("no_LPB").value.split("/");
}
var NO_SJ = document.getElementById("no_sj").value.split("/");

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('myForm');
    form.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && event.target.tagName === 'INPUT') event.preventDefault();
    });
    syncSlipRows();
    if (!pageState.includes("amend")) {
        const today = new Date();
        document.getElementById("tgl_penerimaan").value = today.getFullYear() + '-' +
            String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
        if (pageState === 'in') getLPB();
        else if (pageState === 'out') getSJ();
        else getSJT();
    }
});

function syncSlipRows() {
    const rows = document.querySelectorAll('#productTable tbody tr');
    rowCount = rows.length;
    document.getElementById('slipRowCount').textContent = rowCount + (rowCount === 1 ? ' row' : ' rows');
    document.getElementById('slipEmptyState').hidden = rowCount > 0;
    const labels = {kd: 'Product code', material_display: 'Material', qty: 'Quantity', uom: 'Unit', note: 'Note'};
    rows.forEach((row, index) => {
        row.cells[0].textContent = index + 1;
        row.querySelectorAll('input:not([type="hidden"])').forEach(input => {
            input.setAttribute('aria-label', labels[input.name.replace('[]', '')] + ', row ' + (index + 1));
        });
        row.querySelector('button').setAttribute('aria-label', 'Remove row ' + (index + 1));
    });
}

function applyAutocomplete(element) {
    $(element).autocomplete({
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
            $(element).val(ui.item.value);
            getProductDetails(element);
            return false;
        }
    });
}

function addRow() {
    const table = document.querySelector('#productTable tbody');
    const newRow = table.insertRow();
    newRow.innerHTML = `
        <td></td>
        <td><input type="text" name="kd[]" placeholder="Product code" class="productCode" oninput="applyAutocomplete(this)" required></td>
        <td><input type="text" name="material_display[]" placeholder="Terisi otomatis" readonly><input type="hidden" name="material[]"></td>
        <td><input type="number" name="qty[]" placeholder="0" required></td>
        <td><input type="text" name="uom[]" placeholder="UOM" required></td>
        <td><input type="text" name="note[]" placeholder="Optional"></td>
        <td><button type="button" class="gm-slip-remove" onclick="deleteRow(this)">Remove</button></td>
    `;
    syncSlipRows();
    newRow.querySelector('.productCode').focus();
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
                row.querySelector('input[name="material_display[]"]').value = data.productName;
                row.querySelector('input[name="material[]"]').value = data.productName;
            } else {
                // Clear fields if no product is found
                row.querySelector('input[name="material_display[]"]').value = "Terisi Otomatis";
                row.querySelector('input[name="material[]"]').value = "";
            }
        }
    });
}

function deleteRow(button) {
    button.closest('tr').remove();
    syncSlipRows();
}

function getLPB(){
    let storageCodeEl = document.getElementById('storageCode').value;
    let noLPBEl = document.getElementById('no_lpb_display');
    let noLPBHiddenEl = document.getElementById('no_LPB');
    let order_date = document.getElementById("tgl_penerimaan").value;
    let date = new Date(order_date);

    let month = date.getMonth() + 1;
    let year = date.getFullYear();

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: "generate_LPB",
            storageCode: storageCodeEl,
            month: month,
            year: year
        },
        success: function (response) {
            let arr = response.split("/");
            if(pageState == "amend_slip_in" && NO_LPB[2] === arr[2] && parseInt(NO_LPB[3]) === parseInt(arr[3]) && parseInt(NO_LPB[4]) === parseInt(arr[4])){
                noLPBEl.value = NO_LPB[0] + "/" + NO_LPB[1] + "/" + NO_LPB[2] + "/" + NO_LPB[3] + "/" + NO_LPB[4];
                noLPBHiddenEl.value = NO_LPB[0] + "/" + NO_LPB[1] + "/" + NO_LPB[2] + "/" + NO_LPB[3] + "/" + NO_LPB[4];
            }
            else{
                noLPBEl.value = response;
                noLPBHiddenEl.value = response;
            }
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}

function getSJ(){
    let storageCodeEl = document.getElementById('storageCode').value;
    let no_sjEl = document.getElementById('no_sj');
    let order_date = document.getElementById("tgl_penerimaan").value;
    let date = new Date(order_date);

    let month = date.getMonth() + 1;
    let year = date.getFullYear();

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: "generate_SJ",
            storageCode: storageCodeEl,
            month: month,
            year: year
        },
        success: function (response) {
            let arr = response.split("/");
            if(pageState == "amend_slip_out" && NO_SJ[2] === arr[2] && parseInt(NO_SJ[3]) === parseInt(arr[3]) && parseInt(NO_SJ[4]) === parseInt(arr[4])){
                no_sjEl.value = NO_SJ[0] + "/" + NO_SJ[1] + "/" + NO_SJ[2] + "/" + NO_SJ[3] + "/" + NO_SJ[4];
            }
            else{
                no_sjEl.value = response;
            }
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}

function getSJT(){
    let storageCodeEl = document.getElementById('storageCode').value;
    let no_sjEl = document.getElementById('no_sj');
    let order_date = document.getElementById("tgl_penerimaan").value;
    let date = new Date(order_date);

    let month = date.getMonth() + 1;
    let year = date.getFullYear();

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: "generate_SJT",
            storageCode: storageCodeEl,
            month: month,
            year: year
        },
        success: function (response) {
            let arr = response.split("/");
            if(pageState == "amend_slip_out_tax" && NO_SJ[2] === arr[2] && parseInt(NO_SJ[3]) === parseInt(arr[3]) && parseInt(NO_SJ[4]) === parseInt(arr[4])){
                no_sjEl.value = NO_SJ[0] + "/" + NO_SJ[1] + "/" + NO_SJ[2] + "/" + NO_SJ[3] + "/" + NO_SJ[4];
            }
            else{
                no_sjEl.value = response;
            }
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}
