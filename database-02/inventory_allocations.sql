-- Additive migration. Back up the database first; do not seed or rewrite inventory.
CREATE TABLE IF NOT EXISTS inventory_allocations (
 order_reference VARCHAR(100) NOT NULL,
 product_id INT NOT NULL,
 quantity INT NOT NULL,
 state VARCHAR(16) NOT NULL DEFAULT 'held',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (order_reference, product_id),
 INDEX inventory_state (state)
) ENGINE=InnoDB;
