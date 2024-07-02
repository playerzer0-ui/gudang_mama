-- invoice

select o.nomor_surat_jalan, o.tanggal, v.nama_vendor, g.nama_gudang, g.alamat_gudang1, g.alamat_gudang2, g.alamat_gudang3, b.nama_barang, ob.jumlah, ob.harga, ob.unit, o.status_mode
from orders o, vendor v, barang b, gudang g, order_barang ob
where o.gudangID = g.gudangID
and b.barangID = ob.barangID
and o.orderID = ob.orderID
and o.vendorID = v.vendorID
and status_mode = 1