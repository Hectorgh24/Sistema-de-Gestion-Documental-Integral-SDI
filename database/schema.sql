-- BASE DE DATOS: SDI_Gestion_Documental
-- Script SQL Definitivo - Compatible con MySQL/InfinityFree
-- IMPORTANTE: Esta es la estructura DEFINITIVA. No modificar sin autorización.


-- MÓDULO DE SEGURIDAD
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- MÓDULO DE NEGOCIO (Basado en PDF)
CREATE TABLE carpetas_fisicas (
    id_carpeta INT AUTO_INCREMENT PRIMARY KEY,
    no_carpeta_fisica INT NOT NULL UNIQUE,
    carpeta_label VARCHAR(255) NOT NULL UNIQUE,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE documentos_auditoria (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    id_carpeta INT NOT NULL,
    no_oficio VARCHAR(100) NOT NULL,
    seguimiento_oficio VARCHAR(255) NULL,
    auditoria VARCHAR(255) NULL,
    emitido_por VARCHAR(255) NOT NULL,
    fecha_oficio DATE NOT NULL,
    descripcion TEXT NOT NULL,
    capturado_por VARCHAR(255) NOT NULL,
    respaldo_estado ENUM('pendiente', 'respaldado') NOT NULL DEFAULT 'pendiente',
    fecha_archivo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    archivo_url VARCHAR(2048) NULL,
    estado VARCHAR(50) NULL,
    comentario TEXT NULL,
    UNIQUE KEY uq_oficio_carpeta (no_oficio, id_carpeta),
    CONSTRAINT fk_documentos_carpetas FOREIGN KEY (id_carpeta) REFERENCES carpetas_fisicas (id_carpeta) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- SEED DATA
INSERT INTO roles (nombre_rol, descripcion) VALUES 
    ('Administrador', 'Total'),
    ('Academico', 'Gestión'),
    ('Alumno', 'Consulta');

INSERT INTO carpetas_fisicas (no_carpeta_fisica, carpeta_label) VALUES 
    (1, '2025_Auditoria_General');

-- Nota: Crear usuario admin manualmente con hash en PHP usando password_hash()

