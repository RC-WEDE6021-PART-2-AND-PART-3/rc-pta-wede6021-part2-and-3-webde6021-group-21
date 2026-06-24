<?php
include 'includes/DBConn.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $role = $_POST['role']; // ADD THIS LINE - Get the selected role
    
    // Check if terms are accepted
    $terms_accepted = isset($_POST['terms']) ? true : false;

    if (!$terms_accepted) {
        $message = "You must agree to the Terms of Service and Privacy Policy!";
    } 
    elseif ($password != $confirmPassword) {
        $message = "Passwords do not match!";
    } 
    elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters!";
    } 
    else {
        $hashedPassword = md5($password);

        // ADDED 'role' to the INSERT query
        $query = "INSERT INTO tbluser (name, email, username, password, role, status)
                  VALUES ('$name', '$email', '$username', '$hashedPassword', '$role', 'pending')";

        if ($conn->query($query) === TRUE) {
            $message = "Account created! Wait for admin verification.";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account - Pastimes</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f4f4f4;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* LEFT SIDE */
        .left {
            width: 50%;
            background: linear-gradient(135deg, #1e2a44, #2f3e5c);
            color: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .left h1 {
            font-size: 40px;
        }
        .left h3 {
           margin-top: 20px;
           margin-bottom: 5px;
        }

        .left p {
           margin-bottom: 10px;
           line-height: 1.4;
        }

        /* RIGHT SIDE */
        .right {
            width: 50%;
            background: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        select {
            background: white;
            cursor: pointer;
        }

        /* Checkbox inline with text */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin: 0;
            padding: 0;
            cursor: pointer;
        }

        .checkbox-group label {
            cursor: pointer;
            color: #333;
            font-size: 14px;
            margin: 0;
        }

        .checkbox-group label a {
            color: #0b1a33;
            text-decoration: none;
            font-weight: bold;
        }

        .checkbox-group label a:hover {
            text-decoration: underline;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #0b1a33;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-btn:hover {
            background: #162a4d;
        }

        .link {
            margin-top: 20px;
            text-align: center;
        }

        .error {
            color: red;
            margin-top: 10px;
        }
        
        .success {
            color: green;
            margin-top: 10px;
        }

        /* Role info box */
        .role-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- LEFT -->
    <div class="left">
        <h1>Join PASTIMES</h1>
        <p>Create your account and start buying or selling vintage branded clothing today</p>
        <br>
        <h3>Secure Account</h3>
        <p>Your data is protected with industry-standard encryption</p>
        <h3>Verified Community</h3>
        <p>All sellers are verified by our admin team</p>
        <h3>Easy Communication</h3>
        <p>Built-in messaging system to connect with buyers and sellers</p>
    </div>

    <!-- RIGHT -->
    <div class="right">
        <h2>Register</h2>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password (min 8 characters)" required>
            <input type="password" name="confirmPassword" placeholder="Confirm Password" required>

            <!-- ADDED: Role Selection -->
            <select name="role" required>
                <option value="">Select Account Type</option>
                <option value="buyer">Buyer - I want to purchase items</option>
                <option value="seller">Seller - I want to sell items</option>
            </select>
            <div class="role-info">
                💡 Sellers need admin verification before they can start selling.
            </div>

            <!-- Checkbox before text on same line -->
            <div class="checkbox-group">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">
                    I agree to the <a href="terms.php">Terms of Service</a> and 
                    <a href="privacy.php">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="submit-btn">Create Account</button>
        </form>

        <div class="error"><?php echo $message; ?></div>

        <div class="link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

</div>

</body>
</html>