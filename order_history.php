<?php
session_start();
include 'includes/DBConn.php';
include 'includes/navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please login to view your orders");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all orders for this user
$orders = $conn->query("SELECT * FROM tblorders WHERE user_id = $user_id ORDER BY order_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Order History - Pastimes</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto 40px;
            padding: 20px;
        }
        h1 {
            color: #0b1a33;
            margin-bottom: 10px;
            margin-top: 0;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .order-header {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .order-header span {
            font-size: 14px;
        }
        .order-number {
            font-weight: bold;
            color: #0b1a33;
        }
        .order-date {
            color: #666;
        }
        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #ffc107; color: #856404; }
        .status-completed { background: #28a745; color: white; }
        .status-processing { background: #17a2b8; color: white; }
        .status-shipped { background: #6c757d; color: white; }
        .status-cancelled { background: #dc3545; color: white; }

        /* ===== ORDER TRACKING TIMELINE ===== */
        .tracking-section {
            margin: 15px 0 20px 0;
            padding: 20px;
            background: #fafafa;
            border-radius: 10px;
        }
        .tracking-title {
            font-weight: bold;
            color: #0b1a33;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
        .tracking-timeline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            padding: 10px 0;
        }
        .tracking-timeline::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 5%;
            right: 5%;
            height: 3px;
            background: #e0e4e8;
            z-index: 0;
            transform: translateY(-50%);
        }
        .tracking-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
            z-index: 1;
        }
        .tracking-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e0e4e8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            color: white;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        .tracking-circle.completed {
            background: #28a745;
        }
        .tracking-circle.active {
            background: #ffc107;
            animation: pulse 1.5s infinite;
        }
        .tracking-circle .icon {
            font-size: 16px;
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }
        .tracking-label {
            font-size: 0.7rem;
            text-align: center;
            color: #999;
            font-weight: 600;
            max-width: 70px;
        }
        .tracking-label.active-label {
            color: #0b1a33;
        }
        .tracking-label.completed-label {
            color: #28a745;
        }

        /* ===== TRACKING INFO ===== */
        .tracking-info {
            margin-top: 15px;
            padding: 12px 15px;
            background: #e8f4fd;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 0.9rem;
        }
        .tracking-info strong {
            color: #0b1a33;
        }

        /* ===== TABLE ===== */
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
        .order-total {
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #eee;
            font-weight: bold;
            font-size: 18px;
        }
        .no-orders {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 15px;
        }
        .no-orders h2 {
            color: #666;
            margin-bottom: 15px;
        }
        .grand-total {
            background: #0b1a33;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: right;
            font-size: 20px;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #0b1a33;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #1f2f4d;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        @media (max-width: 768px) {
            .container {
                margin: 20px auto 40px;
                padding: 15px;
            }
            .order-header {
                flex-direction: column;
            }
            .tracking-timeline {
                flex-wrap: wrap;
                gap: 10px;
            }
            .tracking-timeline::before {
                display: none;
            }
            .tracking-step {
                flex: 1;
                min-width: 60px;
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
                border: none;
            }
            td:before {
                content: attr(data-label);
                font-weight: bold;
                margin-right: 10px;
            }
            .tracking-info {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>📦 My Order History</h1>
    <p class="subtitle">View all your orders and track their status in real-time</p>
    
    <?php if ($orders && $orders->num_rows == 0): ?>
        <div class="no-orders">
            <h2>📦 No orders yet</h2>
            <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
            <a href="product.php" class="btn">Start Shopping</a>
        </div>
    <?php else: ?>
        <?php 
        $all_total = 0;
        while($order = $orders->fetch_assoc()): 
            $order_id = $order['order_id'];
            $items = $conn->query("SELECT oi.*, c.name, c.image FROM tblorderitems oi 
                                   JOIN tblclothes c ON oi.clothes_id = c.clothes_id 
                                   WHERE oi.order_id = $order_id");
            $all_total += $order['total_amount'];
            
            $status_class = '';
            $status_text = ucfirst($order['status']);
            switch($order['status']) {
                case 'pending': $status_class = 'status-pending'; break;
                case 'completed': $status_class = 'status-completed'; break;
                case 'processing': $status_class = 'status-processing'; break;
                case 'shipped': $status_class = 'status-shipped'; break;
                case 'cancelled': $status_class = 'status-cancelled'; break;
                default: $status_class = 'status-pending';
            }

            // ===== ORDER STATUS FOR TIMELINE =====
            $status_order = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'completed' => 3];
            $current_status = $status_order[$order['status']] ?? 0;
            
            // Define timeline steps
            $steps = [
                ['key' => 'pending', 'label' => 'Order Placed', 'icon' => '📝'],
                ['key' => 'processing', 'label' => 'Processing', 'icon' => '⚙️'],
                ['key' => 'shipped', 'label' => 'Shipped', 'icon' => '📦'],
                ['key' => 'completed', 'label' => 'Delivered', 'icon' => '✅']
            ];
        ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-number">📄 Order #: <?php echo $order['order_number']; ?></span>
                    <span class="order-date">📅 Date: <?php echo date('F j, Y', strtotime($order['order_date'])); ?></span>
                    <span>⏰ Time: <?php echo date('g:i A', strtotime($order['order_date'])); ?></span>
                    <span class="status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                </div>
                
                <!-- ===== ORDER TRACKING TIMELINE ===== -->
                <div class="tracking-section">
                    <div class="tracking-title">📌 Order Tracking</div>
                    <div class="tracking-timeline">
                        <?php foreach($steps as $index => $step): 
                            $step_status = $status_order[$step['key']];
                            $is_completed = $step_status <= $current_status;
                            $is_active = $step_status == $current_status;
                            
                            $circle_class = '';
                            $label_class = '';
                            if ($is_completed) {
                                $circle_class = 'completed';
                                $label_class = 'completed-label';
                            } elseif ($is_active) {
                                $circle_class = 'active';
                                $label_class = 'active-label';
                            }
                        ?>
                            <div class="tracking-step">
                                <div class="tracking-circle <?php echo $circle_class; ?>">
                                    <?php if ($is_completed && !$is_active): ?>
                                        <span class="icon">✓</span>
                                    <?php else: ?>
                                        <span class="icon"><?php echo $step['icon']; ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="tracking-label <?php echo $label_class; ?>"><?php echo $step['label']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tracking Info -->
                    <div class="tracking-info">
                        <span>
                            <strong>Status:</strong> <?php echo ucfirst($order['status']); ?>
                            <?php if ($order['status'] == 'shipped' || $order['status'] == 'completed'): ?>
                                ✅
                            <?php endif; ?>
                        </span>
                        <?php if (!empty($order['tracking_number'])): ?>
                            <span><strong>Tracking #:</strong> <?php echo $order['tracking_number']; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($order['estimated_delivery'])): ?>
                            <span><strong>Est. Delivery:</strong> <?php echo date('F j, Y', strtotime($order['estimated_delivery'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($order['delivery_date'])): ?>
                            <span><strong>Delivered:</strong> <?php echo date('F j, Y', strtotime($order['delivery_date'])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- ===== ORDER ITEMS TABLE ===== -->
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Item">
                                    <img src="images/<?php echo $item['image']; ?>" class="product-image" onerror="this.src='https://via.placeholder.com/50'">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </td>
                                <td data-label="Price">R <?php echo number_format($item['price'], 2); ?></td>
                                <td data-label="Quantity"><?php echo $item['quantity']; ?></td>
                                <td data-label="Subtotal">R <?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="order-total">
                    Order Total: R <?php echo number_format($order['total_amount'], 2); ?>
                </div>
            </div>
        <?php endwhile; ?>
        
        <div class="grand-total">
            💰 Total Spent: R <?php echo number_format($all_total, 2); ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; text-align: center;">
        <a href="product.php" class="btn btn-secondary">← Continue Shopping</a>
    </div>
</div>

</body>
</html>