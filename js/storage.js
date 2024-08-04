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
            console.log(response);
            let data = JSON.parse(response);
            populateReportTable(data);
        }
    });

    // Populate the report table
}

function populateReportTable(data) {
    const tbody = document.querySelector('#reporttable tbody');
    tbody.innerHTML = ''; // Clear existing rows

    // Variables to store total sums for final row
    let totalSaldoAwalQty = 0;
    let totalSaldoAwalRupiah = 0;
    let totalPenerimaanQty = 0;
    let totalPenerimaanRupiah = 0;
    let totalPengeluaranQty = 0;
    let totalPengeluaranRupiah = 0;
    let totalSaldoAkhirQty = 0;
    let totalSaldoAkhirRupiah = 0;

    let count = 0;
    for (let key in data) {
        if (key === "0") continue; // Skip metadata

        count++;
        let item = data[key];
        let row = document.createElement('tr');

        // No, KD, Material
        row.innerHTML = `
            <td>${count}</td>
            <td>${item.productCode}</td>
            <td>${item.productName}</td>
        `;

        // Saldo Awal
        let saldoAwalQty = item.saldo_awal.totalQty;
        let saldoAwalRupiah = parseFloat(item.saldo_awal.totalPrice);
        totalSaldoAwalQty += saldoAwalQty;
        totalSaldoAwalRupiah += saldoAwalRupiah;

        row.innerHTML += `
            <td>${saldoAwalQty}</td>
            <td>${formatNumber(item.saldo_awal.price_per_qty)}</td>
            <td>${formatNumber(saldoAwalRupiah)}</td>
        `;

        // Penerimaan
        let pembelianQty = parseInt(item.penerimaan.pembelian.totalQty);
        let pembelianRupiah = parseFloat(item.penerimaan.pembelian.totalPrice);
        totalPenerimaanQty += pembelianQty;
        totalPenerimaanRupiah += pembelianRupiah;

        let totalInQty = item.penerimaan.totalIn.totalQty;
        let totalInRupiah = parseFloat(item.penerimaan.totalIn.totalPrice);
        totalPenerimaanQty += totalInQty;
        totalPenerimaanRupiah += totalInRupiah;

        row.innerHTML += `
            <td>${pembelianQty}</td>
            <td>${formatNumber(item.penerimaan.pembelian.price_per_qty)}</td>
            <td>${formatNumber(pembelianRupiah)}</td>
            <td>${item.penerimaan.movingIn.totalQty}</td>
            <td>${formatNumber(item.penerimaan.movingIn.price_per_qty)}</td>
            <td>${formatNumber(item.penerimaan.movingIn.totalPrice)}</td>
            <td>${item.penerimaan.repackIn.totalQty}</td>
            <td>${formatNumber(item.penerimaan.repackIn.price_per_qty)}</td>
            <td>${formatNumber(item.penerimaan.repackIn.totalPrice)}</td>
            <td>${totalInQty}</td>
            <td>${formatNumber(item.penerimaan.totalIn.price_per_qty)}</td>
            <td>${formatNumber(totalInRupiah)}</td>
        `;

        // Barang Siap Dijual
        row.innerHTML += `
            <td>${item.barang_siap_dijual.totalQty}</td>
            <td>${formatNumber(item.barang_siap_dijual.price_per_qty)}</td>
            <td>${formatNumber(item.barang_siap_dijual.totalPrice)}</td>
        `;

        // Pengeluaran
        let penjualanQty = parseInt(item.pengeluaran.penjualan.totalQty);
        let penjualanRupiah = parseFloat(item.pengeluaran.penjualan.totalPrice);
        totalPengeluaranQty += penjualanQty;
        totalPengeluaranRupiah += penjualanRupiah;

        let totalOutQty = item.pengeluaran.totalOut.totalQty;
        let totalOutRupiah = parseFloat(item.pengeluaran.totalOut.totalPrice);
        totalPengeluaranQty += totalOutQty;
        totalPengeluaranRupiah += totalOutRupiah;

        row.innerHTML += `
            <td>${penjualanQty}</td>
            <td>${formatNumber(item.pengeluaran.penjualan.price_per_qty)}</td>
            <td>${formatNumber(penjualanRupiah)}</td>
            <td>${item.pengeluaran.movingOut.totalQty}</td>
            <td>${formatNumber(item.pengeluaran.movingOut.price_per_qty)}</td>
            <td>${formatNumber(item.pengeluaran.movingOut.totalPrice)}</td>
            <td>${item.pengeluaran.repackOut.totalQty}</td>
            <td>${formatNumber(item.pengeluaran.repackOut.price_per_qty)}</td>
            <td>${formatNumber(item.pengeluaran.repackOut.totalPrice)}</td>
            <td>${totalOutQty}</td>
            <td>${formatNumber(item.pengeluaran.totalOut.price_per_qty)}</td>
            <td>${formatNumber(totalOutRupiah)}</td>
        `;

        // Saldo Akhir
        let saldoAkhirQty = item.saldo_akhir.totalQty;
        let saldoAkhirRupiah = parseFloat(item.saldo_akhir.totalPrice);
        totalSaldoAkhirQty += saldoAkhirQty;
        totalSaldoAkhirRupiah += saldoAkhirRupiah;

        row.innerHTML += `
            <td>${saldoAkhirQty}</td>
            <td>${formatNumber(item.saldo_akhir.price_per_qty)}</td>
            <td>${formatNumber(saldoAkhirRupiah)}</td>
        `;

        tbody.appendChild(row);
    }

    // Optionally, you can add a row for totals if needed
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID', { style: 'decimal', maximumFractionDigits: 0 }).format(number);
}