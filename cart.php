<?php

include 'includes/DBConn.php';
include 'includes/navbar.php';

// ADD ITEM (NEW)
if (isset($_POST['product_id'])) {

    $product_id = $_POST['product_id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][] = $product_id;
}

//Ensure cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

//REMOVE ITEM
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];

    foreach ($_SESSION['cart'] as $key => $value) {
        if ($value == $remove_id) {
            unset($_SESSION['cart'][$key]);
        }
    }

    $_SESSION['cart'] = array_values($_SESSION['cart']);
}
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
        }

        .container {
            width: 90%;
            margin: auto;
            padding: 20px;
        }

        /* TOP LINK */
        .top-link {
            margin-bottom: 20px;
        }

        .top-link a {
           text-decoration: none;
           color: #888;   /* grey */
           font-weight: 500;
        }
        .top-link a:hover {
           color: black;
        }

        /* LAYOUT */
        .cart-layout {
            display: flex;
            gap: 20px;
        }

        .items {
            width: 65%;
        }

        .summary {
            width: 35%;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        /* ITEM CARD */
        .item {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .item-left {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .item img {
            width: 90px;
            height: 90px;
            border-radius: 10px;
        }

        .item-details {
            font-size: 14px;
        }

        .item-price {
            font-size: 20px;
            font-weight: bold;
        }

        .remove {
            color: red;
            text-decoration: none;
        }

        /* SUMMARY */
        .summary h3 {
            margin-bottom: 20px;
        }

        .summary p {
            display: flex;
            justify-content: space-between;
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

        .continue {
            background: #eee;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- CONTINUE SHOPPING -->
    <div class="top-link">
        <a href="product.php">← Continue Shopping</a>
    </div>

    <h2>Shopping Cart (<?php echo count($_SESSION['cart']); ?> items)</h2>

    <div class="cart-layout">

        <!-- LEFT SIDE ITEMS -->
        <div class="items">

        <?php
        $total = 0;

        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id) {

                $result = $conn->query("SELECT * FROM tblClothes WHERE clothes_id=$id");
                $item = $result->fetch_assoc();

                $total += $item['price'];
        ?>

            <div class="item">
                <div class="item-left">
                    <img src="images/<?php echo $item['image']; ?>">

                    <div class="item-details">
                        <h4><?php echo $item['name']; ?></h4>
                        <p>Sold by: Seller</p>
                    </div>
                </div>

                <div>
                    <p class="item-price">R<?php echo $item['price']; ?></p>
                    <a class="remove" href="cart.php?remove=<?php echo $id; ?>">Remove</a>
                </div>
            </div>

        <?php
            }
        } else {
            echo "Cart is empty.";
        }
        ?>

        </div>

        <!-- RIGHT SIDE SUMMARY -->
        <?php if (!empty($_SESSION['cart'])) { ?>
<div class="summary">

    <h3>Order Summary</h3>

    <p><span>Subtotal</span> <span>R<?php echo $total; ?></span></p>

    <?php
    $shipping = 50;
    $tax = $total * 0.08;
    $grandTotal = $total + $shipping + $tax;
    ?>

    <p><span>Shipping</span> <span>R<?php echo $shipping; ?></span></p>
    <p><span>Tax</span> <span>R<?php echo number_format($tax,2); ?></span></p>

    <hr>

    <p class="total">
        <span>Total</span>
        <span>R<?php echo number_format($grandTotal,2); ?></span>
    </p>

    <button class="btn checkout">Proceed to Checkout</button>

    <button class="btn continue" onclick="window.location='productListing.php'">
        Continue Shopping
    </button>

</div>
<?php } ?>