<?php
session_start();

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    $_SESSION = array();
    session_destroy();
    header("Location: index.php");
    exit();
}

include 'includes/DBConn.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pastimes - Home</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f7fa;
        }

        /* NAVBAR */
        .navbar {
            background-color: #0b1a33;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav-left a {
            color: white;
            margin-right: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: opacity 0.3s;
        }

        .nav-left a:hover {
            opacity: 0.8;
        }

        .nav-right a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .nav-right a:hover {
            opacity: 0.8;
        }

        /* CONTENT */
        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 100px 20px 40px 20px;
        }

        /* HERO SECTION */
        .hero {
            background: linear-gradient(135deg, #0b1a33 0%, #1f2f4d 100%);
            color: white;
            padding: 60px 50px;
            margin-top: 20px;
            border-radius: 20px;
            text-align: center;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* SEARCH BAR */
        .search-section {
            margin: 40px 0;
        }

        .search-bar {
            text-align: center;
            margin-bottom: 20px;
        }

        .search-wrapper {
            display: flex;
            justify-content: center;
            gap: 10px;
            max-width: 700px;
            margin: 0 auto;
        }

        .search-bar input {
            flex: 1;
            background: white;
            padding: 15px 20px;
            border-radius: 50px;
            border: 2px solid #e0e4e8;
            outline: none;
            font-size: 16px;
            transition: all 0.3s;
        }

        .search-bar input:focus {
            border-color: #0b1a33;
            box-shadow: 0 0 0 3px rgba(11,26,51,0.1);
        }

        .search-btn {
            background: #0b1a33;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .search-btn:hover {
            background: #1f2f4d;
            transform: translateY(-2px);
        }

        /* BUTTONS */
        .hero-buttons {
            margin-top: 30px;
        }

        .btn-primary {
            background-color: white;
            color: #0b1a33;
            border: none;
            padding: 12px 30px;
            margin-right: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background-color: transparent;
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background-color: white;
            color: #0b1a33;
        }

        /* Featured Items Section */
        .featured-section {
            margin-top: 60px;
        }

        .featured-section h2 {
            font-size: 2rem;
            color: #0b1a33;
            margin-bottom: 30px;
            text-align: center;
        }

        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .image-container {
            width: 100%;
            height: 250px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .card:hover .image-container img {
            transform: scale(1.1);
        }

        .card-content {
            padding: 20px;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #0b1a33;
            margin-bottom: 10px;
        }

        .price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #0b1a33;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 15px;
            grid-column: 1 / -1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                padding: 15px 20px;
            }

            .nav-left a, .nav-right a {
                margin: 0 8px;
                font-size: 12px;
            }

            .container {
                padding: 80px 15px 30px 15px;
            }

            .hero {
                padding: 40px 20px;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .search-wrapper {
                flex-direction: column;
                padding: 0 20px;
            }

            .products {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="nav-left">
        <a href="index.php">Home</a>
        <a href="product.php">Product Listing</a>
        <a href="sellerDashboard.php">Seller</a>
        <a href="adminLogin.php">Admin</a>
        <a href="messages.php">Messages</a>
    </div>
    <div class="nav-right">
        <?php if (isset($_SESSION['username'])): ?>
            <a href="?logout=1">Welcome, <?php echo $_SESSION['username']; ?> (Logout)</a>
        <?php else: ?>
            <a href="login.php">Sign In</a>
        <?php endif; ?>
        <a href="cart.php">🛒 Cart</a>
    </div>
</div>

<!-- CONTENT -->
<div class="container">
    <div class="hero">
        <h1>Discover Vintage Fashion</h1>
        <p>Second-hand branded clothing with quality you can trust. Curated by our community, verified by our team.</p>
        <div class="hero-buttons">
            <button class="btn-primary" onclick="window.location.href='product.php'">Browse Collection</button>
            <button class="btn-secondary" onclick="window.location.href='sellerDashboard.php'">Start Selling</button>
        </div>
    </div>

    <!-- SEARCH BAR -->
    <div class="search-section">
        <div class="search-bar">
            <div class="search-wrapper">
                <input type="text" id="searchInput" placeholder="Search for vintage clothing..." onkeypress="handleSearchKeyPress(event)">
                <button class="search-btn" onclick="performSearch()">🔍 Search</button>
            </div>
        </div>
    </div>

    <!-- Featured Items Section -->
    <div class="featured-section">
        <h2>✨ Featured Items</h2>
        <div class="products">
            <?php
            // FETCH 5 FEATURED PRODUCTS FROM DATABASE
            $featured_query = "SELECT * FROM tblclothes ORDER BY clothes_id DESC LIMIT 5";
            $featured_result = $conn->query($featured_query);
            
            if ($featured_result && $featured_result->num_rows > 0):
                while($product = $featured_result->fetch_assoc()):
                    // Handle image path
                    $image_path = 'images/' . $product['image'];
                    if (!empty($product['image']) && file_exists($image_path)) {
                        $image_src = $image_path;
                    } else {
                        $image_src = 'https://via.placeholder.com/400x300?text=' . urlencode($product['name']);
                    }
            ?>
                <a href="productDetails.php?id=<?php echo $product['clothes_id']; ?>" class="card">
                    <div class="image-container">
                        <img src="<?php echo $image_src; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                    </div>
                    <div class="card-content">
                        <div class="card-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="price">R <?php echo number_format($product['price'], 2); ?></div>
                    </div>
                </a>
            <?php 
                endwhile;
            else:
            ?>
                <div class="no-results">No products found in database. Please add some products first.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Search function - redirects to product.php with search query
    function performSearch() {
        const searchTerm = document.getElementById('searchInput').value.trim();
        if (searchTerm) {
            window.location.href = `product.php?search=${encodeURIComponent(searchTerm)}`;
        }
    }

    // Handle Enter key press in search input
    function handleSearchKeyPress(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    }
</script>

</body>
</html>