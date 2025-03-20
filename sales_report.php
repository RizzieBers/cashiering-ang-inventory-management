<?php
session_start();
include 'connection.php';

if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Fetch sales data with subtotal grouped by product
// Fetch sales data with subtotal grouped by product from the correct table
$sales_query = "
    SELECT 
        oi.product_name, 
        SUM(oi.quantity) AS total_quantity, 
        SUM(oi.subtotal) AS total_sales
    FROM order_items oi
    LEFT JOIN orders o ON o.order_id = oi.order_id
    GROUP BY oi.product_name
    ORDER BY total_sales DESC
";

$sales_result = mysqli_query($con, $sales_query);


// Calculate overall total sales
$total_sales = 0;
$sales_data = [];
while ($row = mysqli_fetch_assoc($sales_result)) {
    $total_sales += $row['total_sales'];
    $sales_data[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Sales Report</title>
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
        <a href="order.php" class="btn btn-info">Order Details</a>
        <a href="sales_report.php" class="btn btn-info">Sales Report</a>
        <form method="POST" style="display:inline;">
            <button type="submit" name="logout" class="btn btn-danger">Logout</button>
        </form>
    </div>

    <div class="container mt-5 glass-effect">
        <h2>Sales Report by Product</h2>
        <h4>Total Sales: PHP <?php echo number_format($total_sales, 2); ?></h4>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mt-4">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Total Quantity Sold</th>
                        <th>Total Sales (PHP)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sales_data)): ?>
                        <?php foreach ($sales_data as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><?php echo number_format($order['total_quantity']); ?></td>
                                <td><?php echo number_format($order['total_sales'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No sales data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
