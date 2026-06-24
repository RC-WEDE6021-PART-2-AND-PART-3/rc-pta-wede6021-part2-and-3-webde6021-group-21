<?php
include 'includes/DBConn.php';

// DISABLE FK CHECKS
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// DROP TABLE
$conn->query("DROP TABLE IF EXISTS tblUser");

// CREATE TABLE
$sql = "CREATE TABLE tblUser (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    username VARCHAR(50),
    password VARCHAR(255),
    role VARCHAR(20),
    status VARCHAR(20)
)";
$conn->query($sql);

// LOAD DATA
$file = fopen("userData.txt", "r");

while (($line = fgetcsv($file)) !== FALSE) {

    $name = $line[0];
    $email = $line[1];
    $username = $line[2];
    $password = $line[3];
    $role = $line[4];
    $status = $line[5];

    $conn->query("INSERT INTO tblUser (name, email, username, password, role, status)
                  VALUES ('$name','$email','$username','$password','$role','$status')");
}

fclose($file);

// ENABLE FK CHECKS AGAIN
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Table recreated and data loaded successfully!";
?>