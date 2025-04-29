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
