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
    // Fetch selected values
    const storageCodeEl = document.getElementById('storageCode').value;
    const monthEl = document.getElementById('month').value;
    const yearEl = document.getElementById('year').value;

    $.ajax({
        type: "get",
        url: "../controller/index.php",
        data: {
            action: "getReportStock",
            month: monthEl,
            year: yearEl,
            storageCode: storageCodeEl

        },
        success: function (response) {
            //console.log(response);
            let data = JSON.parse(response);
            populateReportTable(data);
        }
    });

    // Populate the report table
}

function populateReportTable(data) {
    const userType = document.getElementById('userType').value; // Get userType from hidden input
    const tbody = document.querySelector('#reporttable tbody');
    tbody.innerHTML = ''; // Clear existing rows

    // Variables to store total sums for final row
    let totalSaldoAwalQty = 0;
    let totalPenerimaanQty = 0;
    let totalPengeluaranQty = 0;
    let totalSaldoAkhirQty = 0;

    let totalSaldoAwalRupiah = 0;
    let totalPenerimaanRupiah = 0;
    let totalPengeluaranRupiah = 0;
    let totalSaldoAkhirRupiah = 0;

    let item;
    let storageCode;
    let month;
    let year;

    let count = 0;
    for (let key in data) {
        if (key === "0"){
            item = data[key];
            storageCode = item.storageCode;
            month = item.month;
            year = item.year;
            continue;
        }

        count++;
        item = data[key];
        let row = document.createElement('tr');

        // No, KD, Material
        row.innerHTML = `
            <td>${count}</td>
            <td>${item.productCode}</td>
            <td>${item.productName}</td>
        `;

        // Saldo Awal
        let saldoAwalQty = item.saldo_awal.totalQty;
        totalSaldoAwalQty += saldoAwalQty;

        row.innerHTML += `<td>${formatNumber(saldoAwalQty)}</td>`;

        if (userType == 1) {
            let saldoAwalRupiah = parseFloat(item.saldo_awal.totalPrice);
            totalSaldoAwalRupiah += saldoAwalRupiah;

            row.innerHTML += `
                <td>${formatNumber(item.saldo_awal.price_per_qty)}</td>
                <td>${formatNumber(saldoAwalRupiah)}</td>
            `;
        }

        // Penerimaan
        let pembelianQty = parseInt(item.penerimaan.pembelian.totalQty);
        let totalInQty = item.penerimaan.totalIn.totalQty;
        totalPenerimaanQty += pembelianQty + totalInQty;

        row.innerHTML += `<td>${formatNumber(pembelianQty)}</td>`;

        if (userType == 1) {
            let pembelianRupiah = parseFloat(item.penerimaan.pembelian.totalPrice);
            let totalInRupiah = parseFloat(item.penerimaan.totalIn.totalPrice);
            totalPenerimaanRupiah += pembelianRupiah + totalInRupiah;

            row.innerHTML += `
                <td>${formatNumber(item.penerimaan.pembelian.price_per_qty)}</td>
                <td>${formatNumber(pembelianRupiah)}</td>
                <td>${formatNumber(item.penerimaan.movingIn.totalQty)}</td>
                <td>${formatNumber(item.penerimaan.movingIn.price_per_qty)}</td>
                <td>${formatNumber(item.penerimaan.movingIn.totalPrice)}</td>
                <td>${formatNumber(item.penerimaan.repackIn.totalQty)}</td>
                <td>${formatNumber(item.penerimaan.repackIn.price_per_qty)}</td>
                <td>${formatNumber(item.penerimaan.repackIn.totalPrice)}</td>
                <td>${formatNumber(totalInQty)}</td>
                <td>${formatNumber(item.penerimaan.totalIn.price_per_qty)}</td>
                <td>${formatNumber(totalInRupiah)}</td>
            `;
        } else {
            row.innerHTML += `
                <td>${formatNumber(item.penerimaan.movingIn.totalQty)}</td>
                <td>${formatNumber(item.penerimaan.repackIn.totalQty)}</td>
                <td>${formatNumber(totalInQty)}</td>
            `;
        }

        // Barang Siap Dijual
        row.innerHTML += `<td>${formatNumber(item.barang_siap_dijual.totalQty)}</td>`;

        if (userType == 1) {
            row.innerHTML += `
                <td>${formatNumber(item.barang_siap_dijual.price_per_qty)}</td>
                <td>${formatNumber(item.barang_siap_dijual.totalPrice)}</td>
            `;
        }

        // Pengeluaran
        let penjualanQty = parseInt(item.pengeluaran.penjualan.totalQty);
        let totalOutQty = item.pengeluaran.totalOut.totalQty;
        totalPengeluaranQty += penjualanQty + totalOutQty;

        row.innerHTML += `<td>${formatNumber(penjualanQty)}</td>`;

        if (userType == 1) {
            let penjualanRupiah = parseFloat(item.pengeluaran.penjualan.totalPrice);
            let totalOutRupiah = parseFloat(item.pengeluaran.totalOut.totalPrice);
            totalPengeluaranRupiah += penjualanRupiah + totalOutRupiah;

            row.innerHTML += `
                <td>${formatNumber(item.pengeluaran.penjualan.price_per_qty)}</td>
                <td>${formatNumber(penjualanRupiah)}</td>
                <td>${formatNumber(item.pengeluaran.movingOut.totalQty)}</td>
                <td>${formatNumber(item.pengeluaran.movingOut.price_per_qty)}</td>
                <td>${formatNumber(item.pengeluaran.movingOut.totalPrice)}</td>
                <td>${formatNumber(item.pengeluaran.repackOut.totalQty)}</td>
                <td>${formatNumber(item.pengeluaran.repackOut.price_per_qty)}</td>
                <td>${formatNumber(item.pengeluaran.repackOut.totalPrice)}</td>
                <td>${formatNumber(totalOutQty)}</td>
                <td>${formatNumber(item.pengeluaran.totalOut.price_per_qty)}</td>
                <td>${formatNumber(totalOutRupiah)}</td>
            `;
        } else {
            row.innerHTML += `
                <td>${formatNumber(item.pengeluaran.movingOut.totalQty)}</td>
                <td>${formatNumber(item.pengeluaran.repackOut.totalQty)}</td>
                <td>${formatNumber(totalOutQty)}</td>
            `;
        }

        // Saldo Akhir
        let saldoAkhirQty = item.saldo_akhir.totalQty;
        totalSaldoAkhirQty += saldoAkhirQty;

        row.innerHTML += `<td>${formatNumber(saldoAkhirQty)}</td>`;

        if (userType == 1) {
            let saldoAkhirRupiah = parseFloat(item.saldo_akhir.totalPrice);
            totalSaldoAkhirRupiah += saldoAkhirRupiah;

            row.innerHTML += `
                <td>${formatNumber(item.saldo_akhir.price_per_qty)}</td>
                <td>${formatNumber(saldoAkhirRupiah)}</td>
            `;
        }

        tbody.appendChild(row);

    }
    // Optionally, you can add a row for totals if needed, based on userType
    document.getElementById("excel").innerHTML = `<a href="../controller/index.php?action=excel_stock&storageCode=${storageCode}&month=${month}&year=${year}" target="_blank"><button class="btn btn-success">excel</button></a>`;

}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID', { style: 'decimal', maximumFractionDigits: 0 }).format(number);
}