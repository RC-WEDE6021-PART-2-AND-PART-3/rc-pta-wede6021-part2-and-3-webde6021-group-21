<?php
session_start();

// Handle logout from navbar link
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<style>
    body {
        margin: 0;
        font-family: Arial;
    }

    nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 40px;
        background: #0b1a33;
        color: white;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        box-sizing: border-box;
    }

    .nav-left a {
        margin-right: 20px;
        text-decoration: none;
        color: white;
        font-weight: bold;
    }

    .nav-left a:hover {
        opacity: 0.8;
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .nav-right a {
        text-decoration: none;
        color: white;
        font-weight: bold;
    }

    .nav-right a:hover {
        opacity: 0.8;
    }

    .nav-right span {
        color: white;
    }

    .cart-icon {
        font-size: 18px;
    }
    
    .wishlist-icon {
        font-size: 18px;
    }

    .badge {
        background: #dc3545;
        color: white;
        border-radius: 50%;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: bold;
        margin-left: 2px;
    }
</style>

<nav>
    <div class="nav-left">
        <a href="index.php">Home</a>
        <a href="product.php">Product Listing</a>
        
        <!-- ===== SELLER DASHBOARD - Visible to everyone ===== -->
        <a href="sellerDashboard.php">Seller Dashboard</a>
        
        <!-- ===== ADMIN DASHBOARD - Visible to everyone ===== -->
        <a href="adminDashboard.php">Admin Dashboard</a>
        
        <a href="messages.php">Messages</a>
    </div>

    <div class="nav-right">
        <!-- WISHLIST LINK - Only for logged in users -->
        <?php if (isset($_SESSION['user_id'])): 
            include 'includes/DBConn.php';
            $user_id = $_SESSION['user_id'];
            $wish_count = $conn->query("SELECT COUNT(*) as count FROM tblwishlist WHERE user_id = $user_id")->fetch_assoc();
            $wishlist_count = $wish_count['count'] ?? 0;
        ?>
            <a href="wishlist.php" class="wishlist-icon">
                ❤️ Wishlist
                <?php if ($wishlist_count > 0): ?>
                    <span class="badge"><?php echo $wishlist_count; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>
        
        <a href="cart.php" class="cart-icon">🛒 Cart</a>

        <?php if(isset($_SESSION['username'])) { ?>
            <a href="?logout=1">Hi, <?php echo $_SESSION['username']; ?> (Logout)</a>
        <?php } else { ?>
            <a href="login.php">Sign In</a>
        <?php } ?>
    </div>
</nav>

<!-- Add margin to body content to prevent hiding behind fixed navbar -->
<div style="height: 70px;"></div>