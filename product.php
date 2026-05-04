<?php 

include 'includes/DBConn.php';
include 'includes/navbar.php';

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';

// Build the query with search filters
$query = "SELECT * FROM tblclothes WHERE 1=1";

if (!empty($search)) {
    $search_escaped = $conn->real_escape_string($search);
    $query .= " AND (name LIKE '%$search_escaped%' OR description LIKE '%$search_escaped%')";
}

if (!empty($min_price)) {
    $min_price = floatval($min_price);
    $query .= " AND price >= $min_price";
}

if (!empty($max_price)) {
    $max_price = floatval($max_price);
    $query .= " AND price <= $max_price";
}

$query .= " ORDER BY clothes_id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Pastimes</title>
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

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 100px auto 40px auto;
            padding: 0 20px;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #0b1a33 0%, #142c4c 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            color: white;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .hero-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Search Section */
        .search-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .search-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .search-group {
            flex: 1;
            min-width: 200px;
        }

        .search-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #0b1a33;
            font-size: 0.9rem;
        }

        .search-group input, 
        .search-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e4e8;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
            outline: none;
        }

        .search-group input:focus,
        .search-group select:focus {
            border-color: #0b1a33;
            box-shadow: 0 0 0 3px rgba(11,26,51,0.1);
        }

        .search-btn {
            background: #0b1a33;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .search-btn:hover {
            background: #142c4c;
            transform: translateY(-2px);
        }

        .reset-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .reset-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Results Info */
        .results-info {
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 2px solid #e0e4e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .results-count {
            color: #0b1a33;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .sort-select {
            padding: 8px 15px;
            border: 2px solid #e0e4e8;
            border-radius: 8px;
            outline: none;
            cursor: pointer;
        }

        /* Products Grid */
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
            position: relative;
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
            font-size: 1.2rem;
            font-weight: 700;
            color: #0b1a33;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .card-description {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 15px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .price {
            font-size: 1.4rem;
            font-weight: 800;
            color: #0b1a33;
            margin-bottom: 15px;
        }

        .view-btn {
            display: inline-block;
            background: #0b1a33;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            text-align: center;
        }

        .view-btn:hover {
            background: #142c4c;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }

        .no-results h3 {
            font-size: 1.5rem;
            color: #0b1a33;
            margin-bottom: 10px;
        }

        .no-results p {
            color: #6c757d;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                margin-top: 80px;
                padding: 0 15px;
            }

            .hero-section {
                padding: 30px 20px;
            }

            .hero-section h1 {
                font-size: 1.8rem;
            }

            .search-form {
                flex-direction: column;
            }

            .search-group {
                width: 100%;
            }

            .products {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }

            .image-container {
                height: 200px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Hero Section -->
    <div class="hero-section">
        <h1>🛍️ Vintage Collection</h1>
        <p>Discover unique vintage pieces with character and history</p>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <form method="GET" action="" class="search-form">
            <div class="search-group" style="flex: 2;">
                <label>🔍 Search Products</label>
                <input type="text" name="search" placeholder="Search by name or description..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="search-group">
                <label>💰 Min Price (R)</label>
                <input type="number" name="min_price" placeholder="Min" step="10" value="<?php echo htmlspecialchars($min_price); ?>">
            </div>
            
            <div class="search-group">
                <label>💰 Max Price (R)</label>
                <input type="number" name="max_price" placeholder="Max" step="10" value="<?php echo htmlspecialchars($max_price); ?>">
            </div>
            
            <div class="search-group">
                <label>&nbsp;</label>
                <button type="submit" class="search-btn">Search</button>
            </div>
            
            <div class="search-group">
                <label>&nbsp;</label>
                <a href="product.php" class="reset-btn">Reset</a>
            </div>
        </form>
    </div>

    <!-- Results Info -->
    <div class="results-info">
        <div class="results-count">
            📦 <?php echo $result->num_rows; ?> product(s) found
            <?php if (!empty($search)): ?>
                for "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </div>
        <div>
            <select class="sort-select" id="sortSelect">
                <option value="">Sort by</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="name_asc">Name: A to Z</option>
                <option value="name_desc">Name: Z to A</option>
            </select>
        </div>
    </div>

    <!-- Products Grid -->
    <?php if ($result->num_rows > 0): ?>
        <div class="products" id="productsGrid">
            <?php while($row = $result->fetch_assoc()): 
                // IMPORTANT: This is the image display code
                $image_path = 'images/' . $row['image'];
                if (!empty($row['image']) && file_exists($image_path)) {
                    $image_src = $image_path;
                } else {
                    $image_src = 'https://via.placeholder.com/400x300?text=' . urlencode($row['name']);
                }
            ?>
                <a href="productDetails.php?id=<?php echo $row['clothes_id']; ?>" class="card">
                    <div class="image-container">
                        <img src="<?php echo $image_src; ?>" 
                             alt="<?php echo htmlspecialchars($row['name']); ?>"
                             onerror="this.src='https://via.placeholder.com/400x300?text=No+Image'">
                    </div>
                    <div class="card-content">
                        <div class="card-title"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="card-description">
                            <?php echo htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : ''); ?>
                        </div>
                        <div class="price">R <?php echo number_format($row['price'], 2); ?></div>
                        <div class="view-btn">View Details →</div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <h3>😔 No products found</h3>
            <p>Try adjusting your search or filter criteria</p>
            <a href="product.php" style="display: inline-block; margin-top: 20px; background: #0b1a33; color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none;">View All Products</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Sorting functionality
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            const productsGrid = document.getElementById('productsGrid');
            const cards = Array.from(productsGrid.children);
            
            cards.sort((a, b) => {
                const cardA = a;
                const cardB = b;
                
                if (sortValue === 'price_asc') {
                    const priceA = parseFloat(cardA.querySelector('.price')?.innerText.replace('R', '').replace(',', '') || 0);
                    const priceB = parseFloat(cardB.querySelector('.price')?.innerText.replace('R', '').replace(',', '') || 0);
                    return priceA - priceB;
                } else if (sortValue === 'price_desc') {
                    const priceA = parseFloat(cardA.querySelector('.price')?.innerText.replace('R', '').replace(',', '') || 0);
                    const priceB = parseFloat(cardB.querySelector('.price')?.innerText.replace('R', '').replace(',', '') || 0);
                    return priceB - priceA;
                } else if (sortValue === 'name_asc') {
                    const nameA = cardA.querySelector('.card-title')?.innerText.toLowerCase() || '';
                    const nameB = cardB.querySelector('.card-title')?.innerText.toLowerCase() || '';
                    return nameA.localeCompare(nameB);
                } else if (sortValue === 'name_desc') {
                    const nameA = cardA.querySelector('.card-title')?.innerText.toLowerCase() || '';
                    const nameB = cardB.querySelector('.card-title')?.innerText.toLowerCase() || '';
                    return nameB.localeCompare(nameA);
                }
                return 0;
            });
            
            cards.forEach(card => productsGrid.appendChild(card));
        });
    }
</script>

</body>
</html>