// JavaScript to populate the year options dynamically
const yearSelect = document.getElementById('year');
const currentYear = new Date().getFullYear();
const startYear = currentYear - 50; // 50 years back
const endYear = currentYear + 10; // 10 years ahead

for (let year = startYear; year <= endYear; year++) {
    const option = document.createElement('option');
    option.value = year;
    option.textContent = year;
    yearSelect.appendChild(option);
}

function generateReport() { 
    let yearValue = document.getElementById("year").value;
    let monthValue = document.getElementById("month").value;

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: "getLaporanPiutang",
            month: monthValue,
            year: yearValue
        },
        success: function (response) {
            let data = JSON.parse(response);
            console.log(response);
            populateTable(data);
        }
    });
}

function populateTable(data) {
    const table = document.getElementById('reporttable');
    table.innerHTML = `
        <thead>
            <tr>
                <th>No.</th>
                <th>Tgl Inv</th>
                <th>Nama Customer</th>
                <th>Nomer Inv</th>
                <th>Nama Material</th>
                <th>QTY</th>
                <th>price/UOM</th>
                <th>nominal</th>
                <th>total nominal</th>
                <th>Tgl Pembayaran</th>
                <th>Nilai Bayar</th>
                <th>Sisa Hutang</th>
            </tr>
        </thead>
        <tbody></tbody>
    `;

    const tbody = table.querySelector('tbody');
    let rowNumber = 1;
    let totalQty = 0;
    let totalNominal = 0;
    let totalPayment = 0;
    let totalRemaining = 0;

    data.forEach((invoice, index) => {
        const rowCount = invoice.rows.length;
        let firstRow = true;

        invoice.rows.forEach(row => {
            const tr = document.createElement('tr');
            if (firstRow) {
                tr.innerHTML += `<td rowspan="${rowCount}">${rowNumber}</td>`;
                tr.innerHTML += `<td rowspan="${rowCount}">${invoice.invoice_date}</td>`;
                tr.innerHTML += `<td rowspan="${rowCount}">${invoice.customerName}</td>`;
                tr.innerHTML += `<td rowspan="${rowCount}">${invoice.no_invoice}</td>`;
            }

            tr.innerHTML += `<td>${row.productName}</td>`;
            tr.innerHTML += `<td>${row.qty}</td>`;
            tr.innerHTML += `<td>${formatNumber(row.price_per_UOM)}</td>`;
            tr.innerHTML += `<td>${formatNumber(row.nominal)}</td>`;

            if (firstRow) {
                tr.innerHTML += `<td rowspan="${rowCount}">${formatNumber(invoice.totalNominal)}</td>`;
                tr.innerHTML += `<td rowspan="${rowCount}">${invoice.payment_date}</td>`;
                tr.innerHTML += `<td rowspan="${rowCount}">${formatNumber(invoice.payment_amount)}</td>`;
                if(formatNumber(invoice.payment_amount - invoice.totalNominal) < 0){
                    tr.innerHTML += `<td class="not-paid" rowspan="${rowCount}">${formatNumber(invoice.payment_amount - invoice.totalNominal)}</td>`;
                }
                else{
                    tr.innerHTML += `<td rowspan="${rowCount}">${formatNumber(invoice.payment_amount - invoice.totalNominal)}</td>`;
                }
                firstRow = false;
            }

            tbody.appendChild(tr);
        });

        rowNumber++;
        totalQty += invoice.totalQty;
        totalNominal += invoice.totalNominal;
        totalPayment += parseFloat(invoice.payment_amount);
        totalRemaining += invoice.payment_amount - invoice.totalNominal;
    });

    const totalRow = document.createElement('tr');
    totalRow.innerHTML = `
        <td colspan="5">Total</td>
        <td>${formatNumber(totalQty)}</td>
        <td></td>
        <td></td>
        <td>${formatNumber(totalNominal)}</td>
        <td></td>
        <td>${formatNumber(totalPayment)}</td>
        <td>${formatNumber(totalRemaining)}</td>
    `;
    tbody.appendChild(totalRow);
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID', { style: 'decimal', maximumFractionDigits: 0 }).format(number);
}
