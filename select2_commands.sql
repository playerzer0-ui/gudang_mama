--  SLIP penerimaan barang (details inserted)
insert into orders values ("001/SJJ/SOME/12/2024", "CBA", "001/LPB/CBA/12/2024", "001202130", "WIM", "NON", "2024-12-03", "1212213", "1");
insert into order_products values ("001/SJJ/SOME/12/2024","-","-", "RR-120-A", 100, "tray", 2000, "something", "in");
insert into order_products values ("001/SJJ/SOME/12/2024","-","-", "RR-100-A", 80, "tray", 100, "something", "in");

-- now select them up to make invoice (in)
select i.nomor_surat_jalan, o.storageCode, o.no_truk, o.no_LPB, o.vendorCode, o.purchase_order, i.no_invoice, i.invoice_date, i.no_faktur, op.productCode, p.productName, op.qty, op.UOM, op.price_per_UOM, (op.qty * op.price_per_UOM) AS nominal
from invoices i, orders o, order_products op, products p
where i.nomor_surat_jalan = "001/SJJ/SOME/12/2024"
and i.nomor_surat_jalan = o.nomor_surat_jalan
and i.nomor_surat_jalan = op.nomor_surat_jalan
and op.productCode = p.productCode;

-- payment in
select p.nomor_surat_jalan, o.storageCode, o.no_LPB, o.purchase_order, i.no_invoice, o.vendorCode, o.purchase_order, op.productCode, pr.productName, op.qty, op.UOM, op.price_per_UOM, (op.qty * op.price_per_UOM) AS nominal, p.payment_date, p.payment_amount 
from invoices i, payments p, orders o, order_products op, products pr
where p.nomor_surat_jalan = "001/SJJ/SOME/12/2024"
and p.nomor_surat_jalan = i.nomor_surat_jalan
and p.nomor_surat_jalan = o.nomor_surat_jalan
and p.nomor_surat_jalan = op.nomor_surat_jalan
and op.productCode = pr.productCode;

-- SLIP OUT
insert into orders values ("001/SJK/NON/10/2024", "NON", "001/SJK/NON/10/2024", "001202130", "NON", "DED", "2024-10-03", "12312213", "2");
insert into order_products values ("001/SJK/NON/10/2024","-","-", "RR-120-A", 100, "tray", 2000, "something", "out");
insert into order_products values ("001/SJK/NON/10/2024","-","-", "RR-100-A", 80, "tray", 100, "something", "out");

-- invoice out
select i.nomor_surat_jalan, o.storageCode, i.no_invoice, i.invoice_date, c.customerName, c.customerAddress, c.customerNPWP, i.no_faktur, op.productCode, p.productName, op.qty, op.UOM, op.price_per_UOM, (op.qty * op.price_per_UOM) AS nominal
from invoices i, orders o, order_products op, products p, customers c
where i.nomor_surat_jalan = "001/SJK/NON/10/2024"
and i.nomor_surat_jalan = o.nomor_surat_jalan
and i.nomor_surat_jalan = op.nomor_surat_jalan
and op.productCode = p.productCode
and o.customerCode = c.customerCode;

-- payment out
select i.nomor_surat_jalan, o.storageCode, i.no_invoice, i.invoice_date, c.customerName, c.customerAddress, c.customerNPWP, i.no_faktur, op.productCode, pr.productName, op.qty, op.UOM, op.price_per_UOM, (op.qty * op.price_per_UOM) AS nominal, p.payment_date, p.payment_amount 
from invoices i, payments p, orders o, order_products op, products pr, customers c
where p.nomor_surat_jalan = "001/SJK/NON/10/2024"
and p.nomor_surat_jalan = i.nomor_surat_jalan
and p.nomor_surat_jalan = o.nomor_surat_jalan
and p.nomor_surat_jalan = op.nomor_surat_jalan
and op.productCode = pr.productCode
and o.customerCode = c.customerCode;

-- SLIP tax out
insert into orders values ("002/SJK/NON/10/2024", "NON", "001/SJK/NON/10/2024", "001202130", "NON", "TOM", "2024-10-03", "12000213", "2");
insert into order_products values ("002/SJK/NON/10/2024","-","-", "RR-120-A", 800, "tray", 2000, "something tax", "out");
insert into order_products values ("002/SJK/NON/10/2024","-","-", "RR-100-A", 60, "tray", 100, "something tax", "out");

-- invoice tax out
select i.nomor_surat_jalan, o.storageCode, i.no_invoice, i.invoice_date, c.customerName, c.customerAddress, c.customerNPWP, i.no_faktur, op.productCode, p.productName, op.qty, op.UOM, op.price_per_UOM, (op.qty * op.price_per_UOM) AS nominal
from invoices i, orders o, order_products op, products p, customers c
where i.nomor_surat_jalan = "002/SJK/NON/10/2024"
and i.nomor_surat_jalan = o.nomor_surat_jalan
and i.nomor_surat_jalan = op.nomor_surat_jalan
and op.productCode = p.productCode
and o.customerCode = c.customerCode;

-- payment tax out
select i.nomor_surat_jalan, o.storageCode, i.no_invoice, i.invoice_date, c.customerName, c.customerAddress, c.customerNPWP, i.no_faktur, op.productCode, pr.productName, op.qty, op.UOM, op.price_per_UOM, (op.qty * op.price_per_UOM) AS nominal, p.payment_date, p.payment_amount 
from invoices i, payments p, orders o, order_products op, products pr, customers c
where p.nomor_surat_jalan = "002/SJK/NON/10/2024"
and p.nomor_surat_jalan = i.nomor_surat_jalan
and p.nomor_surat_jalan = o.nomor_surat_jalan
and p.nomor_surat_jalan = op.nomor_surat_jalan
and op.productCode = pr.productCode
and o.customerCode = c.customerCode;

-- slip repack
select r.no_repack, r.repack_date, r.storageCode, op.productCode, pr.productName, op.qty, op.UOM, op.price_per_UOM, op.note, op.product_status
from repacks r, order_products op, products pr
where r.no_repack = "001/SJR/APA/12/2024"
and r.no_repack = op.repack_no_repack
and op.productCode = pr.productCode;

-- slip pemindahan
select m.no_moving, m.moving_date, m.storageCodeSender, m.storageCodeReceiver, op.productCode, pr.productName, op.qty, op.UOM, op.price_per_UOM, (op.qty * op.price_per_UOM) AS nominal
from movings m, order_products op, products pr
where m.no_moving = "001/SJP/APA/12/2024"
and m.no_moving = op.moving_no_moving
and op.productCode = pr.productCode;


-- LAPORAN HUTANG
SELECT 
    i.invoice_date, 
    i.no_invoice, 
    v.vendorName, 
    pr.productName, 
    op.qty, 
    op.price_per_UOM, 
    (op.qty * op.price_per_UOM) AS nominal, 
    p.payment_date, 
    p.payment_amount, 
    (p.payment_amount - (op.qty * op.price_per_UOM)) AS remaining
FROM 
    orders o
JOIN 
    invoices i ON o.nomor_surat_jalan = i.nomor_surat_jalan
JOIN 
    vendors v ON o.vendorCode = v.vendorCode
JOIN 
    order_products op ON o.nomor_surat_jalan = op.nomor_surat_jalan
JOIN 
    products pr ON op.productCode = pr.productCode
LEFT JOIN 
    payments p ON o.nomor_surat_jalan = p.nomor_surat_jalan
WHERE 
    MONTH(i.invoice_date) = 7
    AND YEAR(i.invoice_date) = 2024
    AND o.storageCode = 'APA';




