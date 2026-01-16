-- Script para agregar el campo 'titulo' a la tabla carpetas_fisicas
-- Ejecutar en phpMyAdmin o MySQL

USE `sdi_gestion_documental`;

-- Agregar la columna titulo después de no_carpeta_fisica
ALTER TABLE `carpetas_fisicas` 
ADD COLUMN `titulo` VARCHAR(150) NOT NULL UNIQUE 
AFTER `no_carpeta_fisica`;

-- Agregar datos ficticios para las carpetas existentes
UPDATE `carpetas_fisicas` 
SET `titulo` = CONCAT('Carpeta ', `no_carpeta_fisica`, ' - ', `etiqueta_identificadora`)
WHERE `titulo` IS NULL OR `titulo` = '';

-- Verificar que el cambio se aplicó correctamente
SELECT * FROM `carpetas_fisicas` LIMIT 5;
