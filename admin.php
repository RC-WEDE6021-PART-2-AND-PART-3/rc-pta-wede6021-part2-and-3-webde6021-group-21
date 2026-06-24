<?php
session_start();
include 'includes/DBConn.php';
include 'includes/navbar.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php?message=Access denied. Admin only.");
    exit();
}

// ==================== CREATE - Add Product ====================
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $seller_id = intval($_POST['seller_id']);
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image_name = time() . '_' . $filename;
            if (!file_exists('images')) {
                mkdir('images', 0777, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $image_name);
            $success = "Product added successfully with image!";
        } else {
            $error = "Invalid image format. Use JPG, PNG, or GIF.";
        }
    } else {
        $error = "Please upload a product image.";
    }
    
    if (empty($error)) {
        $conn->query("INSERT INTO tblclothes (name, description, price, quantity, image, seller_id, is_approved) 
                      VALUES ('$name', '$description', $price, $quantity, '$image_name', $seller_id, 1)");
        $success = "Product added successfully!";
    }
}

// ==================== UPDATE - Edit Product ====================
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $seller_id = intval($_POST['seller_id']);
    
    $conn->query("UPDATE tblclothes SET 
                  name='$name', 
                  description='$description', 
                  price=$price, 
                  quantity=$quantity,
                  seller_id=$seller_id
                  WHERE clothes_id=$id");
    $success = "Product updated successfully!";
}

// ==================== DELETE - Delete Product ====================
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    $conn->query("DELETE FROM tblclothes WHERE clothes_id=$id");
    $success = "Product deleted successfully!";
    header("Location: admin.php");
    exit();
}

