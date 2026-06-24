<?php
session_start();
include 'includes/DBConn.php';
include 'includes/navbar.php';

// ===== CHECK IF USER IS LOGGED IN =====
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'adminDashboard.php';
    header("Location: login.php?message=Please login to access the Admin Dashboard");
    exit();
}

// ===== CHECK IF USER IS AN ADMIN =====
if ($_SESSION['role'] != 'admin') {
    header("Location: index.php?message=Access denied. Admin access required.");
    exit();
}

// ===== APPROVE SELLER REQUEST =====
if (isset($_GET['approve_request'])) {
    $request_id = intval($_GET['approve_request']);
    
    $req_result = $conn->query("SELECT * FROM tblsellerrequests WHERE request_id = $request_id");
    if ($req_result && $req_result->num_rows > 0) {
        $request = $req_result->fetch_assoc();
        
        $conn->query("INSERT INTO tblclothes (name, description, price, image, seller_id, quantity, is_approved) 
                      VALUES ('{$request['clothing_name']}', '{$request['description']}', {$request['price']}, '{$request['image']}', {$request['user_id']}, 1, 1)");
        
        $conn->query("UPDATE tblsellerrequests SET status = 'approved' WHERE request_id = $request_id");
        
        $success = "Request approved! Product added to store.";
    }
    header("Location: adminDashboard.php");
    exit();
}

// ===== REJECT SELLER REQUEST =====
if (isset($_GET['reject_request'])) {
    $request_id = intval($_GET['reject_request']);
    
    $req_result = $conn->query("SELECT * FROM tblsellerrequests WHERE request_id = $request_id");
    if ($req_result && $req_result->num_rows > 0) {
        $conn->query("UPDATE tblsellerrequests SET status = 'rejected' WHERE request_id = $request_id");
        $success = "Request rejected.";
    }
    header("Location: adminDashboard.php");
    exit();
}

// VERIFY SELLER
if (isset($_GET['verify'])) {
    $id = intval($_GET['verify']);
    $conn->query("UPDATE tbluser SET status='verified', is_verified=1 WHERE user_id=$id");
    $success = "User verified successfully!";
    header("Location: adminDashboard.php");
    exit();
}

// DELETE USER
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    if ($id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } else {
        $conn->query("DELETE FROM tblmessage WHERE sender_id=$id OR receiver_id=$id");
        $conn->query("DELETE FROM tblorder WHERE user_id=$id");
        $conn->query("DELETE FROM tblorderitems WHERE order_id IN (SELECT order_id FROM tblorders WHERE user_id=$id)");
        $conn->query("DELETE FROM tblorders WHERE user_id=$id");
        $conn->query("DELETE FROM tblclothes WHERE seller_id=$id");
        $conn->query("DELETE FROM tblsellerrequests WHERE user_id=$id");
        $conn->query("DELETE FROM tbladminmessages WHERE sender_id=$id OR receiver_id=$id");
        $conn->query("DELETE FROM tbluser WHERE user_id=$id");
        $success = "User deleted successfully!";
    }
    header("Location: adminDashboard.php");
    exit();
}

