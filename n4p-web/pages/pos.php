<?php
/**
 * N4P (Not4Posers) POS System
 * POS / Checkout Page
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getCurrentUser($conn);
$products = getAllProducts($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS & Checkout | N4P POS System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        .pos-layout {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            margin-top: 2rem;
        }

        .products-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .product-search {
            position: relative;
        }

        .product-search input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            font-size: 1rem;
        }

        .product-search input:focus {
            border-color: #6b21a8;
            outline: none;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .product-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .product-item:hover {
            border-color: #6b21a8;
            box-shadow: 0 4px 12px rgba(107, 33, 168, 0.15);
        }

        .product-item-image {
            width: 100%;
            height: 120px;
            background: #f3f4f6;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }

        .product-item-name {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            line-height: 1.2;
            height: 2.4em;
            overflow: hidden;
        }

        .product-item-price {
            color: #6b21a8;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .product-item-stock {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .product-item.out-of-stock {
            opacity: 0.6;
            pointer-events: none;
        }

        .product-item.out-of-stock::after {
            content: 'OUT OF STOCK';
            position: absolute;
            color: #ef4444;
            font-weight: 700;
            transform: rotate(-45deg);
        }

        .cart-panel {
            position: sticky;
            top: 100px;
            height: fit-content;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .cart-header {
            background: #f3f4f6;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            text-align: center;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            max-height: 400px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.875rem;
        }

        .cart-item-info {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            color: #1f2937;
        }

        .cart-item-qty-input {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .cart-item-qty-input button {
            width: 24px;
            height: 24px;
            border: 1px solid #e5e7eb;
            background: white;
            cursor: pointer;
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-item-qty-input button:hover {
            background: #f3f4f6;
        }

        .cart-item-qty-input input {
            width: 40px;
            border: none;
            text-align: center;
            padding: 0;
        }

        .cart-item-remove {
            background: none;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-weight: 600;
        }

        .cart-summary {
            padding: 1rem;
            border-top: 2px solid #e5e7eb;
            background: #fafafa;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 1.125rem;
            color: #1f2937;
            border-top: 1px solid #e5e7eb;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }

        .payment-section {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
        }

        .form-group {
            margin-bottom: 0.75rem;
        }

        .form-group label {
            font-size: 0.875rem;
        }

        .form-group input,
        .form-group select {
            font-size: 0.875rem;
            padding: 0.5rem;
        }

        .checkout-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            flex: 1;
        }

        @media (max-width: 1024px) {
            .pos-layout {
                grid-template-columns: 1fr;
            }

            .cart-panel {
                position: static;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="navbar">
            <div class="navbar-brand">
                <a href="<?php echo APP_URL; ?>/" style="text-decoration: none; color: inherit;">N4P</a>
            </div>
            
            <div class="navbar-nav">
                <a href="<?php echo APP_URL; ?>/pages/dashboard.php">Dashboard</a>
                <a href="<?php echo APP_URL; ?>/pages/pos.php" class="active">POS</a>
                <a href="<?php echo APP_URL; ?>/pages/products.php">Products</a>
                <a href="<?php echo APP_URL; ?>/pages/transactions.php">Transactions</a>
                <?php if (isAdmin()): ?>
                    <a href="<?php echo APP_URL; ?>/pages/inventory.php">Inventory</a>
                    <a href="<?php echo APP_URL; ?>/pages/users.php">Users</a>
                <?php endif; ?>
            </div>
            
            <div class="navbar-user">
                <div class="user-menu">
                    <button class="user-menu-toggle" onclick="toggleUserMenu()">👤</button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="<?php echo APP_URL; ?>/pages/profile.php">My Profile</a>
                        <button onclick="logout()" style="color: #ef4444;">Logout</button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- no sidebar on POS page -->

    <!-- Main Content -->
    <main class="main-container no-sidebar">
        <div class="container">
            <h1 class="page-title">Point of Sale (POS)</h1>

            <div class="pos-layout">
                <!-- Products Section -->
                <div class="products-section">
                    <div class="product-search">
                        <input type="text" id="productSearch" placeholder="🔍 Search products..." onkeyup="filterProducts()">
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="products-grid" id="productsGrid">
                                <?php foreach ($products as $product): ?>
                                    <div class="product-item <?php echo $product['stock'] <= 0 ? 'out-of-stock' : ''; ?>" 
                                         onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['selling_price']; ?>, <?php echo $product['stock']; ?>)">
                                        <div class="product-item-image">📦</div>
                                        <div class="product-item-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <div class="product-item-price"><?php echo formatPrice($product['selling_price']); ?></div>
                                        <div class="product-item-stock">Stock: <?php echo $product['stock']; ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Panel -->
                <div class="cart-panel">
                    <div class="cart-header">🛒 CART</div>
                    
                    <div class="cart-items" id="cartItems">
                        <div style="text-align: center; color: #6b7280; padding: 2rem;">
                            Cart is empty
                        </div>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotalDisplay">IDR 0,00</span>
                        </div>
                        <div class="summary-row">
                            <span>Discount:</span>
                            <span id="discountDisplay">-IDR 0,00</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (10%):</span>
                            <span id="taxDisplay">IDR 0,00</span>
                        </div>
                        <div class="summary-row total">
                            <span>TOTAL:</span>
                            <span id="totalDisplay">IDR 0,00</span>
                        </div>
                    </div>

                    <div class="payment-section">
                        <div class="form-group">
                            <label>Customer Name (Optional)</label>
                            <input type="text" id="customerName" placeholder="Customer name">
                        </div>
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select id="paymentMethod">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Discount %</label>
                            <input type="number" id="discountPercentage" min="0" max="100" value="0" onchange="calculateTotal()">
                        </div>
                        <div class="checkout-buttons">
                            <button onclick="clearCart()" class="btn btn-secondary btn-small">Clear</button>
                            <button onclick="processPayment()" class="btn btn-success btn-small">Pay</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let cart = [];

        function addToCart(productId, productName, price, stock) {
            if (stock <= 0) return;
            
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity++;
                }
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: price,
                    quantity: 1,
                    stock: stock
                });
            }
            
            renderCart();
            calculateTotal();
        }

        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            renderCart();
            calculateTotal();
        }

        function updateQuantity(productId, quantity) {
            const item = cart.find(item => item.id === productId);
            if (item) {
                quantity = parseInt(quantity);
                if (quantity > 0 && quantity <= item.stock) {
                    item.quantity = quantity;
                } else if (quantity <= 0) {
                    removeFromCart(productId);
                    return;
                }
            }
            renderCart();
            calculateTotal();
        }

        function renderCart() {
            const cartItemsDiv = document.getElementById('cartItems');
            
            if (cart.length === 0) {
                cartItemsDiv.innerHTML = `<div style="text-align: center; color: #6b7280; padding: 2rem;">Cart is empty</div>`;
                return;
            }
            
            let html = '';
            cart.forEach(item => {
                const subtotal = item.price * item.quantity;
                html += `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div style="color: #6b7280; font-size: 0.75rem;">IDR ${item.price.toLocaleString('id-ID')}</div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <div class="cart-item-qty-input">
                                <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})">−</button>
                                <input type="number" value="${item.quantity}" onchange="updateQuantity(${item.id}, this.value)">
                                <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                            </div>
                            <button class="cart-item-remove" onclick="removeFromCart(${item.id})">✕</button>
                        </div>
                    </div>
                `;
            });
            
            cartItemsDiv.innerHTML = html;
        }

        function calculateTotal() {
            let subtotal = 0;
            cart.forEach(item => {
                subtotal += item.price * item.quantity;
            });
            
            const discountPercentage = parseInt(document.getElementById('discountPercentage').value) || 0;
            const discount = subtotal * (discountPercentage / 100);
            const subtotalAfterDiscount = subtotal - discount;
            const tax = subtotalAfterDiscount * 0.1; // 10% tax
            const total = subtotalAfterDiscount + tax;
            
            document.getElementById('subtotalDisplay').textContent = 'IDR ' + subtotal.toLocaleString('id-ID', {minimumFractionDigits: 2});
            document.getElementById('discountDisplay').textContent = '-IDR ' + discount.toLocaleString('id-ID', {minimumFractionDigits: 2});
            document.getElementById('taxDisplay').textContent = 'IDR ' + tax.toLocaleString('id-ID', {minimumFractionDigits: 2});
            document.getElementById('totalDisplay').textContent = 'IDR ' + total.toLocaleString('id-ID', {minimumFractionDigits: 2});
        }

        function clearCart() {
            if (confirm('Clear cart?')) {
                cart = [];
                renderCart();
                calculateTotal();
                document.getElementById('customerName').value = '';
                document.getElementById('discountPercentage').value = '0';
            }
        }

        function processPayment() {
            if (cart.length === 0) {
                alert('Cart is empty!');
                return;
            }
            
            // Prepare checkout data
            const cartData = JSON.stringify(cart);
            const customerName = document.getElementById('customerName').value || 'Walk-in Customer';
            const paymentMethod = document.getElementById('paymentMethod').value;
            const discountPercentage = parseInt(document.getElementById('discountPercentage').value) || 0;
            
            // Send to checkout handler
            fetch('<?php echo APP_URL; ?>/api/checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    cart: cart,
                    customerName: customerName,
                    paymentMethod: paymentMethod,
                    discountPercentage: discountPercentage
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Transaction completed! ID: ' + data.transaction_id);
                    clearCart();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing payment');
            });
        }

        function filterProducts() {
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            const items = document.querySelectorAll('.product-item');
            
            items.forEach(item => {
                const name = item.querySelector('.product-item-name').textContent.toLowerCase();
                if (name.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('active');
        }

        function logout() {
            if (confirm('Are you sure?')) {
                window.location.href = '<?php echo APP_URL; ?>/api/logout.php';
            }
        }

        // Initialize
        calculateTotal();
    </script>
</body>
</html>
