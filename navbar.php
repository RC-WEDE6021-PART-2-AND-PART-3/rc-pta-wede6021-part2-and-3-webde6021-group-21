<?php session_start(); ?>

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
    border-bottom: 1px solid #ccc;
}

.nav-left a {
    margin-right: 20px;
    text-decoration: none;
    color: white;
    font-weight: bold;
}

.nav-right a {
    margin-left: 20px;
    text-decoration: none;
    color: black;
}

.nav-right {
    display: flex;
    align-items: center;
}
</style>

<nav>

    <!-- LEFT SIDE -->
    <div class="nav-left">
        <a href="index.php">Home</a>
        <a href="product.php">Product Listing</a>
        <a href="sellerDashboard.php">Seller</a>
        <a href="adminDashboard.php">Admin</a>
        <a href="messages.php">Messages</a>
    </div>

    <!-- RIGHT SIDE -->
    <div class="nav-right">

        <a href="cart.php">🛒</a>

        <?php if(isset($_SESSION['username'])) { ?>
            <span>Hi, <?php echo $_SESSION['username']; ?></span>
        <?php } else { ?>
            <a href="login.php">Sign In</a>
        <?php } ?>

    </div>

</nav>