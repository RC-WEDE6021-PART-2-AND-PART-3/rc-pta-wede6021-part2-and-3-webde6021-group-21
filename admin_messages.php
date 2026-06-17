<?php
session_start();
include 'includes/DBConn.php';
include 'includes/navbar.php';

// Check if admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle send message
if (isset($_POST['send_message'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    $conn->query("INSERT INTO tbladminmessages (sender_id, receiver_id, subject, message, sent_at) 
                  VALUES ({$_SESSION['user_id']}, $receiver_id, '$subject', '$message', NOW())");
    $success = "Message sent!";
}

// Get all users
$users = $conn->query("SELECT user_id, name, username, role FROM tbluser WHERE user_id != {$_SESSION['user_id']}");
$messages = $conn->query("
    SELECT m.*, u.name as sender_name, u2.name as receiver_name 
    FROM tbladminmessages m
    JOIN tbluser u ON m.sender_id = u.user_id
    JOIN tbluser u2 ON m.receiver_id = u2.user_id
    ORDER BY m.sent_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Messages - Pastimes</title>
    <style>
        body { 
            font-family: Arial; 
            background: #f5f7fa; 
            margin: 0; 
            padding: 0;
        }
        .container { 
            max-width: 1200px; 
            margin: 20px auto 40px;  /* Changed from 100px to 20px */
            padding: 20px; 
        }
        h1 { 
            color: #0b1a33; 
            margin-bottom: 20px;
            margin-top: 0;  /* Remove top margin */
            font-size: 28px;
        }
        .message-layout { 
            display: flex; 
            gap: 20px; 
            flex-wrap: wrap; 
        }
        .send-form { 
            flex: 1; 
            background: white; 
            padding: 20px; 
            border-radius: 15px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .inbox { 
            flex: 2; 
            background: white; 
            padding: 20px; 
            border-radius: 15px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: bold; 
        }
        input, select, textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
        }
        .btn { 
            background: #0b1a33; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
        }
        .btn:hover {
            background: #1f2f4d;
        }
        .message-item { 
            padding: 15px; 
            border-bottom: 1px solid #eee; 
        }
        .message-item:last-child { 
            border-bottom: none; 
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
        }
        @media (max-width: 768px) {
            .message-layout { 
                flex-direction: column; 
            }
            .container { 
                margin: 20px auto 40px;  /* Changed from 80px to 20px */
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>💬 Admin Messages</h1>
    
    <?php if (isset($success)): ?>
        <div class="success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="message-layout">
        <div class="send-form">
            <h3>Send Message</h3>
            <form method="POST">
                <div class="form-group">
                    <label>To User</label>
                    <select name="receiver_id" required>
                        <option value="">Select User</option>
                        <?php while($user = $users->fetch_assoc()): ?>
                            <option value="<?php echo $user['user_id']; ?>"><?php echo $user['name']; ?> (<?php echo $user['role']; ?>) - @<?php echo $user['username']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="4" required></textarea>
                </div>
                <button type="submit" name="send_message" class="btn">Send Message</button>
            </form>
        </div>
        
        <div class="inbox">
            <h3>Message History</h3>
            <?php if ($messages->num_rows == 0): ?>
                <p>No messages yet.</p>
            <?php else: ?>
                <?php while($msg = $messages->fetch_assoc()): ?>
                    <div class="message-item">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px; flex-wrap: wrap;">
                            <strong>From: <?php echo $msg['sender_name']; ?></strong>
                            <strong>To: <?php echo $msg['receiver_name']; ?></strong>
                            <small><?php echo $msg['sent_at']; ?></small>
                        </div>
                        <div><strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject']); ?></div>
                        <div style="margin-top: 10px;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="adminDashboard.php" class="btn">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>