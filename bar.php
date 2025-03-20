<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "WEB";

$con = new mysqli($servername, $username, $password, $dbname);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}


// Handle adding new products
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        // Your existing code for adding a product
        $productName = $_POST['product_name'];
        $productPrice = $_POST['product_price'];
        $stock = $_POST['stock'];
        $category = $_POST['category'];
        $image = $_FILES['product_image'];

        $imagePath = '';
        if ($image && $image['tmp_name']) {
            $imageName = basename($image['name']);
            $imagePath = $imageName;

           
        }

    

        // Insert into inventory (existing code)
    }

    if (isset($_POST['update_inventory'])) {
        $productId = $_POST['product_id'];
        $newstock = $_POST['new_stock'];
    
        $updateInventorySql = "UPDATE inventory SET stock = ? WHERE product_id = ?";
        $stmt = $con->prepare($updateInventorySql);
        $stmt->bind_param('ii', $newstock, $productId);
        $stmt->execute();
    
        // Redirect to refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
}


// Fetch inventory details from the database
$inventorySql = "SELECT * FROM inventory";
$inventoryResult = $con->query($inventorySql);

// Handle product addition or updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $productName = $_POST['product_name'];
        $productPrice = $_POST['product_price'];
        $stock = $_POST['stock'];
        $category = $_POST['category'];
        $image = $_FILES['product_image'];

        // Handle image upload
        $imagePath = '';
        if ($image && $image['tmp_name']) {
            $imageName = basename($image['name']);
            $imagePath = 'uploads/' . $imageName;
            if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
                die("Error uploading file.");
            }
        }

        // Insert new product into inventory
        $addProductSql = "INSERT INTO inventory (product_name, product_price, stock, category, product_image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $con->prepare($addProductSql);
        $stmt->bind_param('sdiss', $productName, $productPrice, $stock, $category, $imageName);
        $stmt->execute();

        // Refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['update_inventory'])) {
        $productId = $_POST['product_id'];
        $newstock = $_POST['new_stock'];

        // Update product stock in inventory
        $updateInventorySql = "UPDATE inventory SET stock = ? WHERE product_id = ?";
        $stmt = $con->prepare($updateInventorySql);
        $stmt->bind_param('ii', $newstock, $productId);
        $stmt->execute();

        // Refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Handle product removal
    if (isset($_POST['remove_product'])) {
        $productId = $_POST['product_id'];

        // Delete the product from inventory
        $removeProductSql = "DELETE FROM inventory WHERE product_id = ?";
        $stmt = $con->prepare($removeProductSql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();

        // Refresh the page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['purchase'])) {
        $productId = $_POST['product_id'];
        $stockPurchased = $_POST['stock'];
    
        // Fetch and check current stock
        $sql = "SELECT stock FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    
        if ($row && $row['stock'] >= $stockPurchased) {
            $newStock = $row['stock'] - $stockPurchased;
    
            // Update stock in database
            $updateSql = "UPDATE products SET stock = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('ii', $newStock, $productId);
            $updateStmt->execute();
        } else {
            echo "Insufficient stock!";
        }
    }
    

    // Handle proceeding to ordering_menu.php
    if (isset($_POST['proceed_to_ordering'])) {
        $productId = $_POST['product_id'];

        // Redirect to ordering_menu.php with the product ID
        header("Location: lang.php?id=" . $productId);
        exit;
    }


    
}


?>

<?php
session_start(); // Start the session at the very beginning

// Handle logout request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
    }
    header("Location: login.php"); // Redirect to login.php after logout
    exit;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Arial:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bar.css">
   <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <!-- Include your CSS here -->
</head>
<body>

<div class="mt-4">
            <a href="order.php" class="btn btn-warning">Order Details</a>
            <a href="lang.php" class="btn btn-info">Product View</a>
            <a href="sales_report.php" class="btn btn-info">Sales Report</a>
            <form method="POST" style="display:inline;">
                <button type="submit" name="logout" class="btn btn-danger">Logout</button>
            </form>
        </div>

<div class="container">
    <!-- Add New Product Form -->
    <div class="product-form">
        <h4>Add New Product</h4>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" name="product_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="product_price">Product Price</label>
                <input type="number" name="product_price" class="form-control" step="1" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" name="stock" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select name="category" class="form-control" required>
                    <option value="">Select a Category</option>
                    <option value="Protein Iced Coffee">Protein Iced Coffee</option>
                    <option value="Protein Shake">Protein Shake</option>
                    <option value="Refreshing Lemonade">Refreshing Lemonade</option>
                </select>
            </div>
            <div class="form-group">
                <label for="product_image">Product Image</label>
                <input type="file" name="product_image" class="form-control-file">
            </div>
            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
        </form>
    </div>

    <!-- Inventory Management Table -->
    <div class="card mt-4 table-container">
    <div class="card-header">
    <h2 style="color: black;">Inventory Management</h2>

    </div>
    <div class="card-body p-4">
    <div class="table-container" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-striped table-hover">

                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Update Stock</th>
                        <th>Remove Product</th>
                    </tr>
                </thead>
                <tbody>
                        <?php
                        // Include database connection
                        include 'connection.php';

                        // Fetch inventory from the database
                        $inventoryQuery = "SELECT * FROM inventory";
                        $inventoryResult = $con->query($inventoryQuery);

                        if ($inventoryResult->num_rows > 0):
                            while ($row = $inventoryResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo number_format(htmlspecialchars($row['product_price']), 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td>
                                        <?php if (!empty($row['product_image']) && file_exists('uploads/' . $row['product_image'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($row['product_image']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" style="width: 100px; height: auto;">
                                        <?php else: ?>
                                            <span>No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                            <input type="number" name="new_stock" value="<?php echo $row['stock']; ?>" min="0">
                                            <button type="submit" name="update_inventory" class="btn btn-primary">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="">
                                            <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                            <button type="submit" name="remove_product" class="btn btn-danger">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; 
                        else: ?>
                            <tr><td colspan="8">No products found in inventory.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="footer">
    <form method="POST" action="">
        <button type="submit" name="logout" class="logout-btn">LOG OUT</button>
    </form>
</div>

</body>
</html>

