ALTER TABLE orders
    ADD COLUMN client_id INT(11) NULL AFTER id,
    ADD COLUMN items_subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER customer_city,
    ADD COLUMN delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER items_subtotal,
    ADD COLUMN delivery_location VARCHAR(150) DEFAULT NULL AFTER delivery_fee,
    ADD KEY idx_orders_client_id (client_id);

ALTER TABLE orders
    ADD CONSTRAINT fk_orders_client_id
    FOREIGN KEY (client_id) REFERENCES client(id)
    ON DELETE SET NULL;
