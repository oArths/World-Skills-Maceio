drop database if exists AlatechMachines;
create database AlatechMachines;
use AlatechMachines;

create table user (
	id int auto_increment not null,
    username varchar(64) not null,
    password varchar(512) not null,
    accessToken varchar(512) not null,
    constraint primary key(id)
);

create table brand (
	id int auto_increment not null,
    name varchar(96) not null,
    constraint primary key(id)
);

create table socketType (
	id int auto_increment not null,
    name varchar(96) not null,
    constraint primary key(id)
);

create table ramMemoryType (
	id int auto_increment not null,
    name varchar(96) not null,
    constraint primary key(id)
);

create table motherboard (
	id int auto_increment not null,
    name varchar(96) not null,
    imageUrl varchar(512) not null,
    brandId int not null,
    socketTypeId int not null,
    ramMemoryTypeId int not null,
    ramMemorySlots int not null,
    maxTdp int not null,
    sataSlots int not null,
    m2Slots int not null,
    pciSlots int not null,
    constraint foreign key(ramMemoryTypeId) references ramMemoryType(id),
    constraint foreign key(socketTypeId) references socketType(id),
    constraint foreign key(brandId) references brand(id),
    constraint primary key(id)
);

create table processor (
	id int auto_increment not null,
    name varchar(96) not null,
    imageUrl varchar(512) not null,
    brandId int not null,
    socketTypeId int not null,
    cores int not null,
    baseFrequency float not null,
    maxFrequency float not null,
    cacheMemory float not null,
    tdp int not null,
    constraint foreign key(socketTypeId) references socketType(id),
    constraint foreign key(brandId) references brand(id),
    constraint primary key(id)
);

create table ramMemory (
	id int auto_increment not null,
    name varchar(96) not null,
    imageUrl varchar(512) not null,
    brandId int not null,
    size int not null,
    ramMemoryTypeId int not null,
    frequency float not null,
    constraint foreign key(ramMemoryTypeId) references ramMemoryType(id),
    constraint foreign key(brandId) references brand(id),
    constraint primary key(id)
);

create table storageDevice (
	id int auto_increment not null,
    name varchar(96) not null,
    imageUrl varchar(512) not null,
    brandId int not null,
    storageDeviceType enum('hdd', 'ssd') not null,
    size int not null,
    storageDeviceInterface enum('sata', 'm2') not null,
    constraint foreign key(brandId) references brand(id),
    constraint primary key(id)
);

create table graphicCard (
	id int auto_increment not null,
    name varchar(96) not null,
    imageUrl varchar(512) not null,
    brandId int not null,
    memorySize int not null,
    memoryType enum('gddr5', 'gddr6') not null,
    minimumPowerSupply int not null,
    supportMultiGpu bool not null,
    constraint foreign key(brandId) references brand(id),
    constraint primary key(id)
);

create table powerSupply (
	id int auto_increment not null,
    name varchar(96) not null,
    imageUrl varchar(512) not null,
    brandId int not null,
    potency int not null,
    badge80Plus enum('none', 'white', 'bronze', 'silver', 'gold', 'platinum', 'titanium') not null,
    constraint foreign key(brandId) references brand(id),
    constraint primary key(id)
);

create table machine (
	id int auto_increment not null,
    name varchar(96) not null,
    description varchar(512) not null,
    imageUrl varchar(512) not null,
    motherboardId int not null,
    processorId int not null,
    ramMemoryId int not null,
    ramMemoryAmount int not null,
    graphicCardId int not null,
    graphicCardAmount int not null,
    powerSupplyId int not null,
    constraint foreign key(motherboardId) references motherboard(id),
    constraint foreign key(processorId) references processor(id),
    constraint foreign key(ramMemoryId) references ramMemory(id),
    constraint foreign key(graphicCardId) references graphicCard(id),
    constraint foreign key(powerSupplyId) references powerSupply(id),
    constraint primary key(id)
);

create table machineHasStorageDevice (
	machineId int not null,
    storageDeviceId int not null,
    amount int not null,
    constraint foreign key(machineId) references machine(id) on delete no action,
    constraint foreign key(storageDeviceId) references storageDevice(id) on delete no action,
    constraint primary key(machineId, storageDeviceId)
);
