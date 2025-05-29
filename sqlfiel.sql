
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    dob DATE NOT NULL,
    phone VARCHAR(20) NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    role ENUM('staff','customer','mechanic') DEFAULT 'customer',
    joinAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE vehicles (
    id INT(11) NOT NULL AUTO_INCREMENT,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT(11) NOT NULL,
    license_plate VARCHAR(20) NOT NULL UNIQUE,
    color VARCHAR(30),
    mileage INT(11),
    status ENUM('available', 'reserved', 'maintenance', 'unavailable') DEFAULT 'available',
    daily_rate DECIMAL(10,2) NOT NULL,
    seating_capacity INT(11),
    transmission ENUM('automatic', 'manual'),
    fuel_type ENUM('petrol', 'diesel', 'electric', 'hybrid'),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    description TEXT,
    PRIMARY KEY (id)
);

CREATE TABLE vehicle_images (
    id INT(11) NOT NULL AUTO_INCREMENT,
    vehicle_id INT(11) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    name VARCHAR(100) DEFAULT 'name',
    PRIMARY KEY (id),
    KEY vehicle_id (vehicle_id),
    CONSTRAINT fk_vehicle_images_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

CREATE TABLE vehicle_features (
    id INT(11) NOT NULL AUTO_INCREMENT,
    vehicle_id INT(11) NOT NULL,
    feature VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    KEY vehicle_id (vehicle_id),
    CONSTRAINT fk_vehicle_features_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    vehicle_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    booking_date DATE NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Cancelled') DEFAULT 'Pending',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY vehicle_id (vehicle_id),
    KEY user_id (user_id),
    CONSTRAINT fk_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE car_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    feedback TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES vehicles(id) ON DELETE CASCADE
);
ALTER TABLE car_feedback ADD COLUMN user_id INT NOT NULL AFTER car_id;

CREATE TABLE maintenance_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    vehicle_id INT(11) NOT NULL,
    description TEXT DEFAULT NULL,
    cost DECIMAL(10,2) DEFAULT 0.00,
    priority ENUM('low', 'moderate', 'high') DEFAULT 'moderate',
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    requested_by INT(11) DEFAULT NULL,
    assigned_to INT(11) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY vehicle_id (vehicle_id),
    KEY requested_by (requested_by),
    KEY assigned_to (assigned_to)
);


-- insert 
-- Insert 2 staff users
INSERT INTO users (first_name, last_name, email, password, dob, phone, license_number, address, role)
VALUES 
('Alice', 'Brown', 'staff1@test.com', '123456', '1990-01-01', '1234567890', 'LIC001', '123 Main St, City A', 'staff'),
('Bob', 'Smith', 'staff2@test.com', '123456', '1985-05-10', '0987654321', 'LIC002', '456 Elm St, City B', 'staff');

-- Insert 2 mechanic users
INSERT INTO users (first_name, last_name, email, password, dob, phone, license_number, address, role)
VALUES 
('Charlie', 'Davis', 'mechanic1@test.com', '123456', '1992-07-15', '5556667777', 'LIC003', '789 Oak St, City C', 'mechanic'),
('Diana', 'Evans', 'mechanic2@test.com', '123456', '1988-03-22', '4443332222', 'LIC004', '321 Pine St, City D', 'mechanic');
