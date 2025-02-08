<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get username from URL
$username = isset($_GET['username']) ? $_GET['username'] : $_SESSION['username'];

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$profile_user = $stmt->fetch();

if (!$profile_user) {
    header("Location: home.php");
    exit();
}

// Handle follow/unfollow
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['follow'])) {
    $follower_id = $_SESSION['user_id'];
    $following_id = $profile_user['user_id'];
    
    if ($follower_id != $following_id) {
        if ($_POST['follow'] == 'follow') {
            $stmt = $pdo->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)");
        } else {
            $stmt = $pdo->prepare("DELETE FROM followers WHERE follower_id = ? AND following_id = ?");
        }
        $stmt->execute([$follower_id, $following_id]);
    }
}

// Check if current user is following profile user
$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ? AND following_id = ?");
$stmt->execute([$_SESSION['user_id'], $profile_user['user_id']]);
$is_following = $stmt->fetchColumn() > 0;

// Get follower and following counts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE following_id = ?");
$stmt->execute([$profile_user['user_id']]);
$followers_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ?");
$stmt->execute([$profile_user['user_id']]);
$following_count = $stmt->fetchColumn();

// Get user's tweets
$stmt = $pdo->prepare("
    SELECT tweets.*, users.username, users.profile_pic 
    FROM tweets 
    JOIN users ON tweets.user_id = users.user_id 
    WHERE tweets.user_id = ? 
    ORDER BY tweets.created_at DESC
");
$stmt->execute([$profile_user['user_id']]);
$tweets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Twitter Clone - <?php echo htmlspecialchars($username); ?>'s Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f8fa;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: white;
            padding: 10px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .profile-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 15px 0;
        }
        .follow-btn {
            background-color: #1da1f2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
        }
        .unfollow-btn {
            background-color: #fff;
            color: #1da1f2;
            border: 1px solid #1da1f2;
        }
        .tweet {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Twitter Clone</h1>
        <div>
            <a href="home.php">Home</a> |
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($profile_user['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
            <h2><?php echo htmlspecialchars($username); ?></h2>
            
            <div class="stats">
                <div>
                    <strong><?php echo $followers_count; ?></strong> Followers
                </div>
                <div>
                    <strong><?php echo $following_count; ?></strong> Following
                </div>
            </div>

            <?php if ($_SESSION['user_id'] != $profile_user['user_id']): ?>
                <form method="POST">
                    <?php if ($is_following): ?>
                        <input type="hidden" name="follow" value="unfollow">
                        <button type="submit" class="follow-btn unfollow-btn">Unfollow</button>
                    <?php else: ?>
                        <input type="hidden" name="follow" value="follow">
                        <button type="submit" class="follow-btn">Follow</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>

        <div class="tweets">
            <?php foreach ($tweets as $tweet): ?>
                <div class="tweet">
                    <div class="tweet-content">
                        <?php echo htmlspecialchars($tweet['content']); ?>
                    </div>
                    <div style="color: #666; font-size: 0.9em; margin-top: 10px;">
                        <?php echo date('M d, Y', strtotime($tweet['created_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 
