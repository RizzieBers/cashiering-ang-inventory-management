<?php
session_start();
include 'connection.php';

// Ensure order ID is provided
if (!isset($_GET['order_id'])) {
    echo "<script>alert('Invalid order.'); window.location.href='lang.php';</script>";
    exit();
}

$order_id = intval($_GET['order_id']);

// Fetch order details
$sql = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();
$stmt->close();

if (!$order) {
    echo "<script>alert('Order not found.'); window.location.href='lang.php';</script>";
    exit();
}

// Fetch order items
$sql_items = "SELECT * FROM order_items WHERE order_id = ?";
$stmt_items = $con->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="receipt.css">
</head>
<body>
    <div class="receipt-container">
        <h2>Order Receipt</h2>
        <p><strong>Order ID:</strong> <?php echo $order['order_id']; ?></p>
        <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Order Type:</strong> <?php echo htmlspecialchars($order['order_type']); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
        <p><strong>Order Date:</strong> <?php echo date("F j, Y"); ?></p>
        <p><strong>Order Time:</strong> <?php echo date("h:i A"); ?></p>

        <hr>
        <h3>Order Summary</h3>
        <table class="receipt-table">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
            <?php
            $total_amount = 0;
            foreach ($order_items as $item) {
                $total_amount += floatval($item['subtotal']);
            ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td>₱<?php echo number_format(floatval($item['item_price']), 2); ?></td>
                <td><?php echo intval($item['quantity']); ?></td>
                <td>₱<?php echo number_format(floatval($item['subtotal']), 2); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <td colspan="3" class="total-row">Total:</td>
                <td><strong>₱<?php echo number_format($total_amount, 2); ?></strong></td>
            </tr>
        </table>
        <a href="lang.php" class="back-btn">Back to Shop</a>
    </div>
</body>
</html>
