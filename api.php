<?php
// =====================================================================
// MaxMart Operations Engine — Backend API
// PHP + PDO (MySQL). Action-based router: ?action=... combined with the
// HTTP method. All responses are JSON.
//
// IMPORTANT: this file uses PHP sessions for login, which rely on cookies.
// Serve it over http:// (e.g. http://localhost/maxmart/) — opening
// index.html directly as a file:// URL will break the session cookie.
// =====================================================================

// --- 0. SESSION (must start before any output) ---
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => false,   // set true once served over HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// --- 1. CORS & PRE-FLIGHT HEADERS ---
// Origin is echoed back (rather than "*") because credentialed requests
// (cookies) are not allowed with a wildcard origin.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- 2. DATABASE CONFIGURATION ---
$host     = "localhost";
$db_name  = "supermarket_db";
$username = "root";
$password = "";
$conn     = null;

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name . ";charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
    exit();
}

// --- 3. SMALL HELPERS ---

// Reads and decodes the JSON request body; always returns an array.
function input() {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// Blocks the request unless a staff member is signed in.
function requireAuth() {
    if (empty($_SESSION['staff_id'])) {
        http_response_code(401);
        echo json_encode(["error" => "You must be signed in to do this."]);
        exit();
    }
}

// The signed-in staff member's public info, or null.
function currentStaff() {
    if (empty($_SESSION['staff_id'])) return null;
    return [
        "staff_id"   => $_SESSION['staff_id'],
        "first_name" => $_SESSION['first_name'],
        "last_name"  => $_SESSION['last_name'],
        "role"       => $_SESSION['role'],
    ];
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Catch every route in an output buffer so an unrecognised action never
// silently returns an empty (unparsable) response to the frontend.
ob_start();

// ======================================================================
// AUTH
// ======================================================================

if ($method === 'POST' && $action === 'login') {
    $data = input();
    $email = trim($data['email'] ?? '');
    $pw = (string)($data['password'] ?? '');

    if ($email === '' || $pw === '') {
        echo json_encode(["error" => "Enter both an email and a password."]);
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM staff WHERE email = :email");
            $stmt->execute([":email" => $email]);
            $staff = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$staff || !password_verify($pw, $staff['password_hash'])) {
                http_response_code(401);
                echo json_encode(["error" => "Incorrect email or password."]);
            } else {
                session_regenerate_id(true);
                $_SESSION['staff_id']   = $staff['staff_id'];
                $_SESSION['first_name'] = $staff['first_name'];
                $_SESSION['last_name']  = $staff['last_name'];
                $_SESSION['role']       = $staff['role'];
                echo json_encode(["message" => "Signed in.", "staff" => currentStaff()]);
            }
        } catch (PDOException $e) {
            echo json_encode(["error" => "Login failed: " . $e->getMessage()]);
        }
    }
}

if ($method === 'POST' && $action === 'logout') {
    $_SESSION = [];
    session_destroy();
    echo json_encode(["message" => "Signed out."]);
}

if ($method === 'GET' && $action === 'me') {
    $staff = currentStaff();
    echo json_encode(["authenticated" => $staff !== null, "staff" => $staff]);
}

// ======================================================================
// PRODUCTS
// ======================================================================

