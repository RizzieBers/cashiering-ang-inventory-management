<?php
session_start();
include 'connection.php'; // Ensure this is at the top
$display_name = isset($_SESSION['display_name']) ? $_SESSION['display_name'] : "Guest";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link rel="stylesheet" href="lang.css">
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
        <!-- CLICKABLE CART -->
        <div class="cart-icon" onclick="toggleCart()">
            🛒 Cart: <span id="cartCount">0</span> items
        </div>
    </div>
</div>

<!-- Redesigned Cart Modal -->
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

<!-- SEARCH BAR -->
<div class="search-container">
    <input type="text" id="search-bar" placeholder="Search for products 🔍" oninput="filterProducts()">
</div>

<?php
include 'connection.php';
$categories = ['Protein Iced Coffee', 'Protein Shake', 'Refreshing Lemonade'];
foreach ($categories as $category) {
    echo "<h2>" . htmlspecialchars($category) . "</h2>";
    echo "<div class='product-list'>";

    $sql = "SELECT * FROM inventory WHERE category = '$category'";
    $result = $con->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $isAvailable = $row['stock'] > 0;
            echo "<div class='product-item' data-name='" . htmlspecialchars($row['product_name']) . "'>";
            echo "<img src='uploads/" . htmlspecialchars($row['product_image']) . "' alt='Product Image'>";
            echo "<h3>" . htmlspecialchars($row['product_name']) . "</h3>";
            echo "<p>₱" . htmlspecialchars($row['product_price']) . "</p>";
            if ($isAvailable) {
                echo "<button onclick=\"addToCart('" . addslashes($row['product_name']) . "', " . floatval($row['product_price']) . ", '" . addslashes($row['product_image']) . "')\">Add to Cart</button>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>OUT OF STOCK</p>";
            }
            echo "</div>";
        }
    }
    echo "</div>";
}
$con->close();
?>

<script>
let cart = JSON.parse(sessionStorage.getItem('cart')) || [];
document.getElementById('cartCount').textContent = cart.length;

function addToCart(name, price, image) {
    let found = cart.find(item => item.name === name);
    if (found) {
        found.quantity++;
    } else {
        cart.push({ name: name, price: price, image: image, quantity: 1 });
    }
    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
}

function updateCartUI() {
    let cartItems = document.getElementById('cart-items');
    let totalPrice = 0;
    cartItems.innerHTML = '';

    cart.forEach((item, index) => {
        totalPrice += item.price * item.quantity;
        cartItems.innerHTML += `
            <div class='cart-item'>
                <img src="uploads/${item.image}" alt="Product Image" class="cart-item-img" onerror="this.src='uploads/default.png'">
                <div class="cart-item-details">
                    <h4>${item.name}</h4>
                    <p>₱${item.price}</p>
                    <div class="cart-quantity">
                        <input type="number" min="1" value="${item.quantity}" onchange="updateQuantity(${index}, this.value)">
                        <button class="cart-remove" onclick="removeFromCart(${index})">❌</button>
                    </div>
                </div>
            </div>
        `;
    });

    document.getElementById('cart-total').textContent = `Total: ₱${totalPrice.toFixed(2)}`;
    document.getElementById('cartCount').textContent = cart.length;
}

function removeFromCart(index) {
    cart.splice(index, 1);
    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
}

function clearCart() {
    cart = [];
    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
}

function toggleCart() {
    let cartModal = document.getElementById('cart-modal');
    cartModal.classList.toggle("show");
}

function checkout() {
    fetch('save_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cart)
    }).then(() => {
        window.location.href = 'checkout.php';
    });
}

function updateQuantity(index, newQuantity) {
    newQuantity = parseInt(newQuantity) || 1; // Ensure it's a valid number
    newQuantity = Math.max(1, newQuantity); // Prevent quantity below 1

    cart[index].quantity = newQuantity;
    sessionStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI(); // Refresh cart UI instantly
}
function filterProducts() {
    let searchQuery = document.getElementById('search-bar').value.toLowerCase();
    let products = document.querySelectorAll('.product-item');

    products.forEach(product => {
        let productName = product.getAttribute('data-name').toLowerCase();
        if (productName.includes(searchQuery)) {
            product.style.display = "block"; // Show matching product
        } else {
            product.style.display = "none"; // Hide non-matching product
        }
    });
}


updateCartUI();
</script>

</body>
</html>
