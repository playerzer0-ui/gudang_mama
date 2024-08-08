let pageState = document.getElementById("pageState").value;

$(document).ready(function () {
    calculateHutang();
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

function getDetailsFromSJ(){
    let no_sjEl = document.getElementById("no_sj").value;
    let storageCodeEl = document.getElementById("storageCode");
    let pageState = document.getElementById("pageState").value;
    let invoice_dateEl = document.getElementById("invoice_date");
    let no_invoiceEl = document.getElementById("no_invoice");
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
    let remaining = document.getElementById("remaining");
    let no_sjEl = document.getElementById("no_sj").value;

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {action: "calculateHutang", payment_amount: amount, no_sj: no_sjEl},
        success: function (response) {
            remaining.innerHTML = response;
        }
    });
}

function calculateNominal(priceInput) {
    const row = priceInput.closest('tr'); // Get the closest row to the input
    const qty = parseFloat(row.querySelector('input[name="qty[]"]').value); // Get the quantity value
    const price = parseFloat(priceInput.value); // Get the price value

    if (!isNaN(qty) && !isNaN(price)) {
        const nominal = qty * price; // Calculate the nominal value
        row.querySelector('input[name="nominal[]"]').value = nominal.toFixed(2); // Update the nominal field
    } else {
        row.querySelector('input[name="nominal[]"]').value = ''; // Clear the nominal field if invalid input
    }

    calculateTotalNominal();
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

    document.getElementById('totalNominal').value = total.toFixed(2); 
    calculatePPN();
    calculatePayAmount();
}

function calculatePPN(){
    let nominal = document.getElementById('totalNominal').value;
    let taxPPN = document.getElementById('taxPPN');

    taxPPN.value = nominal * 0.11;
}

function calculatePayAmount(){
    let nominal = document.getElementById('totalNominal').value;
    let taxPPN = document.getElementById('taxPPN').value;
    let amount_paid = document.getElementById('amount_paid');

    amount_paid.value = parseFloat(nominal) + parseFloat(taxPPN);
}