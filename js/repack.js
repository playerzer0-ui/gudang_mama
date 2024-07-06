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

    if(tableId == "materialAwalTable"){
        row.innerHTML = `<td>${rowCount}</td>
        <td><input name="kd_awal[]" type="text" placeholder="di isi" /></td>
        <td><input name="material_awal[]" type="text" placeholder="Otomatis" /></td>
        <td><input name="qty_awal[]" type="text" placeholder="di isi" /></td>
        <td><input name="uom_awal[]" type="text" placeholder="di isi" /></td>
        <td><input name="note_awal[]" type="text" /></td>`;
    }
    else{
        row.innerHTML = `<td>${rowCount}</td>
                <td><input name="kd_akhir[]" type="text" placeholder="di isi" /></td>
                <td><input name="material_akhir[]" type="text" placeholder="Otomatis" /></td>
                <td><input name="qty_akhir[]" type="text" placeholder="di isi" /></td>
                <td><input name="uom_akhir[]" type="text" placeholder="Otomatis" /></td>
                <td><input name="note_akhir[]" type="text" /></td>`;
    }
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

function deleteRow(tableId) {
    var table = document.getElementById(tableId);
    var rowCount = table.rows.length;
    if (rowCount > 1) {
        table.deleteRow(rowCount - 1);
    }
}

function getRepackNO(){
    let storageCodeEl = document.getElementById('storageCode').value;
    let noRepackEl = document.getElementById('no_repack');

    $.ajax({
        type: "get",
        url: "../controller/index.php?action=generate_SJR&storageCode=" + storageCodeEl,
        success: function (response) {
            noRepackEl.value = response;
        },
        error: function(xhr, status, error) {
            console.error("Error: " + error);
        }
    });
}