// ADD USER
if (isset($_POST['addUser'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']);
    $role = $conn->real_escape_string($_POST['role']);
    
    $check = $conn->query("SELECT * FROM tbluser WHERE username='$username' OR email='$email'");
    if ($check->num_rows > 0) {
        $error = "Username or email already exists!";
    } else {
        $conn->query("INSERT INTO tbluser (name, email, username, password, role, status)
                      VALUES ('$name', '$email', '$username', '$password', '$role', 'verified')");
        $success = "User added successfully!";
    }
    header("Location: adminDashboard.php");
    exit();
}

// UPDATE USER
if (isset($_POST['updateUser'])) {
    $id = intval($_POST['user_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $role = $conn->real_escape_string($_POST['role']);
    
    $conn->query("UPDATE tbluser SET 
        name='$name',
        email='$email',
        role='$role'
        WHERE user_id=$id");
    $success = "User updated successfully!";
    header("Location: adminDashboard.php");
    exit();
}

// Get all users
$result = $conn->query("SELECT * FROM tbluser ORDER BY user_id ASC");
$admin_id = $_SESSION['user_id'];

// ===== GET SELLER REQUESTS =====
$seller_requests = $conn->query("SELECT * FROM tblsellerrequests WHERE status = 'pending' ORDER BY request_date DESC");
$approved_requests = $conn->query("SELECT * FROM tblsellerrequests WHERE status = 'approved' ORDER BY request_date DESC LIMIT 10");
$rejected_requests = $conn->query("SELECT * FROM tblsellerrequests WHERE status = 'rejected' ORDER BY request_date DESC LIMIT 10");

// Get pending seller count for badge
$pending_count = $conn->query("SELECT COUNT(*) as count FROM tblsellerrequests WHERE status='pending'")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Pastimes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1400px;
            margin: 20px auto 40px;
            padding: 20px;
        }
        h2 {
            color: #0b1a33;
            margin-bottom: 20px;
            font-size: 28px;
            margin-top: 0;
        }
        .stats {
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
        .form-box {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-box h3 {
            margin-bottom: 15px;
            color: #0b1a33;
        }
        .form-group {
            display: inline-block;
            margin: 5px;
        }
        input, select {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        button {
            background: #0b1a33;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #1f2f4d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #0b1a33;
            color: white;
        }
        .btn {
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 12px;
            display: inline-block;
            margin: 2px;
        }
        .btn-verify { background: #28a745; color: white; }
        .btn-edit { background: #ffc107; color: black; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-primary { background: #0b1a33; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
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
        
        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .tab-btn {
            padding: 10px 25px;
            background: #f0f0f0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .tab-btn.active {
            background: #0b1a33;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .request-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-pending { background: #ffc107; color: #856404; }
        .badge-approved { background: #28a745; color: white; }
        .badge-rejected { background: #dc3545; color: white; }
        
        @media (max-width: 768px) {
            .container { 
                margin: 20px auto 40px;
                padding: 15px;
            }
            table, thead, tbody, th, td, tr { display: block; }
            th { display: none; }
            tr { margin-bottom: 15px; border: 1px solid #eee; padding: 10px; }
            td { display: flex; justify-content: space-between; padding: 8px; }
            td:before { content: attr(data-label); font-weight: bold; margin-right: 10px; }
            .form-group { display: block; width: 100%; }
            input, select { width: 90%; }
            .tab-buttons { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Admin Dashboard</h2>
    
    <?php if (isset($success)): ?>
        <div class="success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="stats">
        <?php
        $total_users = $conn->query("SELECT COUNT(*) as count FROM tbluser")->fetch_assoc();
        $total_sellers = $conn->query("SELECT COUNT(*) as count FROM tbluser WHERE role='seller'")->fetch_assoc();
        $pending_sellers = $conn->query("SELECT COUNT(*) as count FROM tbluser WHERE role='seller' AND status='pending'")->fetch_assoc();
        $total_products = $conn->query("SELECT COUNT(*) as count FROM tblclothes")->fetch_assoc();
        $total_orders = $conn->query("SELECT COUNT(*) as count FROM tblorders")->fetch_assoc();
        $pending_requests = $conn->query("SELECT COUNT(*) as count FROM tblsellerrequests WHERE status='pending'")->fetch_assoc();
        ?>
        <div class="stat-card">
            <div class="number"><?php echo $total_users['count']; ?></div>
            <div class="label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_sellers['count']; ?></div>
            <div class="label">Sellers</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $pending_sellers['count']; ?></div>
            <div class="label">Pending Verification</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_products['count']; ?></div>
            <div class="label">Products</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_orders['count']; ?></div>
            <div class="label">Orders</div>
        </div>
        <div class="stat-card">
            <div class="number" style="color: #ffc107;"><?php echo $pending_requests['count']; ?></div>
            <div class="label">Pending Seller Requests</div>
        </div>
    </div>
    
    <!-- Tab Buttons -->
    <div class="tab-buttons">
        <button class="tab-btn active" onclick="showTab('users')">👥 Users</button>
        <button class="tab-btn" onclick="showTab('requests')">📝 Seller Requests <?php if($pending_requests['count'] > 0): ?><span class="badge badge-pending"><?php echo $pending_requests['count']; ?></span><?php endif; ?></button>
    </div>
    
    <!-- ============================================ -->
    <!-- ===== USERS TAB ===== -->
    <!-- ============================================ -->
    <div id="users" class="tab-content active">
        <!-- ADD USER FORM -->
        <div class="form-box">
            <h3>➕ Add New User</h3>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Full Name" required>
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <select name="role">
                        <option value="buyer">Buyer</option>
                        <option value="seller">Seller</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button name="addUser">Add User</button>
            </form>
        </div>
        
        <!-- USER TABLE -->
        <h3>👥 User Management</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <form method="POST">
                        <tr>
                            <td data-label="ID"><?php echo $row['user_id']; ?>
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                            </td>
                            <td data-label="Name">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" style="width: 120px;">
                            </td>
                            <td data-label="Email">
                                <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" style="width: 150px;">
                            </td>
                            <td data-label="Username"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td data-label="Role">
                                <select name="role">
                                    <option value="buyer" <?php echo $row['role']=='buyer'?'selected':''; ?>>Buyer</option>
                                    <option value="seller" <?php echo $row['role']=='seller'?'selected':''; ?>>Seller</option>
                                    <option value="admin" <?php echo $row['role']=='admin'?'selected':''; ?>>Admin</option>
                                </select>
                            </td>
                            <td data-label="Status">
                                <?php if ($row['status'] == 'verified'): ?>
                                    <span style="color: green;">✓ Verified</span>
                                <?php else: ?>
                                    <span style="color: orange;">⏳ Pending</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Actions">
                                <button type="submit" name="updateUser" class="btn btn-edit">Update</button>
                                
                                <?php if ($row['status'] != 'verified' && $row['role'] == 'seller'): ?>
                                    <a href="?verify=<?php echo $row['user_id']; ?>" class="btn btn-verify">Verify</a>
                                <?php endif; ?>
                                
                                <?php if ($row['user_id'] != $admin_id): ?>
                                    <a href="?delete=<?php echo $row['user_id']; ?>" 
                                       class="btn btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete <?php echo $row['name']; ?>?')">
                                       Delete
                                    </a>
                                <?php else: ?>
                                    <span style="color: #999;">(You)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </form>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- ============================================ -->
    <!-- ===== SELLER REQUESTS TAB ===== -->
    <!-- ============================================ -->
    <div id="requests" class="tab-content">
        <h3>📝 Seller Requests</h3>
        <p style="margin-bottom: 20px;">Review and approve/reject seller product requests.</p>
        
        <?php if ($seller_requests->num_rows == 0): ?>
            <div style="text-align: center; padding: 40px; background: white; border-radius: 15px;">
                <p>No pending seller requests.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Seller ID</th>
                        <th>Clothing Name</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($req = $seller_requests->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo $req['request_id']; ?></td>
                            <td data-label="Seller ID"><?php echo $req['user_id']; ?></td>
                            <td data-label="Clothing Name"><?php echo htmlspecialchars($req['clothing_name']); ?></td>
                            <td data-label="Brand"><?php echo htmlspecialchars($req['brand']); ?></td>
                            <td data-label="Price">R <?php echo number_format($req['price'], 2); ?></td>
                            <td data-label="Description"><?php echo htmlspecialchars(substr($req['description'], 0, 50)); ?>...</td>
                            <td data-label="Image">
                                <?php if ($req['image'] && file_exists('seller_images/'.$req['image'])): ?>
                                    <img src="seller_images/<?php echo $req['image']; ?>" class="request-image">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td data-label="Date"><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                            <td data-label="Actions">
                                <a href="?approve_request=<?php echo $req['request_id']; ?>" 
                                   class="btn btn-success" 
                                   onclick="return confirm('Approve this request? The product will be added to the store.')">
                                   ✅ Approve
                                </a>
                                <br>
                                <a href="?reject_request=<?php echo $req['request_id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Reject this request?')">
                                   ❌ Reject
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <!-- ===== APPROVED REQUESTS ===== -->
        <?php if ($approved_requests->num_rows > 0): ?>
            <h4 style="margin-top: 30px;">✅ Approved Requests</h4>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Brand</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($req = $approved_requests->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['clothing_name']); ?></td>
                            <td><?php echo htmlspecialchars($req['brand']); ?></td>
                            <td><span class="badge badge-approved">Approved</span></td>
                            <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <!-- ===== REJECTED REQUESTS ===== -->
        <?php if ($rejected_requests->num_rows > 0): ?>
            <h4 style="margin-top: 30px;">❌ Rejected Requests</h4>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Brand</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($req = $rejected_requests->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($req['clothing_name']); ?></td>
                            <td><?php echo htmlspecialchars($req['brand']); ?></td>
                            <td><span class="badge badge-rejected">Rejected</span></td>
                            <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Quick Links -->
    <div style="margin-top: 30px; padding: 20px; background: white; border-radius: 15px;">
        <h3>🔗 Quick Links</h3>
        <a href="admin.php" class="btn btn-primary" style="margin-right: 10px;">📦 Manage Products</a>
        <a href="admin_messages.php" class="btn btn-primary" style="margin-right: 10px;">💬 Messages</a>
        <a href="product.php" class="btn btn-primary">🛍️ View Store</a>
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
</script>

</body>
</html>