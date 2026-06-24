<?php
session_start();
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

// ============ HANDLE REVIEW SUBMISSION ============
if (isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $comment = $conn->real_escape_string($_POST['comment']);
    
    // Check if user already reviewed this product
    $check = $conn->query("SELECT * FROM tblreviews WHERE user_id = $user_id AND clothes_id = $id");
    if ($check->num_rows > 0) {
        $review_error = "You have already reviewed this product!";
    } else {
        $conn->query("INSERT INTO tblreviews (user_id, clothes_id, rating, comment) 
                      VALUES ($user_id, $id, $rating, '$comment')");
        $review_success = "Review submitted successfully!";
    }
}

// ============ GET AVERAGE RATING ============
$avg_result = $conn->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM tblreviews WHERE clothes_id = $id");
$rating_data = $avg_result->fetch_assoc();
$avg_rating = round($rating_data['avg_rating'], 1);
$total_reviews = $rating_data['total_reviews'];

// ============ GET ALL REVIEWS ============
$reviews = $conn->query("SELECT r.*, u.name FROM tblreviews r 
                         JOIN tbluser u ON r.user_id = u.user_id 
                         WHERE r.clothes_id = $id 
                         ORDER BY r.review_date DESC");

// ============ GET WISHLIST STATUS ============
$in_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $wish_check = $conn->query("SELECT * FROM tblwishlist WHERE user_id = {$_SESSION['user_id']} AND clothes_id = $id");
    $in_wishlist = ($wish_check->num_rows > 0);
}

// ============ HANDLE WISHLIST TOGGLE ============
if (isset($_GET['wishlist']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    if ($in_wishlist) {
        $conn->query("DELETE FROM tblwishlist WHERE user_id = $user_id AND clothes_id = $id");
    } else {
        $conn->query("INSERT INTO tblwishlist (user_id, clothes_id) VALUES ($user_id, $id)");
    }
    header("Location: productDetails.php?id=$id");
    exit();
}
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
            margin: 20px auto 40px auto;
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
            margin-bottom: 10px;
        }

        /* ===== RATING DISPLAY ===== */
        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .stars {
            font-size: 1.2rem;
        }

        .stars .empty {
            color: #ddd;
        }

        .rating-count {
            color: #666;
            font-size: 0.9rem;
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
            flex-wrap: wrap;
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

        .btn-wishlist {
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid #dc3545;
            background: transparent;
            color: #dc3545;
            flex: 0.5;
            transition: all 0.3s;
        }

        .btn-wishlist.active {
            background: #dc3545;
            color: white;
        }

        .btn-wishlist:hover {
            background: #dc3545;
            color: white;
        }

        /* ===== REVIEWS SECTION ===== */
        .reviews-section {
            margin-top: 40px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .reviews-section h2 {
            color: #0b1a33;
            margin-bottom: 20px;
        }

        .review-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .review-form select {
            padding: 8px 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-right: 10px;
        }

        .review-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
            resize: vertical;
            font-family: inherit;
        }

        .review-form button {
            background: #0b1a33;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
        }

        .review-form button:hover {
            background: #1f2f4d;
        }

        .review-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 5px;
        }

        .review-name {
            font-weight: bold;
            color: #0b1a33;
        }

        .review-rating {
            color: #ffc107;
        }

        .review-date {
            color: #999;
            font-size: 0.8rem;
        }

        .review-comment {
            color: #495057;
            line-height: 1.5;
            margin-top: 5px;
        }

        .no-reviews {
            text-align: center;
            color: #999;
            padding: 30px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .login-prompt {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px auto 40px;
            }
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
            .btn-wishlist {
                flex: 1;
            }
            .review-header {
                flex-direction: column;
                align-items: flex-start;
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
            
            <!-- ===== PRODUCT RATING DISPLAY ===== -->
            <div class="product-rating">
                <div class="stars">
                    <?php 
                    $full_stars = floor($avg_rating);
                    for($i = 1; $i <= 5; $i++): 
                        if($i <= $full_stars): ?>
                            <span>⭐</span>
                        <?php else: ?>
                            <span class="empty">☆</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <span class="rating-count">
                        <?php echo $avg_rating > 0 ? $avg_rating : 'No'; ?> (<?php echo $total_reviews; ?> reviews)
                    </span>
                </div>
            </div>

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
                
                <!-- ===== WISHLIST BUTTON ===== -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="?wishlist=1&id=<?php echo $id; ?>" 
                       class="btn-wishlist <?php echo $in_wishlist ? 'active' : ''; ?>">
                        <?php echo $in_wishlist ? '❤️ In Wishlist' : '🤍 Add to Wishlist'; ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-wishlist">🤍 Login to Wishlist</a>
                <?php endif; ?>
            </div>

            <div style="margin-top: 15px; display: flex; gap: 15px;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="messages.php?user=<?php echo $product['seller_id']; ?>" class="btn-message" style="flex: 1;">💬 Message Seller</a>
                <?php else: ?>
                    <a href="login.php" class="btn-message" style="flex: 1;">🔑 Login to Message</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ========== REVIEWS SECTION ============== -->
    <!-- ========================================== -->
    <div class="reviews-section">
        <h2>⭐ Reviews & Ratings</h2>

        <?php if (isset($review_success)): ?>
            <div class="success">✅ <?php echo $review_success; ?></div>
        <?php endif; ?>

        <?php if (isset($review_error)): ?>
            <div class="error">❌ <?php echo $review_error; ?></div>
        <?php endif; ?>

        <!-- Review Form -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="review-form">
                <h3>Write a Review</h3>
                <form method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                    <label>Your Rating:</label>
                    <select name="rating" required>
                        <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                        <option value="4">⭐⭐⭐⭐ Good</option>
                        <option value="3">⭐⭐⭐ Average</option>
                        <option value="2">⭐⭐ Poor</option>
                        <option value="1">⭐ Terrible</option>
                    </select>
                    <textarea name="comment" placeholder="Share your experience with this product..." rows="3"></textarea>
                    <button type="submit" name="submit_review">Submit Review</button>
                </form>
            </div>
        <?php else: ?>
            <div class="login-prompt">
                <a href="login.php" style="color: #0b1a33; font-weight: bold;">Login</a> to leave a review.
            </div>
        <?php endif; ?>

        <!-- Display Reviews -->
        <?php if ($reviews->num_rows > 0): ?>
            <?php while($review = $reviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-header">
                        <span class="review-name"><?php echo htmlspecialchars($review['name']); ?></span>
                        <span class="review-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php if($i <= $review['rating']): ?>⭐<?php else: ?>☆<?php endif; ?>
                            <?php endfor; ?>
                        </span>
                        <span class="review-date"><?php echo date('M d, Y', strtotime($review['review_date'])); ?></span>
                    </div>
                    <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-reviews">
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>