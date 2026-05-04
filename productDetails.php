<?php
include 'includes/DBConn.php';
include 'includes/navbar.php';

// Get product ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no ID, show error
if ($id == 0) {
    echo "<div style='text-align: center; padding: 50px;'>";
    echo "<h1>Invalid Product</h1>";
    echo "<p>No product ID specified.</p>";
    echo "<a href='index.php' style='color: #0b1a33;'>Go Back Home</a>";
    echo "</div>";
    exit();
}

// Fetch product from database
$query = "SELECT * FROM tblclothes WHERE clothes_id = $id";
$result = $conn->query($query);

// If product not found
if ($result->num_rows == 0) {
    echo "<div style='text-align: center; padding: 50px;'>";
    echo "<h1>Product Not Found</h1>";
    echo "<p>The product you're looking for doesn't exist.</p>";
    echo "<a href='index.php' style='color: #0b1a33;'>Go Back Home</a>";
    echo "</div>";
    exit();
}

$product = $result->fetch_assoc();

// Get seller info
$seller_query = "SELECT name, username, user_id FROM tbluser WHERE user_id = " . $product['seller_id'];
$seller_result = $conn->query($seller_query);
$seller = $seller_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Pastimes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #f5f7fa;
        }

        .container {
            max-width: 1200px;
            margin: 100px auto 40px auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #0b1a33;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .product-detail {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            padding: 40px;
        }

        .product-image {
            width: 100%;
            height: 400px;
            overflow: hidden;
            border-radius: 15px;
            background: #f8f9fa;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info h1 {
            font-size: 2rem;
            color: #0b1a33;
            margin-bottom: 15px;
        }

        .price {
            font-size: 2rem;
            font-weight: 800;
            color: #0b1a33;
            margin-bottom: 20px;
        }

        .description {
            color: #495057;
            line-height: 1.6;
            margin-bottom: 30px;
            padding: 20px 0;
            border-top: 1px solid #e0e4e8;
            border-bottom: 1px solid #e0e4e8;
        }

        .description h3 {
            color: #0b1a33;
            margin-bottom: 10px;
        }

        .seller-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .seller-info h3 {
            color: #0b1a33;
            margin-bottom: 10px;
        }

        .seller-info p {
            margin-bottom: 5px;
            color: #495057;
        }

        .btn-group {
            display: flex;
            gap: 15px;
        }

        .btn-cart {
            background: #0b1a33;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
        }

        .btn-cart:hover {
            background: #142c4c;
        }

        .btn-message {
            background: transparent;
            color: #0b1a33;
            border: 2px solid #0b1a33;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            flex: 1;
        }

        .btn-message:hover {
            background: #0b1a33;
            color: white;
        }

        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                padding: 20px;
                gap: 20px;
            }

            .product-image {
                height: 300px;
            }

            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="product.php" class="back-link">← Back to Products</a>

    <div class="product-detail">
        <div class="product-image">
            <?php 
            $image_path = 'images/' . $product['image'];
            if (!empty($product['image']) && file_exists($image_path)) {
                $image_src = $image_path;
            } else {
                $image_src = 'https://via.placeholder.com/600x400?text=' . urlencode($product['name']);
            }
            ?>
            <img src="<?php echo $image_src; ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 onerror="this.src='https://via.placeholder.com/600x400?text=No+Image'">
        </div>

        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="price">R <?php echo number_format($product['price'], 2); ?></div>
            
            <div class="description">
                <h3>Description:</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <div class="seller-info">
                <h3>👤 Seller Information</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($seller['name']); ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($seller['username']); ?></p>
            </div>

            <div class="btn-group">
                <form method="POST" action="cart.php" style="flex: 1;">
                    <input type="hidden" name="product_id" value="<?php echo $product['clothes_id']; ?>">
                    <button type="submit" class="btn-cart">🛒 Add to Cart</button>
                </form>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="messages.php?user=<?php echo $product['seller_id']; ?>" class="btn-message">💬 Message Seller</a>
                <?php else: ?>
                    <a href="login.php" class="btn-message">🔑 Login to Message</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>