<?php

include 'includes/DBConn.php';
include 'includes/navbar.php';

if (!isset($_SESSION['username'])) {
    die("Please login first");
}

$sender = $_SESSION['username'];

// Get sender ID from tbluser
$user_result = $conn->query("SELECT user_id FROM tbluser WHERE username = '$sender'");
if ($user_result && $user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $sender_id = $user_data['user_id'];
} else {
    die("User not found");
}

// SEND MESSAGE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['receiver_id']) && isset($_POST['message'])) {
    $receiver = intval($_POST['receiver_id']);
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $escaped_message = $conn->real_escape_string($message);
        // Fixed: Using 'message' column instead of 'message_text', removed 'timestamp'
        $conn->query("INSERT INTO tblmessage (sender_id, receiver_id, message)
                      VALUES ($sender_id, $receiver, '$escaped_message')");
    }
    
    // Redirect to the same conversation after sending
    header("Location: " . $_SERVER['PHP_SELF'] . "?user=" . $receiver);
    exit();
}

// Get selected conversation from URL
$selected_user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;

// GET MESSAGES for the selected conversation
$result = null;
if ($selected_user_id > 0) {
    // Fixed: Using 'message' column instead of 'message_text', removed 'timestamp'
    $result = $conn->query("
        SELECT * FROM tblmessage 
        WHERE (sender_id = $sender_id AND receiver_id = $selected_user_id)
           OR (sender_id = $selected_user_id AND receiver_id = $sender_id)
        ORDER BY message_id ASC
    ");
}

// Get unique conversations for sidebar
$convos = $conn->query("
    SELECT DISTINCT u.user_id, u.username, u.name
    FROM tbluser u
    WHERE u.user_id IN (
        SELECT receiver_id FROM tblmessage WHERE sender_id = $sender_id
        UNION
        SELECT sender_id FROM tblmessage WHERE receiver_id = $sender_id
    )
");

// Get all users for new conversation
$all_users = $conn->query("SELECT user_id, username, name FROM tbluser WHERE user_id != $sender_id");

// Get selected user info
$selected_user_name = "";
$selected_display_name = "";
if ($selected_user_id > 0) {
    $user_info = $conn->query("SELECT username, name FROM tbluser WHERE user_id = $selected_user_id");
    if ($user_info && $user_info->num_rows > 0) {
        $user_data = $user_info->fetch_assoc();
        $selected_user_name = $user_data['username'];
        $selected_display_name = $user_data['name'] ?: $user_data['username'];
    }
}

// Get the current filename
$current_file = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Vintage Marketplace</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            background: #f7f3ef;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Adjust container to account for navbar */
        .messages-container {
            width: 1300px;
            max-width: 95vw;
            height: calc(100vh - 80px);
            background: #fffdf9;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            display: flex;
            overflow: hidden;
            margin: 80px auto 20px auto;
        }

        /* Sidebar */
        .sidebar {
            width: 320px;
            background: #fef9f2;
            border-right: 1px solid #e8dfd3;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 24px 20px 16px;
            border-bottom: 1px solid #e8dfd3;
        }

        .sidebar-header h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #4a3b2c;
            letter-spacing: -0.3px;
        }

        .search-convos {
            padding: 12px 16px;
            border-bottom: 1px solid #e8dfd3;
        }

        .search-convos input {
            width: 100%;
            padding: 10px 14px;
            background: white;
            border: 1px solid #e2d8ce;
            border-radius: 30px;
            font-size: 0.85rem;
            outline: none;
            transition: all 0.2s;
        }

        .search-convos input:focus {
            border-color: #c2a575;
            box-shadow: 0 0 0 2px rgba(194,165,117,0.1);
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            gap: 12px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f0e9e2;
        }

        .conversation-item:hover {
            background: #f5ede4;
        }

        .conversation-item.active {
            background: #efe3d7;
            border-left: 3px solid #c2a575;
        }

        .avatar {
            width: 48px;
            height: 48px;
            background: #e6d9ce;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            color: #5e4b34;
            flex-shrink: 0;
        }

        .conv-info {
            flex: 1;
            min-width: 0;
        }

        .conv-name {
            font-weight: 600;
            color: #2c241a;
            margin-bottom: 4px;
        }

        .conv-preview {
            font-size: 0.75rem;
            color: #8f7e6b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conv-time {
            font-size: 0.7rem;
            color: #b8a48c;
        }

        /* Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fffdf9;
        }

        .chat-header {
            padding: 18px 24px;
            border-bottom: 1px solid #efe5db;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fffdf9;
        }

        .back-icon {
            display: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: #8f7e6b;
        }

        .chat-avatar {
            width: 44px;
            height: 44px;
            background: #e0d3c6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #5e4b34;
        }

        .chat-user-info {
            flex: 1;
        }

        .chat-user-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #2c241a;
        }

        .chat-status {
            font-size: 0.7rem;
            color: #7d9b6e;
            margin-top: 2px;
        }

        /* Messages area */
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 24px 28px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            background: #fcf9f5;
        }

        .message-bubble {
            display: flex;
            max-width: 70%;
        }

        .message-sent {
            justify-content: flex-end;
            align-self: flex-end;
        }

        .message-received {
            justify-content: flex-start;
            align-self: flex-start;
        }

        .bubble {
            padding: 10px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            line-height: 1.4;
            max-width: 100%;
            word-wrap: break-word;
        }

        .message-sent .bubble {
            background: #d9e8db;
            color: #2c3e2b;
            border-bottom-right-radius: 4px;
        }

        .message-received .bubble {
            background: white;
            border: 1px solid #e8dfd3;
            color: #3a3228;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 0.65rem;
            color: #b7a78a;
            margin-top: 4px;
            text-align: right;
        }

        .message-received .message-time {
            text-align: left;
        }

        /* Input area */
        .message-input-wrapper {
            padding: 18px 24px 24px;
            border-top: 1px solid #efe5db;
            background: #fffdf9;
        }

        .input-group {
            display: flex;
            gap: 12px;
            align-items: flex-end;
        }

        .input-group input {
            flex: 1;
            border: 1px solid #e2d8ce;
            border-radius: 24px;
            padding: 12px 18px;
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            background: white;
            transition: 0.2s;
        }

        .input-group input:focus {
            border-color: #c2a575;
            box-shadow: 0 0 0 2px rgba(194,165,117,0.15);
        }

        .send-btn {
            background: #c2a575;
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 0.85rem;
        }

        .send-btn:hover {
            background: #aa8c5c;
        }

        .empty-chat {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #b8a48c;
            text-align: center;
            flex-direction: column;
            gap: 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .chat-area {
                width: 100%;
            }
            .back-icon {
                display: block;
            }
            .messages-area {
                padding: 16px;
            }
            .message-bubble {
                max-width: 85%;
            }
            .messages-container {
                height: calc(100vh - 60px);
                margin-top: 60px;
            }
        }
    </style>
