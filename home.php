<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    die();
}

// Handle new tweet submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tweet_content'])) {
    $content = trim($_POST['tweet_content']);
    if (strlen($content) <= 280 && strlen($content) > 0) {
        $user_id = $_SESSION['user_id'];
        $content = mysqli_real_escape_string($conn, $content);
        $query = "INSERT INTO tweets (user_id, content) VALUES ('$user_id', '$content')";
        mysqli_query($conn, $query);
    }
}

// Fetch tweets for timeline
$query = "SELECT tweets.*, users.username, users.profile_pic 
          FROM tweets 
          JOIN users ON tweets.user_id = users.user_id 
          ORDER BY tweets.created_at DESC";
$result = mysqli_query($conn, $query);
$tweets = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Twitter Clone - Home</title>
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
        .tweet-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .tweet-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            min-height: 100px;
        }
        .tweet {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .tweet-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .tweet-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .tweet-actions {
            margin-top: 10px;
            display: flex;
            gap: 20px;
        }
        .tweet-actions a {
            color: #666;
            text-decoration: none;
        }
        button {
            background-color: #1da1f2;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #1991db;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Twitter Clone</h1>
        <div>
            <a href="profile.php?username=<?php echo $_SESSION['username']; ?>">Profile</a> |
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="tweet-form">
            <form method="POST">
                <textarea name="tweet_content" placeholder="What's happening?" maxlength="280" required></textarea>
                <button type="submit">Tweet</button>
            </form>
        </div>

        <div class="tweets">
            <?php if($tweets): ?>
                <?php foreach ($tweets as $tweet): ?>
                    <div class="tweet">
                        <div class="tweet-header">
                            <img src="<?php echo htmlspecialchars($tweet['profile_pic']); ?>" alt="Profile Picture">
                            <strong><?php echo htmlspecialchars($tweet['username']); ?></strong>
                        </div>
                        <div class="tweet-content">
                            <?php echo htmlspecialchars($tweet['content']); ?>
                        </div>
                        <div class="tweet-actions">
                            <a href="#" onclick="likeTweet(<?php echo $tweet['tweet_id']; ?>)">Like</a>
                            <span><?php echo date('M d', strtotime($tweet['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No tweets yet!</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function likeTweet(tweetId) {
        fetch('like_tweet.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tweet_id: tweetId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Tweet liked/unliked successfully');
            }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
</body>
</html> 
