document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('myForm');

    form.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            return false;
        }
    });
});

function getDetailsFromSJ(){
    let no_sjEl = document.getElementById("no_sj").value;
    let storageCodeEl = document.getElementById("storageCode");
    let no_LPBEl = document.getElementById("no_LPB");
    let no_trukEl = document.getElementById("no_truk");
    let vendorCodeEl = document.getElementById("vendorCode");
    let purchaseOrderEl = document.getElementById("purchase_order");

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: 'getOrderByNoSJ',
            no_sj: no_sjEl
        },
        success: function (response) {
            const table = document.getElementById('productTable').getElementsByTagName('tbody')[0];
            const data = JSON.parse(response);
            
            storageCodeEl.value = data.storageCode;
            no_LPBEl.value = data.no_LPB;
            no_trukEl.value = data.no_truk;
            vendorCodeEl.value = data.vendorCode;
            purchaseOrderEl.value = data.purchase_order;
        }
    });

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: 'getOrderProducts',
            status: 'in',
            no_sj: no_sjEl
        },
        success: function (response) {
            const table = document.getElementById('productTable').getElementsByTagName('tbody')[0];
            let data = JSON.parse(response);
            let newRow;
            let rowCount = 1;

            console.log("response: " + data[0].nomor_surat_jalan);

            data.forEach(item => {
                newRow = table.insertRow();
                newRow.innerHTML = `
                    <td>${rowCount}</td>
                    <td><input type="text" name="kd[]" value="${item.productCode}" class="productCode" disabled></td>
                    <td><input style="width: 300px;" value="${item.productName}" type="text" name="material_display[]" disabled></td>
                    <td><input type="number" value="${item.qty}" name="qty[]" disabled></td>
                    <td><input type="text" value="${item.uom}" name="uom[]" value="" disabled></td>
                    <td><input type="number" inputmode="numeric" name="price_per_uom[]" placeholder="di isi"></td>
                    <td><input type="text" name="nominal[]" placeholder="di isi" readonly></td>
                `;
            });
        }
    });
}

function calculateNominal(price){
    let amount = price.value;

    
}