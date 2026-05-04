<?php
include 'includes/DBConn.php';
include 'includes/navbar.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



// VERIFY
if (isset($_GET['verify'])) {
    $id = $_GET['verify'];
    $conn->query("UPDATE tbluser SET status='verified' WHERE user_id=$id");
}

// DELETE (FIXED WITH DEPENDENCIES)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Delete related data FIRST (prevents foreign key errors)
    @$conn->query("DELETE FROM tblmessage WHERE sender_id=$id OR receiver_id=$id");
    @$conn->query("DELETE FROM tblorder WHERE user_id=$id");
    @$conn->query("DELETE FROM tblclothes WHERE seller_id=$id");

    // Then delete user
    $conn->query("DELETE FROM tbluser WHERE user_id=$id");
}

// ADD USER
if (isset($_POST['addUser'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    $conn->query("INSERT INTO tblUser (name,email,username,password,role,status)
                  VALUES ('$name','$email','$username','$password','$role','pending')");
}

// UPDATE USER
if (isset($_POST['updateUser'])) {
    $id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    $conn->query("UPDATE tbluser SET 
        name='$name',
        email='$email',
        role='$role'
        WHERE user_id=$id");
}

$result = $conn->query("SELECT * FROM tbluser");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>
body {
    font-family: Arial;
    background: #f5f5f5;
}

.container {
    width: 90%;
    margin: auto;
}

h2 {
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

th {
    background: black;
    color: white;
}

a, button {
    text-decoration: none;
    padding: 6px 10px;
    border-radius: 5px;
    font-size: 12px;
}

.verify { background: green; color: white; }
.delete { background: red; color: white; }
.edit { background: #555; color: white; }

.form-box {
    background: white;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 8px;
}

input, select {
    padding: 8px;
    margin: 5px;
}

button {
    background: black;
    color: white;
    border: none;
}
</style>
</head>

<body>

<div class="container">

<h2>Admin Dashboard</h2>

<!-- ADD USER -->
<div class="form-box">
<h3>Add User</h3>

<form method="POST">
    <input type="text" name="name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="role">
        <option value="buyer">Buyer</option>
        <option value="seller">Seller</option>
        <option value="admin">Admin</option>
    </select>

    <button name="addUser">Add User</button>
</form>
</div>

<!-- USER TABLE -->
<table>
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Username</th>
    <th>Role</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()) { ?>
<tr>

<form method="POST">

    <td>
        <input type="text" name="name" value="<?php echo $row['name']; ?>">
    </td>

    <td>
        <input type="email" name="email" value="<?php echo $row['email']; ?>">
    </td>

    <td><?php echo $row['username']; ?></td>

    <td>
        <select name="role">
            <option <?php if($row['role']=="buyer") echo "selected"; ?>>buyer</option>
            <option <?php if($row['role']=="seller") echo "selected"; ?>>seller</option>
            <option <?php if($row['role']=="admin") echo "selected"; ?>>admin</option>
        </select>
    </td>

    <td><?php echo $row['status']; ?></td>

    <td>
        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">

        <button name="updateUser" class="edit">Update</button>

        <?php if ($row['status'] != 'verified') { ?>
            <a class="verify" href="adminDashboard.php?verify=<?php echo $row['user_id']; ?>">Verify</a>
        <?php } ?>

        <!-- CONFIRMATION ADDED -->
        <a class="delete"
           onclick="return confirm('Are you sure you want to delete this user? This will remove all related data.')"
           href="adminDashboard.php?delete=<?php echo $row['user_id']; ?>">
           Delete
        </a>
    </td>

</form>

</tr>
<?php } ?>

</table>

</div>

</body>
</html>