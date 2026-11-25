CREATE DATABASE IF NOT EXISTS bdClub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE bdClub;

CREATE TABLE IF NOT EXISTS asociados (
    documento VARCHAR(20) NOT NULL PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fechaNacimiento DATE NOT NULL,
    genero VARCHAR(30) NOT NULL,
    direccionResidencia VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(30) NOT NULL DEFAULT 'asociado'
) ENGINE=InnoDB;
