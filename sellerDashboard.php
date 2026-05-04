<?php
include 'includes/DBConn.php';
include 'includes/navbar.php';




// NOT LOGGED IN
if (!isset($_SESSION['username'])) {
    echo "Please login first.";
    exit();
}

// NOT A SELLER
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    echo "Access Denied";
    exit();
}

// NOT VERIFIED
if (!isset($_SESSION['status']) || $_SESSION['status'] != 'verified') {
    echo "Access Denied <br>Your seller account must be verified by admin.";
    exit();
}
?>

<style>
    body {
        background: #f4f6f9;
        font-family: Arial;
    }

    .dashboard-container {
        padding: 30px;
    }

    /* HEADER CARD */
    .header-card {
        background: linear-gradient(to right, #0b1a33, #1f2f4d);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .header-card h2 {
        margin: 0;
    }

    /* FORM CARD */
    .form-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        width: 400px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-card h3 {
        margin-bottom: 15px;
        color: #0b1a33;
    }

    input, textarea {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    textarea {
        height: 80px;
        resize: none;
    }

    button {
        width: 100%;
        padding: 12px;
        margin-top: 15px;
        background: #0b1a33;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 15px;
        cursor: pointer;
    }

    button:hover {
        background: #162a4d;
    }
</style>

<div class="dashboard-container">

    <!-- HEADER -->
    <div class="header-card">
        <h2>Welcome, <?php echo $_SESSION['username']; ?> 👋</h2>
        <p>Manage your listings and upload new items to sell on Pastimes.</p>
    </div>

    <!-- FORM -->
    <div class="form-card">
        <h3>Upload New Item</h3>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Item Name" required>

            <input type="number" name="price" placeholder="Price (R)" required>

            <textarea name="description" placeholder="Description"></textarea>

            <input type="file" name="image" required>

            <button type="submit">Upload Item</button>
        </form>
    </div>

</div>