<?php
// Ensure session is only started if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'connection.php'; // Ensure this file correctly establishes $con

// Properly check if the database connection is established
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle order deletion (Completely remove from database)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    $delete_sql = "DELETE FROM orders WHERE order_id = ?";

    $stmt = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => mysqli_error($con)]);
    }
    exit;
}

// Fetch orders with product details
$sql = "
    SELECT o.order_id, o.user_id, o.customer_name, oi.product_name, o.order_type, 
           o.payment_method, o.total_amount, o.order_date, o.order_time
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    ORDER BY o.customer_name, o.order_time DESC
";

$result = mysqli_query($con, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($con));
}

// Organize orders by customer
$orders_by_customer = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders_by_customer[$row['customer_name']][] = $row;
}

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Order Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #121212;
            color: white;
            text-align: center;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            padding: 20px;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.1);
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
        .table {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        .table th, .table td {
            padding: 12px;
            border: 1px solid #ffcc00;
            text-align: center;
            color: #fff;
        }
        .table th {
            background: #ffcc00;
            color: black;
            cursor: pointer;
        }
        .hidden-orders {
            display: none;
        }
        .btn {
            font-weight: bold;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
    </style>
</head>
<body>
<div class="mt-4">
    <a href="bar.php" class="btn btn-warning">Back to Inventory</a>
    <a href="lang.php" class="btn btn-info">Product View</a>
    <a href="sales_report.php" class="btn btn-info">Sales Report</a>
    <form method="POST" style="display:inline;">
        <button type="submit" name="logout" class="btn btn-danger">Logout</button>
    </form>
</div>
<div class="container mt-5">
    <h2>Order Details</h2>
    <div class="table-container">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Cashier Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders_by_customer as $customer_name => $orders): ?>
                    <tr class="customer-row" data-customer="<?php echo htmlspecialchars($customer_name); ?>">
                        <td><?php echo htmlspecialchars($customer_name); ?></td>
                    </tr>
                    <tr class="hidden-orders" id="orders-<?php echo htmlspecialchars($customer_name); ?>">
                        <td colspan="1">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Product Name</th>
                                        <th>Order Type</th>
                                        <th>Payment Method</th>
                                        <th>Total Amount</th>
                                        <th>Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr id="order-<?php echo $order['order_id']; ?>">
                                            <td><?php echo $order['order_id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo $order['order_type']; ?></td>
                                            <td><?php echo $order['payment_method']; ?></td>
                                            <td><?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><?php echo $order['order_time']; ?></td>
                                            <td>
                                                <button class="btn btn-danger btn-sm delete-btn" data-order-id="<?php echo $order['order_id']; ?>">Delete</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.querySelectorAll('.customer-row').forEach(row => {
        row.addEventListener('click', () => {
            const customer = row.getAttribute('data-customer');
            const ordersRow = document.getElementById(`orders-${customer}`);
            ordersRow.style.display = ordersRow.style.display === 'none' ? 'table-row' : 'none';
        });
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            if (!confirm("Are you sure you want to delete this order?")) return;

            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `delete_order=1&order_id=${orderId}`
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`order-${orderId}`).remove();
                } else {
                    alert('Failed to delete order: ' + data.error);
                }
            }).catch(error => console.error('Error:', error));
        });
    });
</script>
</body>
</html>
