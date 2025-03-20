<?php
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? null;
$display_name = $_SESSION['display_name'] ?? "Guest";
$cart = $_SESSION['cart'] ?? [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact_number = trim(htmlspecialchars($_POST['contact_number']));
    $order_type = trim(htmlspecialchars($_POST['order_type']));
    $payment_method = trim(htmlspecialchars($_POST['payment_method'] ?? 'Cash'));
    $status = 'Pending';

    if (empty($user_id)) {
        echo "<script>alert('You must be logged in to place an order.'); window.location.href='login.php';</script>";
        exit();
    }

    if (empty($cart)) {
        echo "<script>alert('Your cart is empty. Please add items before checkout.'); window.location.href='lang.php';</script>";
        exit();
    }

    $total_amount = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cart));

    // Use NOW() for accurate date & time
    $sql = "INSERT INTO orders (user_id, customer_name, contact_number, order_type, total_amount, order_date, order_time, payment_method, status) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("issssss", $user_id, $display_name, $contact_number, $order_type, $total_amount, $payment_method, $status);

    if ($stmt->execute()) {
        $order_id = $con->insert_id;
        $stmt->close();

        foreach ($cart as $item) {
            $product_name = htmlspecialchars($item['name']);
            $item_price = floatval($item['price']);
            $quantity = intval($item['quantity']);
            $subtotal = $item_price * $quantity;

            $sql_image = "SELECT product_image FROM inventory WHERE product_name = ?";
            $stmt_image = $con->prepare($sql_image);
            $stmt_image->bind_param("s", $product_name);
            $stmt_image->execute();
            $result_image = $stmt_image->get_result();
            $image_row = $result_image->fetch_assoc();
            $product_image = $image_row['product_image'] ?? 'uploads/default.png';
            $stmt_image->close();

            $sql_item = "INSERT INTO order_items (order_id, product_name, item_price, quantity, subtotal, product_image) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_item = $con->prepare($sql_item);
            $stmt_item->bind_param("isdiis", $order_id, $product_name, $item_price, $quantity, $subtotal, $product_image);
            $stmt_item->execute();
            $stmt_item->close();
        }

        $_SESSION['last_order_id'] = $order_id;
        unset($_SESSION['cart']);
        header("Location: receipt.php?order_id=$order_id");
        exit();

    } else {
        echo "<script>alert('An error occurred while processing your order.'); window.location.href='checkout.php';</script>";
        exit();
    }
}

// Fetch past orders with correct order date
$sql = "SELECT oi.*, o.order_date FROM order_items oi 
        JOIN orders o ON oi.order_id = o.order_id 
        WHERE o.user_id = ? 
        ORDER BY o.order_date DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Past Orders</title>
    <link rel="stylesheet" href="past.css">
</head>
<body>

<div class="header">
    <div class="header-left">Welcome, <?php echo htmlspecialchars($display_name); ?>!</div>
    <div class="header-right">
        <div class="user-dropdown">
            <span>👤 <?php echo htmlspecialchars($display_name); ?> ▼</span>
            <div class="dropdown-menu">
                <a href="profile.php">👤 Profile</a>
                <a href="lang.php">🛍 View Products</a> 
                <a href="pastorders.php">📦 Past Orders</a>
                <a href="vouchers.php">🎟 Vouchers</a>
                <a href="help.php">❓ Help Center</a>
                <a href="logout.php">🚪 Logout</a>
            </div>
        </div>
        <div class="cart-icon" onclick="toggleCart()">
            🛒 Cart: <span id="cartCount">0</span> items
        </div>
    </div>
</div>

<!-- Cart Modal -->
<div id="cart-modal" class="cart-modal">
    <div class="cart-overlay" onclick="toggleCart()"></div>
    <div class="cart-box">
        <div class="cart-header">
            <h2>Your Cart</h2>
            <button class="close-modal" onclick="toggleCart()">✖</button>
        </div>
        <div id="cart-items" class="cart-items"></div>
        <p id="cart-total" class="cart-total">Total: ₱0.00</p>
        <div class="cart-actions">
            <button class="clear-cart" onclick="clearCart()">🗑 Clear Cart</button>
            <button class="checkout-button" onclick="checkout()">Proceed to Checkout</button>
        </div>
    </div>
</div>

<div class="container">
    <h2>My Past Orders</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                    <td>
                        <img src="<?= !empty($row['product_image']) ? htmlspecialchars($row['product_image']) : 'uploads/default.png' ?>" 
                             class="product-image" alt="Product Image" width="80" height="80">
                    </td>
                    <td><?= htmlspecialchars($row['quantity']) ?></td>
                    <td>₱<?= number_format($row['subtotal'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p class="no-orders">You have no past orders.</p>
    <?php endif; ?>
</div>

<script>
// Initialize cart from session storage
let cart = JSON.parse(sessionStorage.getItem('cart')) || [];

// Update cart UI (fixes missing items & count)
function updateCartUI() {
    let cartItems = document.getElementById('cart-items');
    let cartCount = document.getElementById('cartCount');
    let totalPrice = 0;

    cartItems.innerHTML = '';
    cart.forEach((item, index) => {
        totalPrice += item.price * item.quantity;
        cartItems.innerHTML += `
            <div class='cart-item'>
                <img src="${item.image}" alt="Product Image" class="cart-item-img">
                <div class="cart-item-details">
                    <h4>${item.name}</h4>
                    <p>₱${item.price.toFixed(2)}</p>
                    <div class="cart-quantity">
                        <input type="number" min="1" value="${item.quantity}" onchange="updateQuantity(${index}, this.value)">
                        <button class="cart-remove" onclick="removeFromCart(${index})">❌</button>
                    </div>
                </div>
            </div>
        `;
    });

    document.getElementById('cart-total').textContent = `Total: ₱${totalPrice.toFixed(2)}`;
    cartCount.textContent = cart.length;
}

// Reorder function (adds items to cart)
function reorder(name, quantity, image) {
    let found = cart.find(item => item.name === name);
    
    if (found) {
        found.quantity += quantity;
    } else {
        cart.push({ name: name, price: 0, image: image, quantity: quantity });
    }

    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
    alert(name + " has been added to your cart!");
}

// Remove item from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
}

// Clear cart
function clearCart() {
    cart = [];
    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
}

// Toggle cart modal
function toggleCart() {
    document.getElementById('cart-modal').classList.toggle("show");
}

// Proceed to checkout
function checkout() {
    fetch('save_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cart)
    }).then(() => {
        window.location.href = 'checkout.php';
    });
}

// Update quantity of cart items
function updateQuantity(index, newQuantity) {
    newQuantity = Math.max(1, parseInt(newQuantity) || 1);
    cart[index].quantity = newQuantity;
    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
}

// Ensure cart updates correctly on page load
updateCartUI();
</script>

</body>
</html>

<?php
$stmt->close();
$con->close();
?>
