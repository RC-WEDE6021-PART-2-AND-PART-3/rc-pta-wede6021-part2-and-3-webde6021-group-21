<?php
session_start();
include 'includes/DBConn.php';
include 'includes/navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please login first");
    exit();
}

// Check if user is a seller
if ($_SESSION['role'] != 'seller') {
    header("Location: index.php?message=Access denied. Seller only area.");
    exit();
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $clothing_name = $conn->real_escape_string($_POST['clothing_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $brand = $conn->real_escape_string($_POST['brand']);
    $price = floatval($_POST['price']);
    
    // Handle image upload
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image_name = time() . '_' . $filename;
            if (!file_exists('seller_images')) {
                mkdir('seller_images', 0777, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], 'seller_images/' . $image_name);
        } else {
            $error = "Only JPG, PNG, GIF images are allowed.";
        }
    } else {
        $error = "Please upload an image of the clothing.";
    }
    
    if (empty($error) && !empty($image_name)) {
        $conn->query("INSERT INTO tblsellerrequests (user_id, clothing_name, description, brand, price, image, status) 
                      VALUES ($user_id, '$clothing_name', '$description', '$brand', $price, '$image_name', 'pending')");
        
        if ($conn->affected_rows > 0) {
            $message = "✅ Your request has been submitted! Admin will review it soon.";
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request to Sell - Pastimes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 20px auto 40px;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0b1a33;
            margin-bottom: 10px;
            text-align: center;
            margin-top: 0;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        textarea {
            resize: vertical;
        }
        button {
            background: #0b1a33;
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background: #1f2f4d;
        }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-box {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
        }
        .info-box p {
            margin: 5px 0;
            color: #0b1a33;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            width: 100%;
            color: #0b1a33;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .container {
                margin: 20px 15px 40px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>📝 Request to Sell Clothes</h1>
    <p class="subtitle">Fill out the form below to submit a request to sell your items</p>
    
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>👕 Clothing Name *</label>
            <input type="text" name="clothing_name" required placeholder="e.g., Vintage Levi's Jeans">
        </div>
        
        <div class="form-group">
            <label>🏷️ Brand *</label>
            <input type="text" name="brand" required placeholder="e.g., Levi's, Nike, Adidas">
        </div>
        
        <div class="form-group">
            <label>💰 Price (R) *</label>
            <input type="number" name="price" step="0.01" required placeholder="0.00">
        </div>
        
        <div class="form-group">
            <label>📝 Description *</label>
            <textarea name="description" rows="4" required placeholder="Describe the item - condition, size, color, material, age, etc."></textarea>
        </div>
        
        <div class="form-group">
            <label>🖼️ Upload Image *</label>
            <input type="file" name="image" accept="image/*" required>
            <small style="color: #666;">Upload a clear image of your item (JPG, PNG, GIF)</small>
        </div>
        
        <button type="submit">Submit Request</button>
    </form>
    
    <div class="info-box">
        <p>📌 What happens next?</p>
        <p>1. Admin will review your request</p>
        <p>2. If approved, your item will be added to the store</p>
        <p>3. You will be notified when approved</p>
    </div>
    
    <a href="sellerDashboard.php" class="back-link">← Back to Seller Dashboard</a>
</div>

</body>
</html>