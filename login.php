<?php
session_start();
include 'includes/DBConn.php';

$message = "";
$username = "";

// Check if there's a message from redirect
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    // Check if remember me is checked
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // username OR email
    $query = "SELECT * FROM tbluser WHERE username='$username' OR email='$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['password'] == $password) {

            if ($user['status'] == "verified") {

                // SESSION
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['status'] = $user['status'];
                
                // Simple remember me using session lifetime
                if ($remember_me) {
                    ini_set('session.cookie_lifetime', 86400 * 30);
                    ini_set('session.gc_maxlifetime', 86400 * 30);
                    session_regenerate_id(true);
                }

                // ===== FIXED: Redirect logic =====
                // Check if user was trying to access a specific page
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                } else {
                    // Default redirect for normal login - go to User Dashboard
                    $redirect = "userDashboard.php";
                }
                header("Location: $redirect");
                exit();

            } else {
                $message = "Account pending verification. Please wait for admin approval.";
            }

        } else {
            $message = "Incorrect password. Please try again.";
        }

    } else {
        $message = "User not found. Please check your username or email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Pastimes</title>

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

        .left ul {
            margin-top: 20px;
        }

        .left li {
            margin-bottom: 10px;
        }

        .right {
            width: 50%;
            background: white;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right h2 {
            font-size: 28px;
            color: #0b1a33;
            margin-bottom: 25px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #0b1a33;
            box-shadow: 0 0 0 2px rgba(11,26,51,0.1);
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 20px;
            background: #0b1a33;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #162a4d;
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

        .link {
            margin-top: 20px;
            text-align: center;
        }

        .link a {
            color: #0b1a33;
            text-decoration: none;
            font-weight: bold;
        }

        .link a:hover {
            text-decoration: underline;
        }

        .error {
            color: #dc3545;
            margin-top: 10px;
            padding: 10px;
            background: #f8d7da;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
            }
            .left, .right {
                width: 100%;
                padding: 30px;
            }
            .left {
                padding: 40px 30px;
            }
            .left h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <!-- LEFT SIDE -->
    <div class="left">
        <h1>Welcome Back</h1>
        <p style="font-size: 18px; opacity: 0.9;">Sign in to continue shopping vintage branded clothing</p>

        <ul style="list-style: none; padding: 0; margin-top: 30px;">
            <li style="margin-bottom: 15px; font-size: 16px;">🛍️ Browse curated collections</li>
            <li style="margin-bottom: 15px; font-size: 16px;">💬 Message sellers directly</li>
            <li style="margin-bottom: 15px; font-size: 16px;">📦 Track your orders</li>
            <li style="margin-bottom: 15px; font-size: 16px;">❤️ Save your favorite items</li>
        </ul>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right">

        <h2>Sign In</h2>

        <?php if ($message): ?>
            <div class="error"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" 
                   placeholder="Enter username or email" 
                   value="<?php echo htmlspecialchars($username); ?>" required>

            <input type="password" name="password" placeholder="Enter your password" required>

            <!-- Checkbox before text on same line -->
            <div class="checkbox-group">
                <input type="checkbox" name="remember_me" id="remember_me">
                <label for="remember_me">Remember me for 30 days</label>
            </div>

            <button type="submit">Sign In</button>
        </form>

        <div class="link">
            Don't have an account? <a href="register.php">Create Account</a>
        </div>

    </div>

</div>

</body>
</html>