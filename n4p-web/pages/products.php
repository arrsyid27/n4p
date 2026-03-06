<?php
/**
 * N4P (Not4Posers) POS System
 * Products Management Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser($conn);
$products = getAllProducts($conn);
$categories = getAllCategories($conn);

// Handle adding/editing product
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$product = null;

if ($action === 'edit' && $product_id) {
    $query = "SELECT * FROM products WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $sku = sanitize($_POST['sku']);
    $purchase_price = (float)$_POST['purchase_price'];
    $selling_price = (float)$_POST['selling_price'];
    $stock = (int)$_POST['stock'];
    $min_stock = (int)$_POST['min_stock'];
    $max_stock = (int)$_POST['max_stock'];
    
    if (isset($_POST['add_product'])) {
        // Add new product
        $query = "INSERT INTO products (category_id, name, description, sku, purchase_price, selling_price, stock, min_stock, max_stock) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issdddiii", $category_id, $name, $description, $sku, $purchase_price, $selling_price, $stock, $min_stock, $max_stock);
        
        if ($stmt->execute()) {
            setSuccessMessage('Product added successfully!');
            header('Location: ' . APP_URL . '/pages/products.php');
            exit;
        } else {
            setErrorMessage('Error adding product: ' . $stmt->error);
        }
    } elseif (isset($_POST['edit_product']) && $product_id) {
        // Edit product
        $query = "UPDATE products SET category_id = ?, name = ?, description = ?, sku = ?, purchase_price = ?, selling_price = ?, stock = ?, min_stock = ?, max_stock = ? 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issdddiiiti", $category_id, $name, $description, $sku, $purchase_price, $selling_price, $stock, $min_stock, $max_stock, $product_id);
        
        if ($stmt->execute()) {
            setSuccessMessage('Product updated successfully!');
            header('Location: ' . APP_URL . '/pages/products.php');
            exit;
        } else {
            setErrorMessage('Error updating product: ' . $stmt->error);
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && $_GET['delete']) {
    $delete_id = (int)$_GET['delete'];
    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        setSuccessMessage('Product deleted successfully!');
    } else {
        setErrorMessage('Error deleting product: ' . $stmt->error);
    }
    header('Location: ' . APP_URL . '/pages/products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($action === 'add' || $action === 'edit') ? ($action === 'add' ? 'Add' : 'Edit') . ' Product' : 'Products'; ?> | N4P POS System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <!-- no sidebar for products -->
    <header>
        <div class="navbar">
            <div class="navbar-brand">
                <a href="<?php echo APP_URL; ?>/" style="text-decoration: none; color: inherit;">N4P</a>
            </div>
            
            <div class="navbar-nav">
                <a href="<?php echo APP_URL; ?>/pages/dashboard.php">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/pages/pos.php">POS</a>
                <a href="<?php echo APP_URL; ?>/pages/products.php" class="active">Products</a>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php">Transactions</a>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo APP_URL; ?>/pages/users.php">Users</a>
                <?php endif; ?>
            </div>
            
            <div class="navbar-user">
                <div class="user-menu">
                    <button class="user-menu-toggle" onclick="toggleUserMenu()">👤</button>
                    <div class="user-dropdown" id="userDropdown">
                        <button onclick="logout()" style="color: #ef4444;">Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- sidebar removed -->

    <!-- Main Content -->
    <main class="main-container no-sidebar">
        <div class="container">
            <?php 
            $success = getSuccessMessage();
            if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
            
            $error = getErrorMessage();
            if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
            ?>

            <?php if ($action === 'list'): ?>
                <!-- Products List -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h1 class="page-title">Products Management</h1>
                    <a href="<?php echo APP_URL; ?>/pages/products.php?action=add" class="btn btn-primary">
                        ➕ Add Product
                    </a>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>SKU</th>
                                    <th>Purchase Price</th>
                                    <th>Selling Price</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                                        <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($p['sku']); ?></td>
                                        <td><?php echo formatCurrency($p['purchase_price']); ?></td>
                                        <td><?php echo formatCurrency($p['selling_price']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $p['stock'] <= $p['min_stock'] ? 'badge-danger' : 'badge-success'; ?>">
                                                <?php echo $p['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?php echo APP_URL; ?>/pages/products.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-primary btn-small">Edit</a>
                                            <a href="<?php echo APP_URL; ?>/pages/products.php?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete? This action cannot be undone.')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: #6b7280;">No products found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php else: ?>
                <!-- Add/Edit Product Form -->
                <h1 class="page-title"><?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?></h1>

                <div class="card">
                    <form method="POST" action="">
                        <div class="card-body">
                            <div class="form-row cols-2">
                                <div class="form-group">
                                    <label for="category_id">Category *</label>
                                    <select id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo ($product && $product['category_id'] === $cat['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="name">Product Name *</label>
                                    <input type="text" id="name" name="name" required value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>">
                                </div>
                            </div>

                            <div class="form-group full">
                                <label for="description">Description</label>
                                <textarea id="description" name="description"><?php echo $product ? htmlspecialchars($product['description']) : ''; ?></textarea>
                            </div>

                            <div class="form-row cols-3">
                                <div class="form-group">
                                    <label for="sku">SKU *</label>
                                    <input type="text" id="sku" name="sku" required value="<?php echo $product ? htmlspecialchars($product['sku']) : generateSKU(); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="purchase_price">Purchase Price *</label>
                                    <input type="number" id="purchase_price" name="purchase_price" step="0.01" required value="<?php echo $product ? $product['purchase_price'] : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="selling_price">Selling Price *</label>
                                    <input type="number" id="selling_price" name="selling_price" step="0.01" required value="<?php echo $product ? $product['selling_price'] : ''; ?>">
                                </div>
                            </div>

                            <div class="form-row cols-3">
                                <div class="form-group">
                                    <label for="stock">Stock *</label>
                                    <input type="number" id="stock" name="stock" required value="<?php echo $product ? $product['stock'] : '0'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="min_stock">Minimum Stock</label>
                                    <input type="number" id="min_stock" name="min_stock" value="<?php echo $product ? $product['min_stock'] : '10'; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="max_stock">Maximum Stock</label>
                                    <input type="number" id="max_stock" name="max_stock" value="<?php echo $product ? $product['max_stock'] : '100'; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <a href="<?php echo APP_URL; ?>/pages/products.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" name="<?php echo $action === 'add' ? 'add_product' : 'edit_product'; ?>" class="btn btn-success">
                                <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                            </button>
                        </div>
                    </form>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleUserMenu() {
            document.getElementById('userDropdown').classList.toggle('active');
        }

        function logout() {
            if (confirm('Logout?')) {
                window.location.href = '<?php echo APP_URL; ?>/api/logout.php';
            }
        }
    </script>
</body>
</html>
