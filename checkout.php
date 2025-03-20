<?php
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['display_name'] ?? "Guest";
$contact_number = $_SESSION['contact_number'] ?? "Not Provided";
$cart = $_SESSION['cart'] ?? [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_type = trim(htmlspecialchars($_POST['order_type']));
    $order_date = date('Y-m-d'); // Automatically set to today's date
    $order_time = date("H:i:s");
    $payment_method = "Cash";
    $status = "Pending";

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

    // Insert order details into the 'orders' table
    $order_sql = "INSERT INTO orders (user_id, customer_name, contact_number, order_type, total_amount, order_date, order_time, payment_method, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $order_stmt = $con->prepare($order_sql);
    $order_stmt->bind_param("issssdsss", $user_id, $user_name, $contact_number, $order_type, $total_amount, $order_date, $order_time, $payment_method, $status);
    $order_stmt->execute();
    $order_id = $con->insert_id;
    $order_stmt->close();

    // Insert each item in the cart into the 'order_items' table
    $item_sql = "INSERT INTO order_items (order_id, product_name, item_price, quantity, subtotal, product_image) 
                 VALUES (?, ?, ?, ?, ?, ?)";
    $item_stmt = $con->prepare($item_sql);

    foreach ($cart as $item) {
        $product_name = htmlspecialchars($item['name']);
        $item_price = $item['price'];
        $quantity = intval($item['quantity']);
        $subtotal = $item_price * $quantity;
        $product_image = !empty($item['image']) ? (strpos($item['image'], 'uploads/') === false ? 'uploads/' . $item['image'] : $item['image']) : 'uploads/default.png';
        $item_stmt->bind_param("isdiis", $order_id, $product_name, $item_price, $quantity, $subtotal, $product_image);
        $item_stmt->execute();
    }
    $item_stmt->close();

    // Clear the cart and redirect to the receipt page
    unset($_SESSION['cart']);
    header("Location: receipt.php?order_id=$order_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>

<div class="checkout-container">
    <h2>Checkout</h2>
    
    <?php if (!empty($cart)): ?>
        <table class="checkout-table">
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
            <?php
            $total = 0;
            foreach ($cart as $item):
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;

                $imagePath = !empty($item['image']) ? (strpos($item['image'], 'uploads/') === false ? 'uploads/' . $item['image'] : $item['image']) : 'uploads/default.png';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product Image" width="50"></td>
                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>₱<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4" class="total-row">Total:</td>
                <td><strong>₱<?php echo number_format($total, 2); ?></strong></td>
            </tr>
        </table>

        <form method="POST" action="checkout.php">
            <label for="order_type">Order Type:</label>
            <select name="order_type" id="order_type" required>
                <option value="Dine-in">Dine-in</option>
                <option value="Takeout">Takeout</option>
            </select>

            <button type="submit" class="checkout-btn">Place Order</button>
            <a href="lang.php" class="back-btn">Back to Shop</a>
        </form>

    <?php else: ?>
        <p class="empty-cart-message">Your cart is empty.</p>
        <a href="lang.php" class="back-btn">Back to Shop</a>
    <?php endif; ?>
</div>

</body>
</html>