// ==================== READ - Get all products ====================
$products = $conn->query("SELECT * FROM tblclothes ORDER BY clothes_id DESC");
$sellers = $conn->query("SELECT user_id, name, username FROM tbluser WHERE role='seller'");

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM tblclothes")->fetch_assoc();
$total_stock = $conn->query("SELECT SUM(quantity) as total FROM tblclothes")->fetch_assoc();
$total_value = $conn->query("SELECT SUM(price * quantity) as value FROM tblclothes")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
        }
        .container {
            max-width: 1400px;
            margin: 20px auto 40px;
            padding: 20px;
        }
        h1 {
            color: #0b1a33;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e4e8;
            padding-bottom: 10px;
        }
        
        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #0b1a33;
        }
        .stat-card .label {
            color: #666;
            margin-top: 5px;
        }
        
        /* Add Product Form */
        .add-product-form {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .add-product-form h3 {
            margin-bottom: 20px;
            color: #0b1a33;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }
        textarea {
            resize: vertical;
        }
        
        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #0b1a33;
            color: white;
        }
        .btn-primary:hover {
            background: #1f2f4d;
        }
        .btn-edit {
            background: #ffc107;
            color: black;
        }
        .btn-edit:hover {
            background: #e0a800;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        /* Products Table */
        .products-table {
            background: white;
            border-radius: 15px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .products-table h3 {
            padding: 20px 20px 0 20px;
            color: #0b1a33;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background: white;
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 15px;
        }
        .modal-content h3 {
            margin-bottom: 20px;
        }
        
        /* Messages */
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .container { 
                margin: 20px auto 40px;
                padding: 15px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            th {
                display: none;
            }
            tr {
                margin-bottom: 15px;
                border: 1px solid #eee;
                padding: 10px;
                border-radius: 8px;
            }
            td {
                display: flex;
                justify-content: space-between;
                padding: 8px;
            }
            td:before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>📦 Manage Products</h1>
    <div class="subtitle">Add, edit, or delete products in your store inventory</div>
    
    <?php if (isset($success)): ?>
        <div class="success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?php echo $total_products['count']; ?></div>
            <div class="label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_stock['total']; ?></div>
            <div class="label">Total Items in Stock</div>
        </div>
        <div class="stat-card">
            <div class="number">R <?php echo number_format($total_value['value'], 2); ?></div>
            <div class="label">Inventory Value</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $sellers->num_rows; ?></div>
            <div class="label">Active Sellers</div>
        </div>
    </div>
    
    <!-- ==================== CREATE PRODUCT FORM ==================== -->
    <div class="add-product-form">
        <h3>➕ Add New Product</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" required placeholder="e.g., Vintage Denim Jacket">
                </div>
                <div class="form-group">
                    <label>Price (R) *</label>
                    <input type="number" name="price" step="0.01" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Stock Quantity *</label>
                    <input type="number" name="quantity" required placeholder="How many in stock?">
                </div>
                <div class="form-group">
                    <label>Seller *</label>
                    <select name="seller_id" required>
                        <option value="">Select Seller</option>
                        <?php 
                        $seller_list = $conn->query("SELECT user_id, name, username FROM tbluser WHERE role='seller'");
                        while($seller = $seller_list->fetch_assoc()): ?>
                            <option value="<?php echo $seller['user_id']; ?>"><?php echo $seller['name']; ?> (<?php echo $seller['username']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Product Image *</label>
                    <input type="file" name="image" accept="image/*" required>
                    <small style="color: #666;">Upload JPG, PNG, or GIF (max 2MB)</small>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Description *</label>
                    <textarea name="description" rows="3" required placeholder="Describe the product - condition, size, material, features..."></textarea>
                </div>
            </div>
            <button type="submit" name="add_product" class="btn btn-primary">➕ Add Product</button>
        </form>
    </div>
    
    <!-- ==================== READ PRODUCTS TABLE ==================== -->
    <div class="products-table">
        <h3>📋 All Products</h3>
        <?php if ($products->num_rows == 0): ?>
            <p style="padding: 40px; text-align: center;">No products found. Add your first product above.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Seller</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($product = $products->fetch_assoc()): 
                        // Get seller name
                        $seller_name = $conn->query("SELECT name FROM tbluser WHERE user_id = {$product['seller_id']}")->fetch_assoc();
                    ?>
                        <tr>
                            <td data-label="ID"><?php echo $product['clothes_id']; ?></td>
                            <td data-label="Image">
                                <?php if ($product['image'] && file_exists('images/'.$product['image'])): ?>
                                    <img src="images/<?php echo $product['image']; ?>" class="product-image">
                                <?php else: ?>
                                    <span style="color: #999;">No image</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Name"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td data-label="Price">R <?php echo number_format($product['price'], 2); ?></td>
                            <td data-label="Stock">
                                <?php if ($product['quantity'] <= 5): ?>
                                    <span style="color: #dc3545; font-weight: bold;">⚠️ <?php echo $product['quantity']; ?> left</span>
                                <?php else: ?>
                                    <?php echo $product['quantity']; ?>
                                <?php endif; ?>
                            </td>
                            <td data-label="Seller"><?php echo $seller_name['name'] ?? 'Unknown'; ?></td>
                            <td data-label="Actions">
                                <button class="btn btn-edit" onclick="openEditModal(
                                    <?php echo $product['clothes_id']; ?>, 
                                    '<?php echo addslashes($product['name']); ?>', 
                                    '<?php echo addslashes($product['description']); ?>', 
                                    <?php echo $product['price']; ?>, 
                                    <?php echo $product['quantity']; ?>, 
                                    <?php echo $product['seller_id']; ?>
                                )">✏️ Edit</button>
                                <a href="?delete_product=<?php echo $product['clothes_id']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Are you sure you want to delete <?php echo addslashes($product['name']); ?>? This action cannot be undone.')">
                                   🗑️ Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Back to Dashboard -->
    <div style="margin-top: 30px;">
        <a href="adminDashboard.php" class="btn btn-primary">← Back to Admin Dashboard</a>
    </div>
</div>

<!-- ==================== UPDATE PRODUCT MODAL ==================== -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>✏️ Edit Product</h3>
        <form method="POST">
            <input type="hidden" name="product_id" id="edit_id">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>Price (R)</label>
                <input type="number" name="price" id="edit_price" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="quantity" id="edit_quantity" required>
            </div>
            <div class="form-group">
                <label>Seller</label>
                <select name="seller_id" id="edit_seller_id" required>
                    <option value="">Select Seller</option>
                    <?php 
                    $all_sellers = $conn->query("SELECT user_id, name, username FROM tbluser WHERE role='seller'");
                    while($seller = $all_sellers->fetch_assoc()): ?>
                        <option value="<?php echo $seller['user_id']; ?>"><?php echo $seller['name']; ?> (<?php echo $seller['username']; ?>)</option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="edit_product" class="btn btn-primary">💾 Save Changes</button>
                <button type="button" onclick="closeModal()" class="btn" style="background: #ccc;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, description, price, quantity, seller_id) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('edit_seller_id').value = seller_id;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    let modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>