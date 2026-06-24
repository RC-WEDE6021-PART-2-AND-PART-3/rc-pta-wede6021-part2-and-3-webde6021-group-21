<?php

include 'includes/DBConn.php';
include 'includes/navbar.php';

$order_number = isset($_SESSION['order_number']) ? $_SESSION['order_number'] : '';
$session_id = isset($_SESSION['session_id']) ? $_SESSION['session_id'] : session_id();

// Clear the order number from session after displaying
unset($_SESSION['order_number']);
unset($_SESSION['session_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation - Pastimes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
        }
        .container {
            max-width: 600px;
            margin: 150px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .order-number {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 18px;
        }
        .reference {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 14px;
            color: #0b1a33;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn-primary {
            background: #0b1a33;
            color: white;
        }
        .btn-primary:hover {
            background: #1f2f4d;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="success-icon">✓</div>
    <h1>Order Successful!</h1>
    <p>Thank you for your purchase.</p>
    
    <div class="order-number">
        <strong>Order Number:</strong> <?php echo $order_number; ?>
    </div>
    
    <div class="reference">
        <strong>Reference Number:</strong> <?php echo $order_number . '-' . substr($session_id, 0, 8); ?>
    </div>
    
    <p>A confirmation has been sent to your email.</p>
    
    <a href="product.php" class="btn btn-primary">Continue Shopping</a>
    <a href="order_history.php" class="btn btn-primary">View Order History</a>
</div>
</body>
</html>