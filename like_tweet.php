<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$tweet_id = $data['tweet_id'];

try {
    // Check if already liked
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE tweet_id = ? AND user_id = ?");
    $stmt->execute([$tweet_id, $_SESSION['user_id']]);
    $existing_like = $stmt->fetch();

    if ($existing_like) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM likes WHERE tweet_id = ? AND user_id = ?");
        $stmt->execute([$tweet_id, $_SESSION['user_id']]);
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO likes (tweet_id, user_id) VALUES (?, ?)");
        $stmt->execute([$tweet_id, $_SESSION['user_id']]);
    }

    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 
