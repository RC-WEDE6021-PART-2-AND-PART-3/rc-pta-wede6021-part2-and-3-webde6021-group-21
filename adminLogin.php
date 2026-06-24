<?php
include 'includes/DBConn.php';



$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $query = "SELECT * FROM tblUser 
          WHERE (username='$username' OR email='$username') 
          AND role='admin'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        header("Location: adminDashboard.php");
        exit();
    } else {
        $message = "Invalid admin login details.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Pastimes</title>

    <style>
        body {
            margin: 40;
            font-family: Arial;
            background: #f4f4f4;
        }

        .container {
            display: flex;
            height: 60vh;
        }

        

        input {
            width: 50%;
            padding: 12px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 50%;
            padding: 12px;
            margin-top: 20px;
            background: #0b1a33;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        button:hover {
            background: #162a4d;
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

        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
</head>
<body>

<h2>Admin Login</h2>

<form method="POST">
    <input type="text" name="username" placeholder="Admin Username" required><br><br>

    <input type="password" name="password" placeholder="Password" required><br><br>

    <button type="submit">Login</button>
</form>

<p style="color:red;"><?php echo $message; ?></p>

</body>
</html>