if ($method === 'GET' && $action === 'get_products') {
    requireAuth();
    try {
        $query = "SELECT p.product_id, p.sku, p.product_name, p.category_id, c.category_name,
                         p.supplier_id, s.company_name AS supplier_name,
                         p.price, p.stock_quantity, p.reorder_level
                  FROM products p
                  JOIN categories c ON p.category_id = c.category_id
                  LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                  ORDER BY p.product_id ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

if ($method === 'POST' && $action === 'add_product') {
    requireAuth();
    $data = input();
    if (!empty($data['sku']) && !empty($data['product_name']) && !empty($data['category_id']) && isset($data['price']) && isset($data['stock_quantity'])) {
        try {
            $query = "INSERT INTO products (sku, product_name, category_id, supplier_id, price, stock_quantity, reorder_level)
                      VALUES (:sku, :product_name, :category_id, :supplier_id, :price, :stock_quantity, :reorder_level)";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ":sku"            => $data['sku'],
                ":product_name"   => $data['product_name'],
                ":category_id"    => $data['category_id'],
                ":supplier_id"    => !empty($data['supplier_id']) ? $data['supplier_id'] : null,
                ":price"          => $data['price'],
                ":stock_quantity" => $data['stock_quantity'],
                ":reorder_level"  => (isset($data['reorder_level']) && $data['reorder_level'] !== '') ? $data['reorder_level'] : 10,
            ]);
            echo json_encode(["message" => "Product added to the catalogue."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Could not add product: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Please fill in all required fields."]);
    }
}

if ($method === 'POST' && $action === 'update_product') {
    requireAuth();
    $data = input();
    if (!empty($data['product_id']) && !empty($data['sku']) && !empty($data['product_name']) && !empty($data['category_id']) && isset($data['price']) && isset($data['stock_quantity'])) {
        try {
            $query = "UPDATE products
                      SET sku = :sku, product_name = :product_name, category_id = :category_id,
                          supplier_id = :supplier_id, price = :price, stock_quantity = :stock_quantity,
                          reorder_level = :reorder_level
                      WHERE product_id = :product_id";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                ":sku"            => $data['sku'],
                ":product_name"   => $data['product_name'],
                ":category_id"    => $data['category_id'],
                ":supplier_id"    => !empty($data['supplier_id']) ? $data['supplier_id'] : null,
                ":price"          => $data['price'],
                ":stock_quantity" => $data['stock_quantity'],
                ":reorder_level"  => (isset($data['reorder_level']) && $data['reorder_level'] !== '') ? $data['reorder_level'] : 10,
                ":product_id"     => $data['product_id'],
            ]);
            echo json_encode(["message" => "Product updated."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Update failed: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Missing parameters for update."]);
    }
}

if ($method === 'POST' && $action === 'delete_product') {
    requireAuth();
    $data = input();
    if (!empty($data['product_id'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :product_id");
            $stmt->execute([":product_id" => $data['product_id']]);
            echo json_encode(["message" => "Product removed from the catalogue."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Cannot delete this product — it is linked to past orders."]);
        }
    } else {
        echo json_encode(["error" => "Invalid product identifier."]);
    }
}

// ======================================================================
// CATEGORIES
// ======================================================================

if ($method === 'GET' && $action === 'get_categories') {
    requireAuth();
    try {
        $query = "SELECT c.category_id, c.category_name, c.description,
                         COUNT(p.product_id) AS product_count
                  FROM categories c
                  LEFT JOIN products p ON p.category_id = c.category_id
                  GROUP BY c.category_id, c.category_name, c.description
                  ORDER BY c.category_name ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

if ($method === 'POST' && $action === 'add_category') {
    requireAuth();
    $data = input();
    if (!empty($data['category_name'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO categories (category_name, description) VALUES (:name, :desc)");
            $stmt->execute([":name" => $data['category_name'], ":desc" => $data['description'] ?? null]);
            echo json_encode(["message" => "Category added."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Could not add category (name may already exist): " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Category name is required."]);
    }
}

if ($method === 'POST' && $action === 'update_category') {
    requireAuth();
    $data = input();
    if (!empty($data['category_id']) && !empty($data['category_name'])) {
        try {
            $stmt = $conn->prepare("UPDATE categories SET category_name = :name, description = :desc WHERE category_id = :id");
            $stmt->execute([":name" => $data['category_name'], ":desc" => $data['description'] ?? null, ":id" => $data['category_id']]);
            echo json_encode(["message" => "Category updated."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Update failed: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Missing parameters for update."]);
    }
}

if ($method === 'POST' && $action === 'delete_category') {
    requireAuth();
    $data = input();
    if (!empty($data['category_id'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = :id");
            $stmt->execute([":id" => $data['category_id']]);
            echo json_encode(["message" => "Category removed."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Cannot delete this category — products are still assigned to it."]);
        }
    } else {
        echo json_encode(["error" => "Invalid category identifier."]);
    }
}

// ======================================================================
// SUPPLIERS
// ======================================================================

if ($method === 'GET' && $action === 'get_suppliers') {
    requireAuth();
    try {
        $query = "SELECT s.supplier_id, s.company_name, s.contact_name, s.phone,
                         COUNT(p.product_id) AS product_count
                  FROM suppliers s
                  LEFT JOIN products p ON p.supplier_id = s.supplier_id
                  GROUP BY s.supplier_id, s.company_name, s.contact_name, s.phone
                  ORDER BY s.company_name ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

if ($method === 'POST' && $action === 'add_supplier') {
    requireAuth();
    $data = input();
    if (!empty($data['company_name']) && !empty($data['phone'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO suppliers (company_name, contact_name, phone) VALUES (:cn, :ct, :ph)");
            $stmt->execute([
                ":cn" => $data['company_name'],
                ":ct" => $data['contact_name'] ?? null,
                ":ph" => $data['phone'],
            ]);
            echo json_encode(["message" => "Supplier added."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Could not add supplier: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Company name and phone are required."]);
    }
}

if ($method === 'POST' && $action === 'update_supplier') {
    requireAuth();
    $data = input();
    if (!empty($data['supplier_id']) && !empty($data['company_name']) && !empty($data['phone'])) {
        try {
            $stmt = $conn->prepare("UPDATE suppliers SET company_name = :cn, contact_name = :ct, phone = :ph WHERE supplier_id = :id");
            $stmt->execute([
                ":cn" => $data['company_name'],
                ":ct" => $data['contact_name'] ?? null,
                ":ph" => $data['phone'],
                ":id" => $data['supplier_id'],
            ]);
            echo json_encode(["message" => "Supplier updated."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Update failed: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Missing parameters for update."]);
    }
}

if ($method === 'POST' && $action === 'delete_supplier') {
    requireAuth();
    $data = input();
    if (!empty($data['supplier_id'])) {
        try {
            $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = :id");
            $stmt->execute([":id" => $data['supplier_id']]);
            echo json_encode(["message" => "Supplier removed. Any linked products are now unassigned rather than deleted."]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Could not delete supplier: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Invalid supplier identifier."]);
    }
}

// ======================================================================
// ORDERS / CHECKOUT
// ======================================================================

if ($method === 'GET' && $action === 'get_orders') {
    requireAuth();
    try {
        $query = "SELECT o.order_id, o.order_date, o.total_amount,
                         CONCAT(st.first_name, ' ', st.last_name) AS staff_name,
                         (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) AS item_count
                  FROM orders o
                  JOIN staff st ON o.staff_id = st.staff_id
                  ORDER BY o.order_date DESC, o.order_id DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

if ($method === 'GET' && $action === 'get_order_items') {
    requireAuth();
    $orderId = $_GET['order_id'] ?? '';
    if (!empty($orderId)) {
        try {
            $query = "SELECT oi.order_item_id, oi.product_id, p.product_name, oi.quantity, oi.unit_price,
                             (oi.quantity * oi.unit_price) AS subtotal
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.product_id
                      WHERE oi.order_id = :id";
            $stmt = $conn->prepare($query);
            $stmt->execute([":id" => $orderId]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["error" => "Missing order identifier."]);
    }
}

// Completes a sale: validates stock, creates the order + its line items,
// and decrements stock — all inside a single transaction so a failure
// partway through leaves nothing half-applied.
if ($method === 'POST' && $action === 'create_order') {
    requireAuth();
    $data = input();
    $items = $data['items'] ?? [];

    if (empty($items) || !is_array($items)) {
        echo json_encode(["error" => "Add at least one product to the cart before completing the sale."]);
    } else {
        try {
            $conn->beginTransaction();

            // Aggregate quantities per product first, so the same product
            // listed twice in one submission can't slip past the stock
            // check by being validated as two smaller, individually-valid lines.
            $aggregated = [];
            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                $qty = (int)($item['quantity'] ?? 0);
                if (!$productId || $qty <= 0) {
                    throw new Exception("Every cart line needs a valid product and a quantity greater than zero.");
                }
                $aggregated[$productId] = ($aggregated[$productId] ?? 0) + $qty;
            }

            $lines = [];
            $total = 0;

            foreach ($aggregated as $productId => $qty) {
                // Lock the row so two simultaneous sales can't oversell the same stock.
                $stmt = $conn->prepare("SELECT product_id, product_name, price, stock_quantity FROM products WHERE product_id = :id FOR UPDATE");
                $stmt->execute([":id" => $productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception("One of the products in the cart no longer exists.");
                }
                if ((int)$product['stock_quantity'] < $qty) {
                    throw new Exception("Only {$product['stock_quantity']} unit(s) of \"{$product['product_name']}\" left in stock.");
                }

                $lines[] = [
                    "product_id" => $productId,
                    "quantity"   => $qty,
                    "unit_price" => $product['price'],
                ];
                $total += $qty * $product['price'];
            }

            $stmt = $conn->prepare("INSERT INTO orders (staff_id, total_amount) VALUES (:staff_id, :total)");
            $stmt->execute([":staff_id" => $_SESSION['staff_id'], ":total" => $total]);
            $orderId = $conn->lastInsertId();

            $itemStmt  = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (:order_id, :product_id, :qty, :unit_price)");
            $stockStmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - :qty WHERE product_id = :id");

            foreach ($lines as $line) {
                $itemStmt->execute([
                    ":order_id"   => $orderId,
                    ":product_id" => $line['product_id'],
                    ":qty"        => $line['quantity'],
                    ":unit_price" => $line['unit_price'],
                ]);
                $stockStmt->execute([":qty" => $line['quantity'], ":id" => $line['product_id']]);
            }

            $conn->commit();
            echo json_encode(["message" => "Sale completed.", "order_id" => $orderId, "total_amount" => $total]);
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

// --- 4. FALLBACK for an unrecognised method/action combination ---
$output = ob_get_clean();
if ($output === '') {
    http_response_code(404);
    echo json_encode(["error" => "Unknown action '$action' for method $method."]);
} else {
    echo $output;
}