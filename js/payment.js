var pageState = document.getElementById("pageState").value;

$(document).ready(function () {
    if(pageState.includes("amend")){
        calculateHutang();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('myForm');

    form.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            return false;
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {
    if (pageState === "amend_payment_moving") {
        updateCOGSAndNominals();
    }
});

function handleFormSubmit(event) {
    event.preventDefault(); // Prevent the default form submission

    var form = document.getElementById('myForm');

    // Check if the form is valid
    if (!form.checkValidity()) {
        // If the form is not valid, trigger the browser's validation UI
        form.reportValidity();
        return;
    }
    if (pageState.includes("amend")){
        form.submit();
    }
    else{
        var formData = new FormData(form);
    
        // Add a flag to indicate PDF generation
        formData.append('generate_pdf', '1');
    
        // Create a new tab for the PDF
        var pdfWindow = window.open('', '_blank');
    
        fetch('../controller/index.php?action=create_payment', {
            method: 'POST',
            body: formData
        }).then(response => response.blob())
        .then(blob => {
            var url = URL.createObjectURL(blob);
            pdfWindow.location.href = url; // Load the PDF in the new tab
    
            // Redirect to the dashboard after a short delay
            setTimeout(() => {
                window.location.href = `../controller/index.php?action=dashboard&msg=payment_made`;
            }, 2000); // Adjust the delay as needed
        }).catch(error => {
            console.error('Error:', error);
        });
    }
}

function updateCOGSAndNominals() {
    const rows = document.querySelectorAll("#productTable tbody tr");
    
    rows.forEach(row => {
        const productCodeInput = row.querySelector('.productCode');
        const productCode = productCodeInput.value;

        if (productCode) {
            getHPP(productCodeInput, updateNominal);
        }
    });
}

function getHPP(input, callback){
    const productCode = input.value;
    const row = input.closest('tr');
    const storageCode = pageState.includes("moving") ? document.getElementById("storageCodeSender").value : document.getElementById("storageCode").value;
    let order_date = document.getElementById("invoice_date").value;
    let date = new Date(order_date);

    let month = date.getMonth() + 1;
    let year = date.getFullYear();

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        dataType: 'json',
        data: {
            action: 'getHPP',
            productCode: productCode,
            storageCode: storageCode,
            month: month,
            year: year
        },
        success: function (data) {
            row.querySelector('input[name="price_per_uom[]"]').placeholder = data.toFixed(0);
            callback(row);
        }
    });
}

function updateNominal(row) {
    const qty = parseFloat(row.querySelector('input[name="qty[]"]').value); // Get the quantity value
    const price = parseFloat(row.querySelector('input[name="price_per_uom[]"]').value); // Get the updated COGS value

    if (!isNaN(qty) && !isNaN(price)) {
        const nominal = qty * price; // Calculate the nominal value
        row.querySelector('input[name="nominal[]"]').value = nominal.toFixed(0); // Update the nominal field
    } else {
        row.querySelector('input[name="nominal[]"]').value = ''; // Clear the nominal field if invalid input
    }

    calculateTotalNominal(); // Update total nominal after each row update
}

function getMovingDetailsFromMovingNo(){
    let storageCodeSender = document.getElementById("storageCodeSender");
    let storageCodeReceiver = document.getElementById("storageCodeReceiver");
    let no_moving = document.getElementById("no_moving").value;
    let moving_date = document.getElementById("moving_date");
    let invoice_dateEl = document.getElementById("invoice_date");
    let no_invoiceEl = document.getElementById("no_invoice");
    let tax = document.getElementById("tax");

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: "getMovingDetails",
            no_moving: no_moving
        }
    }).done(function (response) {
        const data = JSON.parse(response);
        storageCodeSender.value = data.storageCodeSender;
        storageCodeReceiver.value = data.storageCodeReceiver;
        moving_date.value = data.moving_date;

        $.ajax({
            type: "get",
            url: "../controller/index.php",
            data: {
                action: 'getInvoiceMovingByNoSJ',
                no_sj: no_moving
            }
        }).done(function (response) {
            const data = JSON.parse(response);
            invoice_dateEl.value = data.invoice_date;
            no_invoiceEl.value = data.no_invoice;
            tax.value = data.tax;

            getOrderProducts(no_moving, "moving");
        });
    });
}


