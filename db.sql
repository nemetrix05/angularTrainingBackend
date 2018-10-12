CREATE DATABASE IF NOT EXISTS angular_api;
USE angular_api;

CREATE TABLE motosnuevas(
    id int(255) auto_increment not null,
    marca varchar(255),
    descripcion text,
    precio varchar(255),
    imagen varchar(255),
    catalogo varchar(255),
    CONSTRAINT key_moto PRIMARY KEY(id)
)ENGINE=InnoDb;