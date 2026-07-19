USE supermarket_db;

-- Populate Staff
-- NOTE: both accounts share the password "password123" — this is a real, working
-- bcrypt hash (verified with PHP's password_verify), not a placeholder string.
INSERT INTO staff (first_name, last_name, email, password_hash, role) VALUES
('John', 'Doe', 'john.mngr@maxmart.com', '$2b$10$lOSnGwDkd47rc1QktNlOjuXL2wjDZ3Xyt34EK1h1DU6bvYkwbmjwW', 'Manager'),
('Jane', 'Smith', 'jane.cash@maxmart.com', '$2b$10$lOSnGwDkd47rc1QktNlOjuXL2wjDZ3Xyt34EK1h1DU6bvYkwbmjwW', 'Cashier');

-- Populate Categories
INSERT INTO categories (category_name, description) VALUES
('Dairy', 'Milk, butter, cheese, and yogurt items'),
('Bakery', 'Freshly baked breads, pastries, and cakes'),
('Beverages', 'Soft drinks, juices, coffee, and water');

-- Populate Suppliers
INSERT INTO suppliers (company_name, contact_name, phone) VALUES
('Global Dairy Distribs', 'Robert Milkman', '+1-555-0192'),
('Apex Baking Co.', 'Sarah Baker', '+1-555-0143');

-- Populate Products
INSERT INTO products (sku, product_name, category_id, supplier_id, price, stock_quantity, reorder_level) VALUES
('880101', 'Organic Whole Milk 1L', 1, 1, 3.49, 45, 15),
('880102', 'Salted Butter 250g', 1, 1, 2.99, 60, 20),
('880201', 'Whole Wheat Bread', 2, 2, 2.49, 12, 10),
('880301', 'Sparkling Water 500ml', 3, NULL, 1.19, 120, 30);

-- Populate a Sample Order
INSERT INTO orders (staff_id, total_amount) VALUES (2, 9.97);

-- Populate Order Items
INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 2, 3.49),
(1, 2, 1, 2.99);