function getDetailsFromSJ(){
    let no_sjEl = document.getElementById("no_sj").value;
    let storageCodeEl = document.getElementById("storageCode");
    let pageState = document.getElementById("pageState").value;
    let invoice_dateEl = document.getElementById("invoice_date");
    let no_invoiceEl = document.getElementById("no_invoice");
    let tax = document.getElementById("tax");
    let no_LPBEl;
    let no_trukEl;
    let vendorCodeEl;
    let purchaseOrderEl;
    let customerCode;
    let customerAddress;
    let npwp;

    if(pageState == "in"){
        no_LPBEl = document.getElementById("no_LPB");
        no_trukEl = document.getElementById("no_truk");
        vendorCodeEl = document.getElementById("vendorCode");
        purchaseOrderEl = document.getElementById("purchase_order");
    }
    else{
        customerCode = document.getElementById("customerCode");
        customerAddress = document.getElementById("customerAddress");
        npwp = document.getElementById("npwp");
    }

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
            
            if(pageState == "in"){
                storageCodeEl.value = data.storageCode;
                no_LPBEl.value = data.no_LPB;
                no_trukEl.value = data.no_truk;
                vendorCodeEl.value = data.vendorCode;
                purchaseOrderEl.value = data.purchase_order;
            }
            else{
                storageCodeEl.value = data.storageCode;
                customerCode.value = data.customerCode;
                customerAddress.value = data.customerAddress;
                npwp.value = data.customerNPWP;
            }
        }
    });

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: 'getInvoiceByNoSJ',
            no_sj: no_sjEl
        },
        success: function (response) {
            const data = JSON.parse(response);
            invoice_dateEl.value = data.invoice_date;
            no_invoiceEl.value = data.no_invoice;
            tax.value = data.tax;
            getOrderProducts(no_sjEl, "in");
        }
    });

}

function getOrderProducts(no_id, status){
    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: 'getOrderProducts',
            status: status,
            no_sj: no_id
        },
        success: function (response) {
            const table = document.getElementById('productTable').getElementsByTagName('tbody')[0];
            let data = JSON.parse(response);
            let newRow;
            let rowCount = 1;
            table.innerHTML = "";

            data.forEach(item => {
                newRow = table.insertRow();
                newRow.innerHTML = `
                    <td>${rowCount}</td>
                    <td><input type="text" name="kd[]" value="${item.productCode}" class="productCode" readonly></td>
                    <td><input style="width: 300px;" value="${item.productName}" type="text" name="material_display[]" readonly><input type="hidden" value="${item.productName}" name="material[]"></td>
                    <td><input type="number" value="${item.qty}" name="qty[]" readonly></td>
                    <td><input type="text" value="${item.uom}" name="uom[]" readonly></td>
                    <td><input type="number" value="${item.price_per_UOM}" inputmode="numeric" name="price_per_uom[]" placeholder="di isi" readonly></td>
                    <td><input type="text" value="${item.price_per_UOM * item.qty}" name="nominal[]" placeholder="otomatis dari sistem" readonly></td>
                `;
            });

            calculateTotalNominal();
        }
    });
}

function calculateHutang(){
    let amount = document.getElementById("payment_amount").value;
    let tax = document.getElementById("tax").value;
    let remaining = document.getElementById("remaining");
    let no_sjEl;
    if(pageState.includes("moving")){
        no_sjEl = document.getElementById("no_moving").value;
    }
    else{
        no_sjEl = document.getElementById("no_sj").value;
    }

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {action: "calculateHutang", payment_amount: amount, tax: tax, no_sj: no_sjEl},
        success: function (response) {
            remaining.innerHTML = response;
        }
    });
}

function calculateTotalNominal() {
    const nominalInputs = document.querySelectorAll('input[name="nominal[]"]'); // Get all nominal inputs
    let total = 0;

    nominalInputs.forEach(input => {
        const nominal = parseFloat(input.value);
        if (!isNaN(nominal)) {
            total += nominal; // Sum up the nominal values
        }
    });

    document.getElementById('totalNominal').value = total.toFixed(0); 
    calculatePPN();
    calculatePayAmount();
}

function calculatePPN(){
    let nominal = document.getElementById('totalNominal').value;
    let taxPPN = document.getElementById('taxPPN');
    let tax = document.getElementById('tax').value;

    taxPPN.value = nominal * (tax / 100);
}

function calculatePayAmount(){
    let nominal = document.getElementById('totalNominal').value;
    let taxPPN = document.getElementById('taxPPN').value;
    let amount_paid = document.getElementById('amount_paid');

    amount_paid.value = parseFloat(nominal) + parseFloat(taxPPN);
}