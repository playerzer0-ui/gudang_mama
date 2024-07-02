create table user(
    userID int not null auto_increment,
    username varchar(100),
    password varchar(255),
    userType int not null,
    primary key(userID)
);

create table storages(
    storageCode varchar(10) unique,
    storageName varchar(80),
    storageAddress varchar(120),
    storageNPWP varchar(30),
    primary key(storageCode)
);

create table vendors(
    vendorCode varchar(10) unique,
    vendorName varchar(100),
    vendorAddress varchar(120),
    vendorNPWP varchar(30),
    primary key(vendorCode)
);

create table customers(
    customerCode varchar(10) unique,
    customerName varchar(100),
    customerAddress varchar(120),
    customerNPWP varchar(30),
    primary key(customerCode)
);

create table products(
    productCode varchar(10) unique,
    productName varchar(100),
    primary key(productCode)
);

create table orders(
    nomor_surat_jalan varchar(80) unique,
    storageCode varchar(10),
    no_LPB varchar(80),
    no_truk varchar(80),
    vendorCode varchar(10),
    customerCode varchar(10),
    order_date DATE,
    purchase_order varchar(80),
    status_mode int,
    primary key(nomor_surat_jalan),
    foreign key(storageCode) references storages(storageCode),
    foreign key(vendorCode) references vendors(vendorCode),
    foreign key(customerCode) references customers(customerCode)
);

create table invoices(
    nomor_surat_jalan varchar(80),
    invoice_date DATE,
    no_invoice varchar(80),
    no_faktur varchar(80),
    foreign key(nomor_surat_jalan) references orders(nomor_surat_jalan)
);

create table payments(
    nomor_surat_jalan varchar(80),
    payment_date DATE,
    payment_amount decimal(30,2),
    foreign key(nomor_surat_jalan) references orders(nomor_surat_jalan)
);

create table repacks(
    no_repack varchar(80) unique,
    repack_date DATE,
    storageCode varchar(10),
    primary key(no_repack),
    foreign key(storageCode) references storages(storageCode)
);

create table movings(
    no_moving varchar(80) unique,
    moving_date date,
    storageCodeSender varchar(10),
    storageCodeReceiver varchar(10),
    primary key(no_moving),
    foreign key(storageCodeSender) references storages(storageCode),
    foreign key(storageCodeReceiver) references storages(storageCode)
);

create table order_products(
    nomor_surat_jalan varchar(80),
    moving_no_moving VARCHAR(80),
    repack_no_repack VARCHAR(80),
    productCode varchar(10),
    qty int,
    UOM varchar(10),
    price_per_UOM decimal(30,2),
    note varchar(80),
    product_status varchar(10),
    foreign key(nomor_surat_jalan) references orders(nomor_surat_jalan),
    foreign key(moving_no_moving) references movings(no_moving),
    foreign key(repack_no_repack) references repacks(no_repack),
    foreign key(productCode) references products(productCode)
);

-- insert data

insert into storages values ("NON", "none", "none", "001.111.111.111-111");
insert into storages values ("CBA", "Catur Berkat Amartya", "jalan catur berkat", "002.111.111.111-111");
insert into storages values ("APA", "Agraprana Paramitha Amartya", "jalan agraprana", "003.111.111.111-111");
insert into storages values ("RBL", "Rizky Berkah Lumintu", "jalan Rizky Berkah", "004.111.111.111-111");
insert into storages values ("BBB", "Berkah Berbagi Berkat", "jalan Berkah Berbagi", "005.111.111.111-111");

insert into vendors values ("NON", "none", "none", "000.111.111.111-111");
insert into vendors values ("WIM", "Wismilak Inti Makmur", "jln Wismilak Inti", "100.111.111.111-111");
insert into vendors values ("ASTRA", "As Astra", "jln astra", "100.90.111.111-111");
insert into vendors values ("COC", "Coca cola", "jln coca cola", "100.111.111.199-111");
insert into vendors values ("NVIDIA", "video graphics", "jln video", "100.90.111.111-111");

insert into customers values ("NON", "none", "none", "000.111.111.111-111");
insert into customers values ("DED", "dedi", "jln ratulangi", "123.111.111.111-111");
insert into customers values ("TOM", "tomi", "jln seen", "123.112.111.111-111");
insert into customers values ("ZEN", "zeno", "jln asdf", "123.001.111.111-111");

