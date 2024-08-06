let rowCount = 0;
let pageState = document.getElementById("pageState").value;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('myForm');

    form.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            return false;
        }
    });
});

if (!pageState.includes("amend")){
    document.addEventListener("DOMContentLoaded", function() {
        let invoice_dateEl = document.getElementById("tgl_penerimaan");
    
        // Get today's date
        let today = new Date();
    
        // Format the date to YYYY-MM-DD
        let year = today.getFullYear();
        let month = String(today.getMonth() + 1).padStart(2, '0'); // Months are zero-based, so add 1 and pad with zero if needed
        let day = String(today.getDate()).padStart(2, '0'); // Pad day with zero if needed
    
        let formattedDate = `${year}-${month}-${day}`;
    
        // Set the value of the date input to today's date
        invoice_dateEl.value = formattedDate;
    });
}

function addRow() {
    rowCount++;
    const table = document.getElementById('productTable').getElementsByTagName('tbody')[0];
    const newRow = table.insertRow();

    newRow.innerHTML = `
        <td>${rowCount}</td>
        <td><input type="text" name="kd[]" placeholder="di isi" class="productCode" required></td>
        <td><input style="width: 300px;" type="text" name="material_display[]" value="Terisi Otomatis" readonly><input type="hidden" name="material[]"></td>
        <td><input type="number" name="qty[]" placeholder="di isi" required></td>
        <td><input type="text" name="uom[]" value="" placeholder="di isi" required></td>
        <td><input type="text" name="note[]" placeholder=""></td>
        <td><button class="btn btn-danger" onclick="deleteRow(this)">Delete</button></td>
    `;

    // Apply autocomplete to the new input field
    $(newRow).find('.productCode').autocomplete({
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
    const row = button.parentNode.parentNode;
    row.parentNode.removeChild(row);

    // Update row numbers
    const rows = document.getElementById('productTable').getElementsByTagName('tbody')[0].rows;
    rowCount = 0;
    for (let i = 0; i < rows.length; i++) {
        rowCount++;
        rows[i].cells[0].innerText = rowCount;
    }
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
            noLPBEl.value = response;
            noLPBHiddenEl.value = response;
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
            no_sjEl.value = response;
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
            no_sjEl.value = response;
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}