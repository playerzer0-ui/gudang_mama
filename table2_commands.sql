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
    no_moving varchar(80),
    tax double,
    foreign key(nomor_surat_jalan) references orders(nomor_surat_jalan),
    foreign key(no_moving) references orders(no_moving)
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
    product_status varchar(20),
    foreign key(nomor_surat_jalan) references orders(nomor_surat_jalan),
    foreign key(moving_no_moving) references movings(no_moving),
    foreign key(repack_no_repack) references repacks(no_repack),
    foreign key(productCode) references products(productCode)
);

create table saldos(
    productCode varchar(10) not null,
    storageCode varchar(10) not null,
    totalQty int,
    price_per_qty decimal(30, 2),
    saldoMonth int,
    saldoYear int,
    saldoCount int,
    foreign key(productCode) references products(productCode),
    foreign key(storageCode) references storages(storageCode)
)


-- insert data

insert into storages values ("NON", "none", "none", "001.111.111.111-111");

insert into vendors values ("NON", "none", "none", "000.111.111.111-111");

insert into customers values ("NON", "none", "none", "000.111.111.111-111");

insert into orders values ("-", "NON", "-", "-", "NON", "NON", null, "-", "0");

insert into repacks values ("-", NULL, "NON");

insert into movings values ("-", NULL, "NON", "NON");

INSERT INTO `users` (`userID`, `username`, `password`, `userType`) VALUES
('37d72912-5ad0-11ef-b5d1-5cbaef99b658', 'admin1', '$2y$10$I6HDp20xfQ.eyexX6Xu0XOmiCwmPmVGf7WuNTF6LApGFg0kxVcbIG', 1),
('3de53767-5ad0-11ef-b5d1-5cbaef99b658', 'user1', '$2y$10$5Ymv4R2Qn3Fw/8FKRJxHmu5XAmO2G0mfXTK2naenRcsssZuvPBLVa', 0);