insert into products values ("RR-120-A", "Reguler Mono 120 Acetatow");
insert into products values ("RR-100-A", "Reguler Mono 100 Acetatow");
insert into products values ("RF-120-A", "Reguler Falvour 120 Acetatow");
insert into products values ("RF-100-A", "Reguler Falvour 100 Acetatow");

insert into orders values ("-", "NON", "-", "-", "NON", "NON", null, "-", "0");
insert into orders values ("001/SJJ/SOME/12/2024", "CBA", "001/LPB/CBA/12/2024", "001202130", "WIM", "NON", "2024-12-03", "1212213", "1");
insert into orders values ("002/SJJ/DOE/12/2024", "APA", "001/LPB/APA/10/2024", "001202130", "WIM", "NON", "2024-12-03", "12312213", "1");
insert into orders values ("001/SJK/NON/10/2024", "NON", "001/SJK/NON/10/2024", "001202130", "NON", "DED", "2024-10-03", "12312213", "2");
insert into orders values ("002/SJK/NON/10/2024", "NON", "001/SJK/NON/10/2024", "001202130", "NON", "TOM", "2024-10-03", "12000213", "2");

insert into invoices values ("001/SJJ/SOME/12/2024", "2024-12-03", "001", "99.99.12312-213");
insert into invoices values ("002/SJJ/DOE/12/2024", "2024-12-03", "002", "99.99.12312-213");
insert into invoices values ("001/SJK/NON/10/2024", "2024-10-03", "003", "99.99.12312-213");
insert into invoices values ("002/SJK/NON/10/2024", "2024-12-03", "004", "99.99.12312-213");

insert into payments values ("001/SJJ/SOME/12/2024", "2024-12-03", "123231");
insert into payments values ("002/SJJ/DOE/12/2024", "2024-12-03", "3000000");
insert into payments values ("001/SJK/NON/10/2024", "2024-10-03", "223112");
insert into payments values ("002/SJK/NON/10/2024", "2024-12-03", "55555555555");

insert into repacks values ("-", NULL, "NON");
insert into repacks values ("001/SJR/APA/12/2024", "2024-12-03", "APA");
insert into repacks values ("002/SJR/BBB/12/2024", "2024-12-03", "BBB");
insert into repacks values ("003/SJR/CBA/12/2024", "2024-12-03", "CBA");

insert into movings values ("-", NULL, "NON", "NON");
insert into movings values ("001/SJP/APA/12/2024", "2024-12-03", "APA", "BBB");
insert into movings values ("002/SJP/BBB/12/2024", "2024-12-03", "BBB", "CBA");
insert into movings values ("003/SJP/CBA/12/2024", "2024-12-03", "CBA", "APA");

insert into order_products values ("001/SJJ/SOME/12/2024","-","-", "RR-120-A", 100, "tray", 2000, "something", "in");
insert into order_products values ("001/SJJ/SOME/12/2024","-","-", "RR-100-A", 80, "tray", 100, "something", "in");

insert into order_products values ("001/SJK/NON/10/2024","-","-", "RR-120-A", 100, "tray", 2000, "something", "out");
insert into order_products values ("001/SJK/NON/10/2024","-","-", "RR-100-A", 80, "tray", 100, "something", "out");
insert into order_products values ("002/SJK/NON/10/2024","-","-", "RR-120-A", 800, "tray", 2000, "something tax", "out");
insert into order_products values ("002/SJK/NON/10/2024","-","-", "RR-100-A", 60, "tray", 100, "something tax", "out");

insert into order_products values ("-", "-", "001/SJR/APA/12/2024", "RR-100-A", 100, "tray", 100, "repack awal", "awal");
insert into order_products values ("-", "-", "001/SJR/APA/12/2024", "RR-100-A", 100, "tray", 200, "repack last", "akhir");
insert into order_products values ("-", "-", "001/SJR/APA/12/2024", "RF-100-A", 100, "tray", 200, "repack last", "akhir");

insert into order_products values ("-", "001/SJP/APA/12/2024", "-", "RR-120-A", 100, "tray", 2000, "moving", "move");
insert into order_products values ("-", "001/SJP/APA/12/2024", "-", "RR-100-A", 100, "tray", 100, "moving", "move");
insert into order_products values ("-", "001/SJP/APA/12/2024", "-", "RF-100-A", 100, "tray", 200, "moving", "move");
