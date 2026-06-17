<?php
session_start(); // Add this at the top
include 'includes/DBConn.php';
include 'includes/navbar.php';

// Initialize cart as associative array (id => quantity)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Format: [product_id => quantity]
}

// ADD ITEM TO CART
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++; // Increase quantity by 1
    } else {
        $_SESSION['cart'][$product_id] = 1; // Add new item with quantity 1
    }
    
    header("Location: cart.php");
    exit();
}

// REMOVE ITEM
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit();
}

// UPDATE CART QUANTITIES
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $new_quantity) {
        $new_quantity = (int)$new_quantity;
        
        if ($new_quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $new_quantity;
        }
    }
    header("Location: cart.php");
    exit();
}

// CHECKOUT
if (isset($_POST['checkout'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['checkout_redirect'] = true;
        header("Location: login.php?message=Please login to checkout");
        exit();
    }
    
    // Check if cart is empty
    if (empty($_SESSION['cart'])) {
        $error = "Your cart is empty!";
    } else {
        $user_id = $_SESSION['user_id'];
        $order_number = 'ORD-' . time() . '-' . $user_id;
        
        // Calculate total and get product details
        $subtotal = 0;
        $cart_items = [];
        
        foreach ($_SESSION['cart'] as $id => $qty) {
            $result = $conn->query("SELECT * FROM tblclothes WHERE clothes_id = $id");
            if ($result && $result->num_rows > 0) {
                $item = $result->fetch_assoc();
                $cart_items[] = [
                    'id' => $id,
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $qty,
                    'image' => $item['image']
                ];
                $subtotal += $item['price'] * $qty;
            }
        }
        
        // Check if we have items in cart
        if (empty($cart_items)) {
            $error = "No valid items in cart!";
        } else {
            $shipping = 50;
            $tax = $subtotal * 0.08;
            $grandTotal = $subtotal + $shipping + $tax;
            
            // Insert into orders table
            $conn->query("INSERT INTO tblorders (user_id, order_number, total_amount, status) 
                          VALUES ($user_id, '$order_number', $grandTotal, 'pending')");
            $order_id = $conn->insert_id;
            
            // Insert order items and update inventory
            foreach ($cart_items as $item) {
                // Insert order item
                $conn->query("INSERT INTO tblorderitems (order_id, clothes_id, quantity, price) 
                              VALUES ($order_id, {$item['id']}, {$item['quantity']}, {$item['price']})");
                
                // Update inventory
                $conn->query("UPDATE tblclothes SET quantity = quantity - {$item['quantity']} WHERE clothes_id = {$item['id']}");
            }
            
            // Clear cart
            $_SESSION['cart'] = [];
            $_SESSION['order_number'] = $order_number;
            
            header("Location: checkout_success.php");
            exit();
        }
    }
}

// Get all cart items with details
$cart_items = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $result = $conn->query("SELECT * FROM tblclothes WHERE clothes_id = $id");
        if ($result && $result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $cart_items[] = [
                'id' => $id,
                'name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $qty,
                'image' => $item['image'],
                'subtotal' => $item['price'] * $qty
            ];
            $subtotal += $item['price'] * $qty;
        }
    }
}

$total_items = array_sum($_SESSION['cart']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shopping Cart - Pastimes</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto 40px;  /* Changed from 100px to 20px */
            padding: 20px;
        }

        .top-link {
            margin-bottom: 20px;
        }

        .top-link a {
            text-decoration: none;
            color: #888;
            font-weight: 500;
        }
        .top-link a:hover {
            color: black;
        }

        .cart-layout {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .items {
            flex: 2;
            min-width: 250px;
        }

        .summary {
            flex: 1;
            min-width: 250px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            height: fit-content;
        }

        .item {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .item-left {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .item img {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
        }

        .item-details h4 {
            margin-bottom: 5px;
        }

        .item-price {
            font-size: 18px;
            font-weight: bold;
        }

        .remove {
            color: red;
            text-decoration: none;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
        }

        .update-btn {
            background: #ffc107;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
        }

        .update-btn:hover {
            background: #e0a800;
        }

        .summary h3 {
            margin-bottom: 20px;
        }

        .summary p {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .total {
            font-size: 22px;
            font-weight: bold;
        }

        .btn {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .checkout {
            background: black;
            color: white;
        }

        .checkout:hover {
            background: #333;
        }

        .continue {
            background: #eee;
        }

        .continue:hover {
            background: #ddd;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .empty-cart {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 10px;
        }

        hr {
            margin: 15px 0;
            border: none;
            border-top: 1px solid #e0e4e8;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px auto 40px;  /* Changed from 80px to 20px */
                padding: 15px;
            }
            .items, .summary {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="top-link">
        <a href="product.php">← Continue Shopping</a>
    </div>

    <h2>Shopping Cart (<?php echo $total_items; ?> items)</h2>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="cart-layout">

        <div class="items">
            <?php if (!empty($cart_items)): ?>
                <form method="POST">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="item">
                            <div class="item-left">
                                <img src="images/<?php echo $item['image']; ?>" onerror="this.src='https://via.placeholder.com/80'">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Price: R<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                            </div>

                            <div>
                                <input type="number" 
                                       name="quantity[<?php echo $item['id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="0" max="99" 
                                       class="quantity-input">
                                <p class="item-price">R<?php echo number_format($item['subtotal'], 2); ?></p>
                                <a class="remove" href="cart.php?remove=<?php echo $item['id']; ?>">Remove</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" name="update_cart" class="update-btn">⟳ Edit Cart</button>
                </form>
            <?php else: ?>
                <div class="empty-cart">
                    <h3>Your cart is empty</h3>
                    <p>Add some items to your cart to continue shopping.</p>
                    <a href="product.php" class="btn continue" style="width: auto; display: inline-block;">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($cart_items)): ?>
            <div class="summary">
                <h3>Order Summary</h3>

                <p><span>Subtotal</span> <span>R<?php echo number_format($subtotal, 2); ?></span></p>

                <?php
                $shipping = 50;
                $tax = $subtotal * 0.08;
                $grandTotal = $subtotal + $shipping + $tax;
                ?>

                <p><span>Shipping</span> <span>R<?php echo number_format($shipping, 2); ?></span></p>
                <p><span>Tax (8%)</span> <span>R<?php echo number_format($tax, 2); ?></span></p>

                <hr>

                <p class="total">
                    <span>Total</span>
                    <span>R<?php echo number_format($grandTotal, 2); ?></span>
                </p>

                <form method="POST">
                    <button type="submit" name="checkout" class="btn checkout">Proceed to Checkout</button>
                </form>

                <button class="btn continue" onclick="window.location='product.php'">
                    Continue Shopping
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>