</head>
<body>
<div class="messages-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Messages</h2>
        </div>
        <div class="search-convos">
            <input type="text" placeholder="Search conversations..." id="searchConv">
        </div>
        <div class="conversations-list" id="convList">
            <?php if ($convos && $convos->num_rows > 0): ?>
                <?php while($conv = $convos->fetch_assoc()): 
                    $partner_id = $conv['user_id'];
                    $partner_name = htmlspecialchars($conv['username']);
                    $display_name = htmlspecialchars($conv['name'] ?: $conv['username']);
                    $initial = strtoupper(substr($display_name, 0, 1));
                    // Fixed: Using 'message' column instead of 'message_text', removed 'timestamp'
                    $preview_res = $conn->query("
                        SELECT message FROM tblmessage 
                        WHERE (sender_id = $sender_id AND receiver_id = $partner_id)
                           OR (sender_id = $partner_id AND receiver_id = $sender_id)
                        ORDER BY message_id DESC LIMIT 1
                    ");
                    $last_msg = $preview_res->fetch_assoc();
                    $preview = $last_msg ? (strlen($last_msg['message']) > 35 ? substr($last_msg['message'],0,32).'...' : $last_msg['message']) : "No messages yet";
                    $is_active = ($selected_user_id == $partner_id) ? 'active' : '';
                ?>
                <div class="conversation-item <?php echo $is_active; ?>" data-user-id="<?php echo $partner_id; ?>">
                    <div class="avatar"><?php echo $initial; ?></div>
                    <div class="conv-info">
                        <div class="conv-name"><?php echo $display_name; ?></div>
                        <div class="conv-preview"><?php echo $preview; ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="padding: 30px 20px; text-align:center; color:#b8a48c;">No conversations yet</div>
            <?php endif; ?>
        </div>

        <div style="padding: 12px 20px 20px; border-top: 1px solid #e8dfd3;">
            <select id="newChatSelect" style="width:100%; padding:10px; border-radius:30px; border:1px solid #e2d8ce; background:white;">
                <option value="">Start new conversation...</option>
                <?php if ($all_users && $all_users->num_rows > 0): ?>
                    <?php while($u = $all_users->fetch_assoc()): ?>
                        <option value="<?php echo $u['user_id']; ?>"><?php echo htmlspecialchars($u['name'] ?: $u['username']); ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
        <?php if ($selected_user_id > 0 && $selected_display_name != ""): ?>
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="back-icon" id="backIcon">←</div>
                <div class="chat-avatar"><?php echo strtoupper(substr($selected_display_name, 0, 1)); ?></div>
                <div class="chat-user-info">
                    <div class="chat-user-name"><?php echo $selected_display_name; ?></div>
                    <div class="chat-status">Online</div>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="messages-area" id="messagesArea">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($msg = $result->fetch_assoc()): 
                        $is_sent = ($msg['sender_id'] == $sender_id);
                        // Fixed: Using 'message' column instead of 'message_text'
                        $msg_text = htmlspecialchars($msg['message']);
                    ?>
                        <div class="message-bubble <?php echo $is_sent ? 'message-sent' : 'message-received'; ?>">
                            <div>
                                <div class="bubble"><?php echo nl2br($msg_text); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-chat">
                        <span>💬</span>
                        <p>No messages yet. Send a message to start the conversation!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Message Input Form -->
            <div class="message-input-wrapper">
                <form method="POST" action="" id="messageForm">
                    <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id; ?>">
                    <div class="input-group">
                        <input type="text" name="message" placeholder="Type a message..." required id="msgInput" autocomplete="off">
                        <button type="submit" class="send-btn">Send</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- No conversation selected -->
            <div class="empty-chat" style="height: 100%;">
                <span>💬</span>
                <p>Select a conversation from the sidebar</p>
                <p style="font-size: 0.8rem; margin-top: 10px;">or start a new conversation using the dropdown below</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Auto-scroll to bottom of messages when page loads
const messagesArea = document.getElementById('messagesArea');
if (messagesArea) {
    messagesArea.scrollTop = messagesArea.scrollHeight;
}

// Handle conversation item clicks (using JavaScript instead of links)
const conversationItems = document.querySelectorAll('.conversation-item');
conversationItems.forEach(item => {
    item.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        if (userId) {
            window.location.href = window.location.pathname + '?user=' + userId;
        }
    });
});

// New conversation handler - redirect when user selects from dropdown
const newChatSelect = document.getElementById('newChatSelect');
if (newChatSelect) {
    newChatSelect.addEventListener('change', function() {
        if (this.value) {
            window.location.href = window.location.pathname + '?user=' + this.value;
        }
    });
}

// Search conversations filter
const searchInput = document.getElementById('searchConv');
if (searchInput) {
    searchInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const convs = document.querySelectorAll('.conversation-item');
        convs.forEach(conv => {
            const name = conv.querySelector('.conv-name')?.innerText.toLowerCase() || '';
            if (name.includes(filter)) {
                conv.style.display = 'flex';
            } else {
                conv.style.display = 'none';
            }
        });
    });
}

// Allow Enter key to send message
const msgInput = document.getElementById('msgInput');
if (msgInput) {
    msgInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const form = document.getElementById('messageForm');
            if (form) {
                form.submit();
            }
        }
    });
}

// Focus on message input when conversation loads
if (msgInput) {
    msgInput.focus();
}

// Mobile back button
const backIcon = document.getElementById('backIcon');
if (backIcon) {
    backIcon.addEventListener('click', function() {
        window.location.href = window.location.pathname;
    });
}
</script>
</body>
</html>