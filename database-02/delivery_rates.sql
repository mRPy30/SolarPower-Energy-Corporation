CREATE TABLE IF NOT EXISTS delivery_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin_address VARCHAR(255) DEFAULT 'Madrigal Business Park, Alabang, Muntinlupa',
    rate_type VARCHAR(50),
    location_name VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'km_range', 'Metro Manila 1-5 km', 2000.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'km_range' AND location_name = 'Metro Manila 1-5 km');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'km_range', 'Metro Manila 6-10 km', 2500.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'km_range' AND location_name = 'Metro Manila 6-10 km');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'km_range', 'Metro Manila 11-20 km', 4000.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'km_range' AND location_name = 'Metro Manila 11-20 km');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'km_range', 'Metro Manila 21-30 km', 6000.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'km_range' AND location_name = 'Metro Manila 21-30 km');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'province', 'Cavite', 4200.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'province' AND location_name = 'Cavite');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'province', 'Laguna', 6000.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'province' AND location_name = 'Laguna');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'province', 'Batangas', 8500.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'province' AND location_name = 'Batangas');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'province', 'Rizal', 7000.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'province' AND location_name = 'Rizal');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'province', 'Bulacan', 7000.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'province' AND location_name = 'Bulacan');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'province', 'Pampanga', 10000.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'province' AND location_name = 'Pampanga');

INSERT INTO delivery_rates (origin_address, rate_type, location_name, price)
SELECT 'Madrigal Business Park, Alabang, Muntinlupa', 'province', 'Tarlac', 10000.00
WHERE NOT EXISTS (SELECT 1 FROM delivery_rates WHERE rate_type = 'province' AND location_name = 'Tarlac');
