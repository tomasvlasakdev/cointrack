<?php

session_start();
include_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $new_username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($new_username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Validate username format
    if (strlen($new_username) < 3 || strlen($new_username) > 30) {
        echo json_encode(['success' => false, 'message' => 'Username must be between 3 and 30 characters']);
        exit();
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
        echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);
        exit();
    }
    
    try {
        // Verify password
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid password']);
            exit();
        }
        
        // Check if username is already taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$new_username, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Username is already taken']);
            exit();
        }
        
        // Update username
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        
        if ($stmt->execute([$new_username, $user_id])) {
            $_SESSION['username'] = $new_username;
            echo json_encode(['success' => true, 'message' => 'Username updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update username']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>