CREATE DATABASE IF NOT EXISTS db_test;
USE db_test;

CREATE TABLE ejecutivo (
    id_eje INT(11) AUTO_INCREMENT PRIMARY KEY,
    nom_eje VARCHAR(255) NOT NULL,
    tel_eje VARCHAR(15) NOT NULL,
    eli_eje INT DEFAULT 1,
    id_padre INT NULL,
    FOREIGN KEY (id_padre) REFERENCES ejecutivo(id_eje)
);

CREATE TABLE plantel (
	id_pla INT(11) auto_increment PRIMARY KEY,
    nom_pla VARCHAR(100) NOT NULL
);

CREATE TABLE ejecutivo_plantel (
	id_eje_pla INT(11) auto_increment primary KEY,
    id_eje INT NOT NULL,
    id_pla INT NOT NULL,
    FOREIGN KEY (id_eje) REFERENCES ejecutivo(id_eje),
    FOREIGN KEY (id_pla) REFERENCES plantel(id_pla)
);

CREATE TABLE citas (
	id_cit INT(11) auto_increment primary key,
    nom_cit VARCHAR(100) NOT NULL,
    fec_cit DATETIME NOT NULL,
    id_eje INT NOT NULL,
    id_pla INT NOT NULL,
    FOREIGN KEY (id_eje) REFERENCES ejecutivo(id_eje),
    FOREIGN KEY (id_pla) REFERENCES plantel(id_pla)
);