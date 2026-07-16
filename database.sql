-- ============================================================
-- Integrated Engineers Point (IEP) - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS iep_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE iep_db;

-- Enquiries Table
CREATE TABLE IF NOT EXISTS enquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    service VARCHAR(100) NOT NULL,
    requirement TEXT NOT NULL,
    status ENUM('new', 'in_progress', 'resolved', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Feedback / Testimonials Table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(150),
    service_used VARCHAR(100),
    rating TINYINT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    message TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(150) NOT NULL,
    short_desc TEXT,
    icon VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0
);

-- Clients Table
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    logo_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1
);

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Seed Data
-- ============================================================

INSERT INTO services (slug, title, short_desc, icon, display_order) VALUES
('split-ac', 'Split AC Services', 'Installation, repair & maintenance for all types of split AC units.', 'fa-snowflake', 1),
('cassette-ac', 'Cassette AC Services', 'Complete solutions for cassette AC installation & servicing.', 'fa-th-large', 2),
('ducted-ac', 'Ducted AC Services', 'Duct design, installation & maintenance services.', 'fa-wind', 3),
('hvac', 'HVAC Services', 'End-to-end HVAC solutions for all types of buildings.', 'fa-building', 4),
('chiller', 'Chiller Unit Services', 'Chiller installation, maintenance & repair services.', 'fa-temperature-low', 5),
('amc', 'AMC / Maintenance', 'Annual Maintenance Contracts for hassle-free cooling year-round.', 'fa-tools', 6),
('breakdown', 'AC Breakdown & Repair', 'Quick diagnosis & repair at your doorstep, 24/7.', 'fa-wrench', 7),
('vrv-vrf', 'VRV / VRF Systems', 'Installation, repair & maintenance of VRV/VRF ventilation systems.', 'fa-network-wired', 8);

INSERT INTO clients (name) VALUES
('Tata'), ('DLF Building India'), ('Godrej'), ('Larsen & Toubro'),
('Adani'), ('Maruti Suzuki'), ('Honda'), ('Wipro');

-- Sample approved feedback
INSERT INTO feedback (customer_name, service_used, rating, message, is_approved) VALUES
('Rajesh Kumar', 'Split AC Services', 5, 'Excellent service! The technician arrived on time and fixed my AC within an hour. Highly recommended.', 1),
('Priya Sharma', 'AMC / Maintenance', 5, 'Been using their AMC for 2 years now. Always prompt, professional, and thorough. Great team!', 1),
('Amit Patel', 'Chiller Unit Services', 4, 'Very professional team. They handled our industrial chiller unit expertly. Will definitely use again.', 1),
('Sunita Verma', 'HVAC Services', 5, 'Integrated Engineers Point installed complete HVAC for our office. Superb quality and on-time delivery.', 1),
('Vikram Singh', 'AC Breakdown & Repair', 5, 'Called them at 9 PM for emergency AC repair. They came within the hour. Truly 24/7 support!', 1),
('Meena Joshi', 'Cassette AC Services', 4, 'Good service, genuine spare parts used. Pricing is transparent with no hidden charges.', 1);

-- Default admin user (password: admin@123)
INSERT INTO admin_users (username, password_hash, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');
