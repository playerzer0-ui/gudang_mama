create table gudang(
    gudangID int not null auto_increment,
    nama_gudang varchar(100),
    alamat_gudang1 varchar(100),
    alamat_gudang2 varchar(100),
    alamat_gudang3 varchar(100),
    primary key(gudangID)
);

create table barang(
    barangID int not null auto_increment,
    nama_barang varchar(100),
    jumlah int not null,
    primary key(barangID)
);

create table customer(
    customerID int not null auto_increment,
    nama_customer varchar(100),
    npwp varchar(30) not null,
    alamat_customer1 varchar(100),
    alamat_customer2 varchar(100),
    alamat_customer3 varchar(100),
    primary key(customerID)
);

create table vendor(
    vendorID int not null auto_increment,
    nama_vendor varchar(100),
    alamat_vendor1 varchar(100),
    alamat_vendor2 varchar(100),
    alamat_vendor3 varchar(100),
    npwp varchar(30) not null,
    primary key(vendorID)
);

create table user(
    userID int not null auto_increment,
    username varchar(100),
    password varchar(255),
    userType int not null,
    primary key(userID)
);

create table orders(
    orderID int not null auto_increment,
    gudangID int not null,
    customerID int not null,
    vendorID int not null,
    tanggal DATE,
    nomor_surat_jalan varchar(120),
    status_mode int not null,
    primary key(orderID),
    foreign key(gudangID) references gudang(gudangID),
    foreign key(customerID) references customer(customerID),
    foreign key(vendorID) references vendor(vendorID)
);

create table order_barang(
    orderID int not null,
    barangID int not null,
    jumlah int,
    harga decimal(30, 2),
    unit varchar(10),
    foreign key(orderID) references orders(orderID),
    foreign key(barangID) references barang(barangID)
);

insert into barang(nama_barang, jumlah) values("MM-120-A", 120);
insert into barang(nama_barang, jumlah) values("MK-120-A", 20);
insert into barang(nama_barang, jumlah) values("RR-120-A", 80);
insert into barang(nama_barang, jumlah) values("RF-120-A", 100);
insert into barang(nama_barang, jumlah) values("BB-120-A", 90);
insert into barang(nama_barang, jumlah) values("AA-120-A", 120);
insert into barang(nama_barang, jumlah) values("OO-120-A", 20);
insert into barang(nama_barang, jumlah) values("R-120-A", 80);
insert into barang(nama_barang, jumlah) values("F-120-A", 100);
insert into barang(nama_barang, jumlah) values("PA-120-A", 90);

insert into customer(nama_customer, npwp, alamat_customer1, alamat_customer2, alamat_customer3) values("dedy", "111.111.111.111-111", "pondok", "blimbing", "malang");
insert into customer(nama_customer, npwp, alamat_customer1, alamat_customer2, alamat_customer3) values("toby", "111.111.111.111-112", "teras", "nanas", "surabaya");
insert into customer(nama_customer, npwp, alamat_customer1, alamat_customer2, alamat_customer3) values("asd", "111.111.111.111-113", "kursi", "apel", "singapur");
insert into customer(nama_customer, npwp, alamat_customer1, alamat_customer2, alamat_customer3) values("tre", "111.111.111.111-114", "sofa", "jeruk", "yuj");
insert into customer(nama_customer, npwp, alamat_customer1, alamat_customer2, alamat_customer3) values("tony", "111.111.111.111-115", "meja", "papaya", "aaa");
insert into customer(nama_customer, npwp, alamat_customer1, alamat_customer2, alamat_customer3) values("okn", "111.111.111.111-116", "papan", "pisang", "qq");
insert into customer(nama_customer, npwp, alamat_customer1, alamat_customer2, alamat_customer3) values("pla", "111.111.111.111-117", "pensil", "garam", "ewrw");
insert into customer(nama_customer, npwp, alamat_customer1, alamat_customer2, alamat_customer3) values("arr", "111.111.111.111-118", "hapus", "merica", "jkasd");

insert into gudang(nama_gudang, alamat_gudang1, alamat_gudang2, alamat_gudang3) values("gudang garam", "pondok", "blimbing", "malang");
insert into gudang(nama_gudang, alamat_gudang1, alamat_gudang2, alamat_gudang3) values("APA", "ertiga", "jln ratulangi", "kota");
insert into gudang(nama_gudang, alamat_gudang1, alamat_gudang2, alamat_gudang3) values("BLT", "suss", "jln sam", "tengger");

insert into vendor(nama_vendor, alamat_vendor1, alamat_vendor2, alamat_vendor3, npwp) values("wismilak", "pondok", "blimbing", "malang", "111.111.111.100-114");
insert into vendor(nama_vendor, alamat_vendor1, alamat_vendor2, alamat_vendor3, npwp) values("ETC", "ass", "nana", "surabaya", "111.111.111.100-115");
insert into vendor(nama_vendor, alamat_vendor1, alamat_vendor2, alamat_vendor3, npwp) values("garam", "drg", "tyr", "thor", "111.111.111.100-154");

insert into orders(gudangID, customerID, vendorID, tanggal, nomor_surat_jalan, status_mode) values(1, 1, 1, "2021-11-11", "IM0001231321", 1);
insert into orders(gudangID, customerID, vendorID, tanggal, nomor_surat_jalan, status_mode) values(1, 2, 1, "2021-11-11", "IM002132131", 2);
insert into orders(gudangID, customerID, vendorID, tanggal, nomor_surat_jalan, status_mode) values(1, 2, 3, "2021-11-11", "IM0123231213", 3);

insert into order_barang values(1, 2, 200, 120000000.88, "TRAY");
insert into order_barang values(1, 3, 100, 199900000.88, "TRAY");