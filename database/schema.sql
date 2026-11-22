CREATE DATABASE IF NOT EXISTS kids_party_planner
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE kids_party_planner;

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(80) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('parent', 'admin') NOT NULL DEFAULT 'parent',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vendors (
    vendor_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    service_type ENUM('Cake', 'Decorations', 'Entertainment', 'Photography') NOT NULL,
    price DECIMAL(7,0) UNSIGNED NOT NULL,
    contact_email VARCHAR(190) NOT NULL,
    PRIMARY KEY (vendor_id),
    KEY idx_vendors_service_type (service_type),
    KEY idx_vendors_name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS parties (
    party_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id INT UNSIGNED NOT NULL,
    child_name VARCHAR(80) NOT NULL,
    party_date DATE NOT NULL,
    theme VARCHAR(100) NOT NULL,
    budget DECIMAL(7,0) UNSIGNED NOT NULL,
    PRIMARY KEY (party_id),
    KEY idx_parties_parent_date (parent_id, party_date),
    CONSTRAINT fk_parties_parent FOREIGN KEY (parent_id)
        REFERENCES users (user_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    party_id INT UNSIGNED NOT NULL,
    vendor_id INT UNSIGNED NOT NULL,
    amount DECIMAL(7,0) UNSIGNED NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (booking_id),
    UNIQUE KEY uq_booking_party_vendor (party_id, vendor_id),
    KEY idx_bookings_vendor (vendor_id),
    CONSTRAINT fk_bookings_party FOREIGN KEY (party_id)
        REFERENCES parties (party_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_bookings_vendor FOREIGN KEY (vendor_id)
        REFERENCES vendors (vendor_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;
