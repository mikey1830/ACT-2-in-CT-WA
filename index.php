<?php
require "db.php";

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";

// CREATE: Add product
if (isset($_POST["add_product"])) {
    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $brand = trim($_POST["brand"]);
    $description = trim($_POST["description"]);
    $price = (float)$_POST["price"];
    $stock = (int)$_POST["stock"];

    if ($name !== "" && $category !== "" && $price >= 0 && $stock >= 0) {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, category, brand, description, price, stock)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $category, $brand, $description, $price, $stock]);
        header("Location: index.php?status=added");
        exit;
    } else {
        $message = "Please complete the required fields using valid values.";
    }
}

// UPDATE: Edit product
if (isset($_POST["update_product"])) {
    $id = (int)$_POST["id"];
    $name = trim($_POST["name"]);
    $category = trim($_POST["category"]);
    $brand = trim($_POST["brand"]);
    $description = trim($_POST["description"]);
    $price = (float)$_POST["price"];
    $stock = (int)$_POST["stock"];

    if ($name !== "" && $category !== "" && $price >= 0 && $stock >= 0) {
        $stmt = $pdo->prepare(
            "UPDATE products
             SET name=?, category=?, brand=?, description=?, price=?, stock=?
             WHERE id=?"
        );
        $stmt->execute([$name, $category, $brand, $description, $price, $stock, $id]);
        header("Location: index.php?status=updated");
        exit;
    } else {
        $message = "Please complete the required fields using valid values.";
    }
}

// DELETE: Remove product
if (isset($_POST["delete_product"])) {
    $id = (int)$_POST["id"];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([$id]);
    header("Location: index.php?status=deleted");
    exit;
}

// READ: Display products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$editProduct = null;
if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$id]);
    $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_GET["status"])) {
    $statuses = [
        "added" => "Product added successfully!",
        "updated" => "Product updated successfully!",
        "deleted" => "Product deleted successfully!"
    ];
    $message = $statuses[$_GET["status"]] ?? "";
}

$totalProducts = count($products);
$totalStock = array_sum(array_column($products, "stock"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Shop | Product Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">🏍️ MOTOR SHOP</div>
    <div class="tagline">Motorcycle Parts & Accessories</div>
</header>

<main class="container">
    <section class="welcome">
        <p class="eyebrow">PRODUCT MANAGEMENT SYSTEM</p>
        <h1>Motor Shop Inventory</h1>
        <p>Manage your motorcycle parts, prices, and stock in one place.</p>
    </section>

    <section class="stats">
        <div class="stat-card">
            <span>Total Products</span>
            <strong><?php echo $totalProducts; ?></strong>
        </div>
        <div class="stat-card">
            <span>Total Stock</span>
            <strong><?php echo $totalStock; ?></strong>
        </div>
    </section>

    <?php if ($message !== ""): ?>
        <div class="message"><?php echo h($message); ?></div>
    <?php endif; ?>

    <section class="panel">
        <h2><?php echo $editProduct ? "Update Product" : "Add New Product"; ?></h2>
        <form method="POST" action="index.php" class="product-form">
            <?php if ($editProduct): ?>
                <input type="hidden" name="id" value="<?php echo h($editProduct["id"]); ?>">
            <?php endif; ?>

            <label>Product Name *
                <input type="text" name="name" required placeholder="Example: Motorcycle Tire"
                    value="<?php echo h($editProduct["name"] ?? ""); ?>">
            </label>

            <label>Category *
                <select name="category" required>
                    <option value="">Select category</option>
                    <?php
                    $categories = ["Engine Parts", "Tires & Wheels", "Brakes", "Electrical", "Accessories", "Lubricants", "Other"];
                    foreach ($categories as $category):
                        $selected = (($editProduct["category"] ?? "") === $category) ? "selected" : "";
                    ?>
                        <option value="<?php echo h($category); ?>" <?php echo $selected; ?>>
                            <?php echo h($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Brand
                <input type="text" name="brand" placeholder="Example: Yamaha"
                    value="<?php echo h($editProduct["brand"] ?? ""); ?>">
            </label>

            <label>Price (₱) *
                <input type="number" name="price" min="0" step="0.01" required placeholder="0.00"
                    value="<?php echo h($editProduct["price"] ?? ""); ?>">
            </label>

            <label>Stock Quantity *
                <input type="number" name="stock" min="0" step="1" required placeholder="0"
                    value="<?php echo h($editProduct["stock"] ?? ""); ?>">
            </label>

            <label class="full">Description
                <textarea name="description" rows="3" placeholder="Product details..."><?php echo h($editProduct["description"] ?? ""); ?></textarea>
            </label>

            <div class="form-actions full">
                <?php if ($editProduct): ?>
                    <button type="submit" name="update_product" class="btn">Save Changes</button>
                    <a href="index.php" class="btn btn-light">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add_product" class="btn">Add Product</button>
                    <button type="reset" class="btn btn-light">Clear</button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-heading">
            <div>
                <h2>Our Products</h2>
                <p>View, update, or delete products in your shop.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo h($product["id"]); ?></td>
                            <td>
                                <strong><?php echo h($product["name"]); ?></strong>
                                <?php if ($product["description"]): ?>
                                    <small><?php echo h($product["description"]); ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo h($product["category"]); ?></td>
                            <td><?php echo h($product["brand"] ?: "—"); ?></td>
                            <td>₱<?php echo number_format((float)$product["price"], 2); ?></td>
                            <td><span class="stock"><?php echo h($product["stock"]); ?></span></td>
                            <td class="actions">
                                <a class="btn-small edit" href="index.php?edit=<?php echo h($product["id"]); ?>">Edit</a>
                                <form method="POST" action="index.php" onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="id" value="<?php echo h($product["id"]); ?>">
                                    <button type="submit" name="delete_product" class="btn-small delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty">No products yet. Add your first motor part above.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <footer>© <?php echo date("Y"); ?> Motor Shop Inventory System</footer>
</main>
</body>
</html>

compress
