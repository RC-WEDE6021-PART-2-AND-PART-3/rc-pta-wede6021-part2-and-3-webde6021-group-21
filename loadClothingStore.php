<?php
include 'includes/DBConn.php';


// 🔴 Disable FK checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// 🔴 DROP TABLES (CORRECT ORDER + CORRECT NAMES)
$conn->query("DROP TABLE IF EXISTS tblmessage");
$conn->query("DROP TABLE IF EXISTS tblorder");
$conn->query("DROP TABLE IF EXISTS tblclothes");
$conn->query("DROP TABLE IF EXISTS tbluser");

// 🟢 CREATE tbluser
$conn->query("
CREATE TABLE tbluser (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    username VARCHAR(50),
    password VARCHAR(255),
    role VARCHAR(20),
    status VARCHAR(20)
)");

// 🟢 CREATE tblclothes
$conn->query("
CREATE TABLE tblclothes (
    clothes_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    price DECIMAL(10,2),
    image VARCHAR(100),
    seller_id INT,
    FOREIGN KEY (seller_id) REFERENCES tbluser(user_id)
)");

// 🟢 CREATE tblorder
$conn->query("
CREATE TABLE tblorder (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    clothes_id INT,
    FOREIGN KEY (user_id) REFERENCES tbluser(user_id),
    FOREIGN KEY (clothes_id) REFERENCES tblclothes(clothes_id)
)");

// 🟢 CREATE tblmessage (IMPORTANT: SAME NAME EVERYWHERE)
$conn->query("
CREATE TABLE tblmessage (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    FOREIGN KEY (sender_id) REFERENCES tbluser(user_id),
    FOREIGN KEY (receiver_id) REFERENCES tbluser(user_id)
)");

// 🟡 INSERT USERS
$conn->query("
INSERT INTO tbluser (name, email, username, password, role, status) VALUES
('Fhulu mudau', 'fhulufhelomm@gmail.com', 'fhulu', MD5('12345678'), 'buyer', 'verified'),
('Ravhura Sedzani', 'ravhurasedzani@gmail.com', 'Sedzi', MD5('12345678'), 'seller', 'verified'),
('Mulweli M', 'mulwelimm@gmail.com', 'Mulweli', MD5('12345678'), 'admin', 'verified')
");

// 🟡 INSERT PRODUCTS
$conn->query("
INSERT INTO tblclothes (name, description, price, image, seller_id) VALUES
('Vintage Jacket', 'Good condition jacket', 250.00, 'jacket.jpg', 2),
('Nike Hoodie', 'Original hoodie', 300.00, 'hoodie.jpg', 2),
('Blue Jeans', 'Stylish denim jeans', 200.00, 'jeans.jpg', 2),
('Summer Dress', 'Light and comfortable', 180.00, 'dress.jpg', 2),
('Leather Boots', 'Durable boots', 400.00, 'boots.jpg', 2),
('White T-Shirt','Simple cotton shirt',120.00,'tshirt.jpg',2),
('Black Skirt','Trendy black skirt',150.00,'skirt.jpg',2),
('Denim Shorts','Comfortable shorts',130.00,'shorts.jpg',2),
('Formal Shirt','Office wear shirt',220.00,'formalshirt.jpg',2),
('Hooded Jacket','Warm winter jacket',350.00,'hoodedjacket.jpg',2)
");

// 🟡 INSERT MESSAGES (IMPORTANT FOR FK TESTING)
$conn->query("
INSERT INTO tblmessage (sender_id, receiver_id, message) VALUES
(1,2,'Hi, is this available?'),
(2,1,'Yes, it is still available')
");

// 🔴 Enable FK checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "ClothingStore database loaded successfully!";
?>