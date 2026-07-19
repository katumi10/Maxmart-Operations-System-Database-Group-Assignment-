# Maxmart-Operations-System-Database-Group-Assignment-
**DATABASE ASSIGNMENT**

Enterprise Database Design, Implementation & Web Integration

**Case Study: MaxMart Supermarket — Accra, Ghana**

**<u>GROUP MEMBERS</u>**

Donkor Shepherd Mensah 2425404178

Katumi Amadu 2425401702

Agyapong Epaphras Daleku Mawufemor 2425401280

Jason Amoah Nyarko 2425402719

Cornelius Tetteh Tetteh 2425400662

Dennis Adu Denkyira 2425403696

Kweku Nyamekye Sarfo-Mensah 2425401408

Nana Yaa Serwaa Acheampong 2425401435

## **Table of Contents**

[**Table of Contents** [1](#table-of-contents)](#table-of-contents)

[**Phase 1 — Business Requirements Specification**
[1](#phase-1-business-requirements-specification)](#phase-1-business-requirements-specification)

[**1.1 Company Profile** [1](#company-profile)](#company-profile)

[**1.2 Narrative of System Requirements**
[1](#narrative-of-system-requirements)](#narrative-of-system-requirements)

[**1.3 Key Business Rules**
[1](#key-business-rules)](#key-business-rules)

[**Phase 2 — Entity Types & Attribute Identification**
[1](#phase-2-entity-types-attribute-identification)](#phase-2-entity-types-attribute-identification)

[**Phase 3 — Data Integrity and Constraints**
[1](#phase-3-data-integrity-and-constraints)](#phase-3-data-integrity-and-constraints)

[**Phase 4 — Entity Type Schema Diagram**
[1](#phase-4-entity-type-schema-diagram)](#phase-4-entity-type-schema-diagram)

[**Phase 5 — Conceptual Modelling — Entity-Relationship Diagram**
[1](#phase-5-conceptual-modelling-entity-relationship-diagram)](#phase-5-conceptual-modelling-entity-relationship-diagram)

[**5.1 Relationships, Cardinality & Participation Constraints**
[1](#relationships-cardinality-participation-constraints)](#relationships-cardinality-participation-constraints)

[**Phase 6 — Logical Relational Schema Diagram**
[1](#phase-6-logical-relational-schema-diagram)](#phase-6-logical-relational-schema-diagram)

[**6.1 Referential Integrity Summary**
[1](#referential-integrity-summary)](#referential-integrity-summary)

[**Phase 7 — Physical Database Implementation & Population**
[1](#phase-7-physical-database-implementation-population)](#phase-7-physical-database-implementation-population)

[**7.1 Data Definition Language (DDL) — schema.sql**
[1](#data-definition-language-ddl-schema.sql)](#data-definition-language-ddl-schema.sql)

[**7.2 Data Manipulation Language (DML) — seed.sql**
[1](#data-manipulation-language-dml-seed.sql)](#data-manipulation-language-dml-seed.sql)

[**Phase 8 — Web Interface Integration**
[1](#phase-8-web-interface-integration)](#phase-8-web-interface-integration)

[**8.1 Architecture Overview**
[1](#architecture-overview)](#architecture-overview)

[**8.2 Authentication & Sessions**
[1](#authentication-sessions)](#authentication-sessions)

[**8.3 Frontend Application Shell**
[1](#frontend-application-shell)](#frontend-application-shell)

[**8.4 CRUD & Business-Process Mapping**
[1](#crud-business-process-mapping)](#crud-business-process-mapping)

[**8.5 Delete-Safety Behaviour**
[1](#delete-safety-behaviour)](#delete-safety-behaviour)

[**8.6 Checkout / Point-of-Sale Flow**
[1](#checkout-point-of-sale-flow)](#checkout-point-of-sale-flow)

[**8.7 Live Dashboard Screenshots**
[1](#live-dashboard-screenshots)](#live-dashboard-screenshots)

[**8.8 Security & Improvement Notes**
[1](#security-improvement-notes)](#security-improvement-notes)

[**Conclusion** [1](#conclusion)](#conclusion)

[**Appendix A — Full Backend Source (api.php)**
[1](#_Toc235376278)](#_Toc235376278)

[**Appendix B — Full Frontend Source (index.html)**
[1](#_Toc235376279)](#_Toc235376279)

# **Phase 1 — Business Requirements Specification**

## **1.1 Company Profile**

MaxMart Supermarket is a mid-sized, independently owned grocery retailer
trading from a single storefront in Accra, Ghana. The store stocks a mix
of imported packaged goods — dairy, bakery, and beverage lines —
alongside locally sourced staples such as rice, banku mix, and kenkey,
serving households in the surrounding neighbourhood. MaxMart currently
employs a small team split across two roles: a store Manager who
oversees pricing, stock, and supplier relationships, and Cashiers who
process sales at the till.

As the business has grown beyond a handful of shelves, its owner has
found that tracking stock levels, supplier relationships, and daily
sales on paper ledgers and spreadsheets is no longer reliable — prices
drift out of sync between the shelf and the till, low stock is not
caught before a line runs out, and there is no single source of truth a
Manager can query at the end of a trading day. MaxMart has therefore
commissioned a relational database, backed by a simple internal web
dashboard, so staff can view and update the product catalogue directly
instead of editing spreadsheets by hand.

## **1.2 Narrative of System Requirements**

Consultation with the store owner identified the following operational
needs the system must support:

- Staff accounts — every employee who can access the system must be
  uniquely identifiable, assigned exactly one role (Manager or Cashier),
  and authenticated with a securely hashed password.

- Category management — every product sold must be grouped into a
  functional category (e.g. Dairy, Bakery, Beverages) so the Manager can
  review performance and shelf allocation by department.

- Supplier tracking — the business deals with multiple external
  suppliers and needs a record of each supplier's contact details,
  independent of which products they currently provide.

- Product catalogue — each product needs a unique SKU/barcode, a display
  name, a category, an optional supplier, a unit price, an on-hand stock
  count, and a reorder threshold.

- Sales recording — each completed sale must be captured as an order
  tied to the staff member who processed it, with individual line items
  recording the product sold, the quantity, and the price charged at the
  time of sale, so historical revenue figures stay accurate even if the
  catalogue price changes afterwards.

- Operational reporting — the Manager should be able to see, at a
  glance, how many distinct products are carried and the total number of
  units on hand, to plan reordering and shelf space.

## **1.3 Key Business Rules**

The following business rules govern the integrity of MaxMart's data and
are enforced directly at the database layer wherever possible, rather
than relying on the application code alone:

- Every product must belong to exactly one category; a category cannot
  be removed while products still reference it.

- A supplier may be removed from the system without deleting the
  products they supply — those products simply become unassigned.

- No product may carry a negative price or a negative stock quantity,
  and no order line item may carry a negative quantity or unit price.

- SKUs, category names, and staff email addresses must each be unique
  across the system.

- A staff record cannot be deleted while it still has orders attached,
  preserving the audit trail of who processed each historical sale.

- Deleting an order deletes its line items with it, since a line item
  has no independent meaning once its parent order is gone.

- A product cannot be deleted while it appears in historical order line
  items, protecting past sales records from silently losing their
  meaning.

- Every staff member is assigned one of exactly two roles — Manager or
  Cashier — enforced by a domain check constraint.

# **Phase 2 — Entity Types & Attribute Identification**

Based on the requirements narrative above, six entity types were
identified. At this stage attributes are listed by name only, ahead of
the technical typing and constraint definition carried out in Phase 3.

| **Entity Type** | **Attributes Identified**                                                                     |
|-----------------|-----------------------------------------------------------------------------------------------|
| STAFF           | staff_id, first_name, last_name, email, password_hash, role, created_at                       |
| CATEGORIES      | category_id, category_name, description                                                       |
| SUPPLIERS       | supplier_id, company_name, contact_name, phone                                                |
| PRODUCTS        | product_id, sku, product_name, category_id, supplier_id, price, stock_quantity, reorder_level |
| ORDERS          | order_id, staff_id, order_date, total_amount                                                  |
| ORDER_ITEMS     | order_item_id, order_id, product_id, quantity, unit_price                                     |

ORDER_ITEMS is an associative (bridge) entity: it resolves the
many-to-many relationship between a single order and the many products
it can contain, while also carrying its own attributes (quantity,
unit_price) that belong to the association itself rather than to either
parent entity.

# **Phase 3 — Data Integrity and Constraints**

Every attribute identified in Phase 2 is given an explicit data type and
constraint set below, exactly as implemented in the physical schema (see
Phase 7).

### **STAFF**

| **Attribute** | **Data Type** | **PK/FK** | **Null?** | **Additional Constraints**            |
|---------------|---------------|-----------|-----------|---------------------------------------|
| staff_id      | INT           | **PK**    | NOT NULL  | AUTO_INCREMENT                        |
| first_name    | VARCHAR(50)   | —         | NOT NULL  | —                                     |
| last_name     | VARCHAR(50)   | —         | NOT NULL  | —                                     |
| email         | VARCHAR(100)  | —         | NOT NULL  | UNIQUE                                |
| password_hash | VARCHAR(255)  | —         | NOT NULL  | Stores a bcrypt hash, never plaintext |
| role          | VARCHAR(20)   | —         | NOT NULL  | CHECK (role IN ('Manager','Cashier')) |
| created_at    | TIMESTAMP     | —         | NOT NULL  | DEFAULT CURRENT_TIMESTAMP             |

### **CATEGORIES**

| **Attribute** | **Data Type** | **PK/FK** | **Null?** | **Additional Constraints** |
|---------------|---------------|-----------|-----------|----------------------------|
| category_id   | INT           | **PK**    | NOT NULL  | AUTO_INCREMENT             |
| category_name | VARCHAR(50)   | —         | NOT NULL  | UNIQUE                     |
| description   | TEXT          | —         | NULL      | —                          |

### **SUPPLIERS**

| **Attribute** | **Data Type** | **PK/FK** | **Null?** | **Additional Constraints** |
|---------------|---------------|-----------|-----------|----------------------------|
| supplier_id   | INT           | **PK**    | NOT NULL  | AUTO_INCREMENT             |
| company_name  | VARCHAR(100)  | —         | NOT NULL  | —                          |
| contact_name  | VARCHAR(50)   | —         | NULL      | —                          |
| phone         | VARCHAR(20)   | —         | NOT NULL  | —                          |

### **PRODUCTS**

| **Attribute**  | **Data Type** | **PK/FK** | **Null?** | **Additional Constraints**                            |
|----------------|---------------|-----------|-----------|-------------------------------------------------------|
| product_id     | INT           | **PK**    | NOT NULL  | AUTO_INCREMENT                                        |
| sku            | VARCHAR(50)   | —         | NOT NULL  | UNIQUE                                                |
| product_name   | VARCHAR(100)  | —         | NOT NULL  | —                                                     |
| category_id    | INT           | *FK*      | NOT NULL  | REFERENCES categories(category_id) ON DELETE RESTRICT |
| supplier_id    | INT           | *FK*      | NULL      | REFERENCES suppliers(supplier_id) ON DELETE SET NULL  |
| price          | DECIMAL(10,2) | —         | NOT NULL  | CHECK (price \>= 0.00)                                |
| stock_quantity | INT           | —         | NOT NULL  | DEFAULT 0, CHECK (stock_quantity \>= 0)               |
| reorder_level  | INT           | —         | NOT NULL  | DEFAULT 10                                            |

### **ORDERS**

| **Attribute** | **Data Type** | **PK/FK** | **Null?** | **Additional Constraints**                    |
|---------------|---------------|-----------|-----------|-----------------------------------------------|
| order_id      | INT           | **PK**    | NOT NULL  | AUTO_INCREMENT                                |
| staff_id      | INT           | *FK*      | NOT NULL  | REFERENCES staff(staff_id) ON DELETE RESTRICT |
| order_date    | DATETIME      | —         | NOT NULL  | DEFAULT CURRENT_TIMESTAMP                     |
| total_amount  | DECIMAL(10,2) | —         | NOT NULL  | DEFAULT 0.00, CHECK (total_amount \>= 0.00)   |

### **ORDER_ITEMS**

| **Attribute** | **Data Type** | **PK/FK** | **Null?** | **Additional Constraints**                         |
|---------------|---------------|-----------|-----------|----------------------------------------------------|
| order_item_id | INT           | **PK**    | NOT NULL  | AUTO_INCREMENT                                     |
| order_id      | INT           | *FK*      | NOT NULL  | REFERENCES orders(order_id) ON DELETE CASCADE      |
| product_id    | INT           | *FK*      | NOT NULL  | REFERENCES products(product_id) ON DELETE RESTRICT |
| quantity      | INT           | —         | NOT NULL  | CHECK (quantity \> 0)                              |
| unit_price    | DECIMAL(10,2) | —         | NOT NULL  | CHECK (unit_price \>= 0.00)                        |

# **Phase 4 — Entity Type Schema Diagram**

The diagram below shows the six entity types identified in Phase 2 as
isolated structures, before any relationships are drawn between them —
each box lists an entity's own attributes only.

<img src="readme_output/media/image1.png"
style="width:4.89583in;height:5.54167in" />

*Figure 1 — Isolated entity types with their attributes, prior to
relationship modelling.*

# **Phase 5 — Conceptual Modelling — Entity-Relationship Diagram**

The Entity-Relationship diagram below (Chen notation) connects the six
entities via five relationships, with cardinality (1 or M) marked on
each connecting line.

<img src="readme_output/media/image2.png"
style="width:4.16667in;height:5.13542in" />

*Figure 2 — Conceptual ER diagram showing entities (rectangles),
relationships (diamonds), and cardinalities.*

## **5.1 Relationships, Cardinality & Participation Constraints**

| **Relationship** | **Entities Involved**  | **Cardinality**  | **Left Participation** | **Right Participation** |
|------------------|------------------------|------------------|------------------------|-------------------------|
| Places           | STAFF → ORDERS         | 1 : M            | Partial                | Total                   |
| Contains         | ORDERS → ORDER_ITEMS   | 1 : M            | Partial                | Total                   |
| Appears In       | PRODUCTS → ORDER_ITEMS | 1 : M            | Partial                | Total                   |
| Classifies       | CATEGORIES → PRODUCTS  | 1 : M            | Partial                | Total                   |
| Supplies         | SUPPLIERS → PRODUCTS   | 1 : M (optional) | Partial                | Partial                 |

"Total" participation means every instance of that entity must take part
in the relationship (e.g. every order must have been placed by a staff
member — staff_id is NOT NULL). "Partial" participation means an
instance may legitimately exist without taking part (e.g. a staff member
may not yet have processed any order, and — because supplier_id is
nullable — a product may exist with no linked supplier).

# **Phase 6 — Logical Relational Schema Diagram**

The conceptual ER model was translated into a logical relational schema.
Each entity became a table; primary keys are underlined and foreign keys
are italicised. Arrows use crow's-foot notation, running from the many
(crow's-foot) side to the one (single tick) side, to show referential
integrity between foreign keys and the primary keys they reference.

<img src="readme_output/media/image3.png"
style="width:6.45833in;height:6.01042in" />

*Figure 3 — Logical relational schema with primary/foreign keys and
referential integrity arrows.*

## **6.1 Referential Integrity Summary**

| **Foreign Key** | **Child Table** | **Parent Table** | **On Delete** | **Rationale**                                                         |
|-----------------|-----------------|------------------|---------------|-----------------------------------------------------------------------|
| category_id     | products        | categories       | RESTRICT      | Prevents orphaned products if a category still in use is removed.     |
| supplier_id     | products        | suppliers        | SET NULL      | Lets a supplier relationship end without deleting the product.        |
| staff_id        | orders          | staff            | RESTRICT      | Preserves the audit trail of who processed historical sales.          |
| order_id        | order_items     | orders           | CASCADE       | A line item has no meaning once its parent order is removed.          |
| product_id      | order_items     | products         | RESTRICT      | Protects historical sales records from referencing a deleted product. |

# **Phase 7 — Physical Database Implementation & Population**

The logical and relational design above was implemented on MySQL (InnoDB
storage engine). InnoDB was chosen specifically because it enforces
foreign-key constraints and supports transactions — both required by the
business rules in Phase 1 — unlike the older MyISAM engine, which
silently ignores foreign keys.

## **7.1 Data Definition Language (DDL) — schema.sql**

The complete table-creation script, including every constraint
documented in Phase 3:

```sql
CREATE DATABASE IF NOT EXISTS supermarket_db;
USE supermarket_db;
-- 1. Staff Table
CREATE TABLE staff (
staff_id INT AUTO_INCREMENT PRIMARY KEY,
first_name VARCHAR(50) NOT NULL,
last_name VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL,
role VARCHAR(20) NOT NULL CHECK (role IN ('Manager', 'Cashier')),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- 2. Categories Table
CREATE TABLE categories (
category_id INT AUTO_INCREMENT PRIMARY KEY,
category_name VARCHAR(50) NOT NULL UNIQUE,
description TEXT NULL
) ENGINE=InnoDB;
-- 3. Suppliers Table
CREATE TABLE suppliers (
supplier_id INT AUTO_INCREMENT PRIMARY KEY,
company_name VARCHAR(100) NOT NULL,
contact_name VARCHAR(50) NULL,
phone VARCHAR(20) NOT NULL
) ENGINE=InnoDB;
-- 4. Products Table
CREATE TABLE products (
product_id INT AUTO_INCREMENT PRIMARY KEY,
sku VARCHAR(50) NOT NULL UNIQUE,
product_name VARCHAR(100) NOT NULL,
category_id INT NOT NULL,
supplier_id INT NULL,
price DECIMAL(10,2) NOT NULL CHECK (price >= 0.00),
stock_quantity INT NOT NULL DEFAULT 0 CHECK (stock_quantity >= 0),
reorder_level INT NOT NULL DEFAULT 10,
FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT,
FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL
) ENGINE=InnoDB;
-- 5. Orders Table
CREATE TABLE orders (
order_id INT AUTO_INCREMENT PRIMARY KEY,
staff_id INT NOT NULL,
order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 CHECK (total_amount >= 0.00),
FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE RESTRICT
) ENGINE=InnoDB;
-- 6. Order_Items Table (Associative Entity)
CREATE TABLE order_items (
order_item_id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL,
product_id INT NOT NULL,
quantity INT NOT NULL CHECK (quantity > 0),
unit_price DECIMAL(10,2) NOT NULL CHECK (unit_price >= 0.00),
FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

## **7.2 Data Manipulation Language (DML) — seed.sql**

A representative set of mock operational data was inserted to exercise
every table and relationship, including two staff members, three
categories, two suppliers, four products, one sample order, and its two
order line items. Both staff accounts share the password password123,
stored as a genuine bcrypt hash (verified with PHP's password_verify(),
not a placeholder string) so the login screen in Phase 8 works
immediately against this seed data:

```sql
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
```

# **Phase 8 — Web Interface Integration**

A working browser dashboard was built and connected live to the
database, so MaxMart staff can sign in, manage the full catalogue, and
ring up sales without touching SQL directly. Following an initial review
of this section (see Section 8.8 of the original submission), the
interface was substantially extended: it now covers all six entities
from the relational schema, not just products, and staff must
authenticate before touching any data.

## **8.1 Architecture Overview**

- Frontend — a single static page (index.html): a login screen plus a
  five-tab dashboard (Inventory, Categories, Suppliers, Checkout, Sales
  History), built with plain HTML, CSS, and JavaScript (fetch API) — no
  framework or build step.

- Backend — a PHP application layer (api.php) exposing an 18-action JSON
  API, acting as the bridge between the browser and the database.

- Database driver — PHP Data Objects (PDO) with the MySQL driver, using
  prepared statements throughout to bind user input safely and prevent
  SQL injection.

- Session layer — native PHP sessions authenticate every request after
  login; cookies are scoped HttpOnly and SameSite=Lax.

## **8.2 Authentication & Sessions**

Signing in posts an email and password to the login action. The backend
looks the staff member up by email and checks the password with PHP's
password_verify() against the bcrypt hash stored in the staff table —
the plaintext password is never compared directly, and the hash never
leaves the server. On success, the session ID is regenerated
(session_regenerate_id()) before the staff member's id, name, and role
are written into the session, which guards against session-fixation
attacks.

**Excerpt — verifying credentials (api.php)**

```php
$stmt = $conn->prepare("SELECT * FROM staff WHERE email = :email");
$stmt->execute([":email" => $email]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$staff || !password_verify($pw, $staff['password_hash'])) {
http_response_code(401);
echo json_encode(["error" => "Incorrect email or password."]);
} else {
session_regenerate_id(true);
$_SESSION['staff_id'] = $staff['staff_id'];
// ...store first_name, last_name, role in the session
}
```

Every action except login, logout, and me (a status check used on page
load) calls a small requireAuth() guard first, which stops the request
with a 401 response if no staff_id is present in the session:

**Excerpt — the auth guard applied to every protected action (api.php)**

```php
function requireAuth() {
if (empty($_SESSION['staff_id'])) {
http_response_code(401);
echo json_encode(["error" => "You must be signed in to do this."]);
exit();
}
}
```

## **8.3 Frontend Application Shell**

The dashboard is now structured as a persistent sidebar (with the
MaxMart logo, navigation between the five tabs, and the signed-in staff
member's name/role) alongside a content area that swaps between panels
entirely client-side — no page reloads. A single api() helper
centralises every network call: it attaches credentials so the session
cookie is sent, parses the JSON response, and throws a JavaScript error
whenever the backend returns an error field, so every screen can handle
failures with one try/catch pattern instead of repeating fetch
boilerplate.

**Excerpt — the shared request helper (index.html)**

```javascript
async function api(action, { method = 'GET', body } = {}) {
const url = `${API_URL}?action=${action}`;
const opts = { method, credentials: 'include' };
if (body !== undefined) {
opts.headers = { 'Content-Type': 'application/json' };
opts.body = JSON.stringify(body);
}
const res = await fetch(url, opts);
const data = await res.json();
if (data && data.error) throw new Error(data.error);
return data;
}
```

Failed requests surface as dismissible toast notifications instead of
blocking alert() dialogs, and the Inventory tab now flags any product
whose stock_quantity has fallen to or below its reorder_level with a
"Low" badge — putting the reorder_level column (present in the schema
since Phase 3, but unused by the original interface) to actual use.

## **8.4 CRUD & Business-Process Mapping**

Products, categories, and suppliers each get the same four-action shape
— list, add, update, delete — so the API surface stays predictable
across resources:

| **Resource** | **Read**       | **Create**   | **Update**      | **Delete**      |
|--------------|----------------|--------------|-----------------|-----------------|
| Products     | get_products   | add_product  | update_product  | delete_product  |
| Categories   | get_categories | add_category | update_category | delete_category |
| Suppliers    | get_suppliers  | add_supplier | update_supplier | delete_supplier |

Authentication and sales sit outside that CRUD shape, since they
represent actions and processes rather than plain records:

| **Action**      | **HTTP** | **Purpose**                                                                                            |
|-----------------|----------|--------------------------------------------------------------------------------------------------------|
| login           | POST     | Verifies email + password with password_verify(); starts the session.                                  |
| logout          | POST     | Destroys the session.                                                                                  |
| me              | GET      | Reports whether a session is active — checked once on page load so a refresh doesn't force a re-login. |
| get_orders      | GET      | Lists past sales with the processing staff member's name and a line-item count.                        |
| get_order_items | GET      | Line items for one order, used by the Sales History "View" detail modal.                               |
| create_order    | POST     | Transactional checkout — see Section 8.6.                                                              |

## **8.5 Delete-Safety Behaviour**

Deletes are wrapped in try/catch so the RESTRICT and SET NULL
foreign-key behaviour defined in Phase 3 surfaces as a readable message
rather than a raw SQL error — for example, deleting a category that
still has products assigned to it returns "Cannot delete this category —
products are still assigned to it." instead of a MySQL
constraint-violation string.

## **8.6 Checkout / Point-of-Sale Flow**

The Checkout tab is the piece that was entirely missing from the
original submission: it lets a signed-in staff member build a cart from
the live product list and complete a sale, which both records the
transaction and reduces stock — closing the loop between the
ORDERS/ORDER_ITEMS tables (populated in Phase 7) and the PRODUCTS table.

The cart itself is ordinary client-side state; the integrity work
happens once it is submitted. create_order wraps the whole operation in
a single database transaction: quantities for the same product are first
aggregated (so a product listed twice in one submission can't slip past
validation as two smaller, individually-valid lines), then each product
row is locked with SELECT ... FOR UPDATE while its stock is checked, and
only once every line has been validated are the order, its line items,
and the stock decrements written — any failure rolls the entire
transaction back, so a sale can never be half-applied.

**Excerpt — validating and locking stock before committing a sale
(api.php)**

```php
$conn->beginTransaction();
foreach ($aggregated as $productId => $qty) {
// Lock the row so two simultaneous sales can't oversell the same stock.
$stmt = $conn->prepare("SELECT ... FROM products WHERE product_id = :id FOR UPDATE");
$stmt->execute([":id" => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if ((int)$product['stock_quantity'] < $qty) {
throw new Exception("Only {$product['stock_quantity']} unit(s) ... left in stock.");
}
// ... accumulate line total
}
// Only now: INSERT the order, INSERT each order_item, and decrement stock.
$conn->commit();
```

The order's staff_id is taken from the session rather than from anything
the browser sends, so a sale is always attributed to whoever is actually
signed in — directly satisfying the Phase 1 requirement that every sale
be traceable to the staff member who processed it.

## **8.7 Live Dashboard Screenshots**

The screenshots below are frames pulled directly from a screen recording
of the system running against the real MySQL database (not a mock-up) —
they show the Checkout flow, a completed sale immediately reflected in
Sales History, and the new Suppliers management tab.

<img src="readme_output/media/image4.png"
style="width:6.25in;height:3.16667in" />

*Figure 4 — Checkout tab: building a cart from live stock before
completing a sale.*

<img src="readme_output/media/image5.png"
style="width:6.25in;height:3.16667in" />

*Figure 5 — Sales History showing Order \#2, attributed to the signed-in
staff member (Jane Smith), immediately after checkout.*

<img src="readme_output/media/image6.png"
style="width:6.25in;height:3.16667in" />

*Figure 6 — Suppliers tab, fully CRUD-managed rather than hardcoded.*

## **8.8 Security & Improvement Notes**

Two gaps flagged in the original submission — no login, and no web-based
order entry — are now closed. The remaining production-readiness
improvements identified are:

- The database connection still uses the root MySQL account with no
  password; a dedicated least-privilege application user should be
  created before any real deployment.

- Both Manager and Cashier roles are recorded and checked at login, but
  the API does not yet differentiate permissions between them — a
  signed-in Cashier can currently delete products or suppliers, actions
  a real deployment would likely restrict to Managers only.

- Login has no rate-limiting or lockout after repeated failed attempts,
  so brute-force protection is a good next addition.

- The session cookie's secure flag is currently false to work over plain
  HTTP on localhost; it should be switched to true once the site is
  served over HTTPS.

# **Conclusion**

This assignment took MaxMart Supermarket's stock-and-till problem
through all eight phases of the database development lifecycle: a
written requirements narrative, entity and attribute identification,
fully typed integrity constraints, an isolated entity-type diagram, a
conceptual ER model with cardinalities and participation constraints, a
logical relational schema with referential integrity arrows, a physical
MySQL implementation populated with representative data, and a working
PHP/JavaScript dashboard.

The web layer now reaches every table in the schema rather than just the
product catalogue: staff sign in against the staff table before touching
any data, categories and suppliers are fully manageable rather than
hardcoded, and completing a sale in the Checkout tab is a real
transaction against ORDERS and ORDER_ITEMS that also decrements stock —
the business process the whole schema was designed around in Phase 1,
not just its supporting catalogue data. What remains, documented
honestly in Section 8.8 rather than glossed over, is permission
differentiation between the Manager and Cashier roles the schema already
distinguishes, and the usual pre-production hardening (a least-privilege
database user, login rate-limiting, and HTTPS).
