<?php
session_start();
include 'includes/DBConn.php';
include 'includes/navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please login first");
    exit();
}

// Check if user is a seller
if ($_SESSION['role'] != 'seller') {
    header("Location: index.php?message=Access denied. Seller only area.");
    exit();
}

$seller_id = $_SESSION['user_id'];
$seller_name = $_SESSION['name'];

// Handle Add New Product
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    
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
        } else {
            $error = "Only JPG, PNG, GIF images are allowed.";
        }
    } else {
        $error = "Please upload an image for your product.";
    }
    
    if (empty($error) && !empty($image_name)) {
        $conn->query("INSERT INTO tblclothes (name, description, price, quantity, image, seller_id, is_approved) 
                      VALUES ('$name', '$description', $price, $quantity, '$image_name', $seller_id, 0)");
        $success = "Product added successfully! Admin will review it soon.";
    }
}

// Handle Edit Product
if (isset($_POST['edit_product'])) {
    $product_id = intval($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    
    $conn->query("UPDATE tblclothes SET 
                  name='$name', 
                  description='$description', 
                  price=$price, 
                  quantity=$quantity 
                  WHERE clothes_id=$product_id AND seller_id=$seller_id");
    $success = "Product updated successfully!";
}

// Handle Delete Product
if (isset($_GET['delete_product'])) {
    $product_id = intval($_GET['delete_product']);
    $conn->query("DELETE FROM tblclothes WHERE clothes_id=$product_id AND seller_id=$seller_id");
    header("Location: seller_dashboard.php");
    exit();
}

// Handle Update Order Status
if (isset($_POST['update_order_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE tblorders SET status='$status' WHERE order_id=$order_id");
    $success = "Order status updated!";
}

// Get seller's products
$products = $conn->query("SELECT * FROM tblclothes WHERE seller_id = $seller_id ORDER BY clothes_id DESC");

// Get statistics
$total_products = $products->num_rows;
$approved_products = $conn->query("SELECT COUNT(*) as count FROM tblclothes WHERE seller_id = $seller_id AND is_approved = 1")->fetch_assoc();
$pending_products = $conn->query("SELECT COUNT(*) as count FROM tblclothes WHERE seller_id = $seller_id AND (is_approved = 0 OR is_approved IS NULL)")->fetch_assoc();

// Get orders for seller's products
$orders = $conn->query("
    SELECT DISTINCT o.*, u.name as customer_name, u.username, u.user_id as customer_id
    FROM tblorders o
    JOIN tblorderitems oi ON o.order_id = oi.order_id
    JOIN tblclothes c ON oi.clothes_id = c.clothes_id
    JOIN tbluser u ON o.user_id = u.user_id
    WHERE c.seller_id = $seller_id
    ORDER BY o.order_date DESC
");

// Get sales statistics
$stats = $conn->query("
    SELECT 
        COUNT(DISTINCT o.order_id) as total_orders,
        SUM(oi.quantity) as total_items_sold,
        SUM(oi.quantity * oi.price) as total_revenue
    FROM tblorderitems oi
    JOIN tblclothes c ON oi.clothes_id = c.clothes_id
    JOIN tblorders o ON oi.order_id = o.order_id
    WHERE c.seller_id = $seller_id AND o.status = 'completed'
")->fetch_assoc();

// Get seller's sell requests
$seller_requests = $conn->query("SELECT * FROM tblsellerrequests WHERE user_id = $seller_id ORDER BY request_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Dashboard - Pastimes</title>
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
            margin-bottom: 5px;
            margin-top: 0;
        }
        .welcome {
            color: #666;
            margin-bottom: 30px;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #0b1a33;
        }
        
        /* Tabs */
        .dashboard-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e4e8;
            padding-bottom: 10px;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 10px 25px;
            background: #f0f0f0;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        .tab-btn.active {
            background: #0b1a33;
            color: white;
        }
        .tab-content {
            display: none;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .tab-content.active {
            display: block;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
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
        
        /* Tables */
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
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending { background: #ffc107; color: #856404; }
        .status-approved { background: #28a745; color: white; }
        .status-completed { background: #28a745; color: white; }
        .status-processing { background: #17a2b8; color: white; }
        
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
        
        .order-card {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px auto 40px;
                padding: 15px;
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
            .dashboard-tabs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Seller Dashboard</h1>
    <p class="welcome">Welcome back, <?php echo htmlspecialchars($seller_name); ?>!</p>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Products</h3>
            <div class="number"><?php echo $total_products; ?></div>
        </div>
        <div class="stat-card">
            <h3>Approved Products</h3>
            <div class="number"><?php echo $approved_products['count']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Pending Approval</h3>
            <div class="number"><?php echo $pending_products['count']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Orders Received</h3>
            <div class="number"><?php echo $stats['total_orders'] ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Items Sold</h3>
            <div class="number"><?php echo $stats['total_items_sold'] ?? 0; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Revenue</h3>
            <div class="number">R <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></div>
        </div>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Dashboard Tabs -->
    <div class="dashboard-tabs">
        <button class="tab-btn active" onclick="showTab('products')">📦 My Products</button>
        <button class="tab-btn" onclick="showTab('add-product')">➕ Add Product</button>
        <button class="tab-btn" onclick="showTab('orders')">📋 Orders</button>
        <button class="tab-btn" onclick="showTab('requests')">📝 Sell Requests</button>
    </div>
    
    <!-- Products Tab -->
    <div id="products" class="tab-content active">
        <h3 style="margin-bottom: 20px;">My Products</h3>
        <?php if ($products->num_rows == 0): ?>
            <p style="text-align: center; padding: 40px;">You haven't added any products yet. Click "Add Product" to get started.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($product = $products->fetch_assoc()): 
                        $status_text = ($product['is_approved'] == 1) ? 'Approved' : 'Pending Approval';
                        $status_class = ($product['is_approved'] == 1) ? 'status-approved' : 'status-pending';
                    ?>
                        <tr>
                            <td data-label="Image">
                                <?php if ($product['image'] && file_exists('images/'.$product['image'])): ?>
                                    <img src="images/<?php echo $product['image']; ?>" class="product-image">
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;">No img</div>
                                <?php endif; ?>
                            </td>
                            <td data-label="Name"><?php echo htmlspecialchars($product['name']); ?></td>
                            <td data-label="Price">R <?php echo number_format($product['price'], 2); ?></td>
                            <td data-label="Stock"><?php echo $product['quantity']; ?> left</td>
                            <td data-label="Status"><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            <td data-label="Actions">
                                <button class="btn btn-edit" onclick="editProduct(<?php echo $product['clothes_id']; ?>, '<?php echo addslashes($product['name']); ?>', '<?php echo addslashes($product['description']); ?>', <?php echo $product['price']; ?>, <?php echo $product['quantity']; ?>)">Edit</button>
                                <a href="?delete_product=<?php echo $product['clothes_id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this product permanently?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Add Product Tab -->
    <div id="add-product" class="tab-content">
        <h3 style="margin-bottom: 20px;">Add New Product</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" required placeholder="e.g., Vintage Denim Jacket">
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="5" required placeholder="Describe your product - condition, size, material, features..."></textarea>
            </div>
            <div class="form-group">
                <label>Price (R) *</label>
                <input type="number" name="price" step="0.01" required placeholder="0.00">
            </div>
            <div class="form-group">
                <label>Stock Quantity *</label>
                <input type="number" name="quantity" required placeholder="How many items do you have?">
            </div>
            <div class="form-group">
                <label>Product Image *</label>
                <input type="file" name="image" accept="image/*" required>
                <small style="color: #666;">Upload a clear image of your product (JPG, PNG, GIF)</small>
            </div>
            <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
        </form>
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
            <p style="color: #856404;">📌 Note: Products will be reviewed by admin before appearing in the store.</p>
        </div>
    </div>
    
    <!-- Orders Tab -->
    <div id="orders" class="tab-content">
        <h3 style="margin-bottom: 20px;">Orders for My Products</h3>
        <?php if ($orders->num_rows == 0): ?>
            <p style="text-align: center; padding: 40px;">No orders received yet. Start selling!</p>
        <?php else: ?>
            <?php while($order = $orders->fetch_assoc()): 
                // Get items for this order that belong to this seller
                $order_items = $conn->query("
                    SELECT oi.*, c.name 
                    FROM tblorderitems oi
                    JOIN tblclothes c ON oi.clothes_id = c.clothes_id
                    WHERE oi.order_id = {$order['order_id']} AND c.seller_id = $seller_id
                ");
                
                $status_class = '';
                switch($order['status']) {
                    case 'pending': $status_class = 'status-pending'; break;
                    case 'processing': $status_class = 'status-processing'; break;
                    case 'completed': $status_class = 'status-completed'; break;
                    default: $status_class = 'status-pending';
                }
            ?>
                <div class="order-card">
                    <div style="display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 15px;">
                        <div><strong>Order #:</strong> <?php echo $order['order_number']; ?></div>
                        <div><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                        <div><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                        <div><strong>Status:</strong> <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span></div>
                    </div>
                    
                    <table style="width: 100%; margin-bottom: 15px;">
                        <thead>
                            <tr><th>Item</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>
                        </thead>
                        <tbody>
                            <?php while($item = $order_items->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="Item"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td data-label="Quantity"><?php echo $item['quantity']; ?></td>
                                    <td data-label="Price">R <?php echo number_format($item['price'], 2); ?></td>
                                    <td data-label="Subtotal">R <?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                        <select name="status" style="padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                            <option value="pending" <?php echo $order['status']=='pending'?'selected':''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status']=='processing'?'selected':''; ?>>Processing</option>
                            <option value="completed" <?php echo $order['status']=='completed'?'selected':''; ?>>Completed</option>
                        </select>
                        <button type="submit" name="update_order_status" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    
    <!-- Sell Requests Tab -->
    <div id="requests" class="tab-content">
        <h3 style="margin-bottom: 20px;">Request to Sell Clothes</h3>
        <p>Want to sell an item that's not in your inventory? Submit a request to admin.</p>
        
        <a href="seller_request.php" class="btn btn-primary" style="display: inline-block; margin-bottom: 30px;">+ Submit New Sell Request</a>
        
        <h4>My Previous Requests</h4>
        <?php if ($seller_requests->num_rows == 0): ?>
            <p>No requests submitted yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($req = $seller_requests->fetch_assoc()): 
                        $req_status_class = ($req['status'] == 'approved') ? 'status-approved' : 'status-pending';
                    ?>
                        <tr>
                            <td data-label="Item"><?php echo htmlspecialchars($req['clothing_name']); ?></td>
                            <td data-label="Brand"><?php echo htmlspecialchars($req['brand']); ?></td>
                            <td data-label="Price">R <?php echo number_format($req['price'], 2); ?></td>
                            <td data-label="Status"><span class="status-badge <?php echo $req_status_class; ?>"><?php echo ucfirst($req['status']); ?></span></td>
                            <td data-label="Date"><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; max-width:500px; margin:100px auto; padding:30px; border-radius:15px;">
        <h3 style="margin-bottom: 20px;">Edit Product</h3>
        <form method="POST">
            <input type="hidden" name="product_id" id="edit_id">
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" id="edit_name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Price (R)</label>
                <input type="number" name="price" id="edit_price" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="quantity" id="edit_quantity" required>
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="edit_product" class="btn btn-primary">Save Changes</button>
                <button type="button" onclick="closeModal()" class="btn" style="background:#ccc;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function editProduct(id, name, description, price, quantity) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>

</body>
</html>