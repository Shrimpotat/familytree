-- MySQL schema for the Family Tree app
-- Create the database first, then run this script.

CREATE DATABASE IF NOT EXISTS familytree CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE familytree;

CREATE TABLE IF NOT EXISTS persons (
    id VARCHAR(64) NOT NULL,
    legacy_id VARCHAR(64) DEFAULT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    birth_date VARCHAR(50) DEFAULT NULL,
    death_date VARCHAR(50) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    father_id VARCHAR(64) DEFAULT NULL,
    mother_id VARCHAR(64) DEFAULT NULL,
    spouse_id VARCHAR(64) DEFAULT NULL,
    family_name VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_legacy_id (legacy_id),
    INDEX idx_name (first_name, last_name),
    INDEX idx_father_id (father_id),
    INDEX idx_mother_id (mother_id),
    INDEX idx_spouse_id (spouse_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
