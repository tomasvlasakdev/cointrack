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
    $confirm = $_POST['confirm'];
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($confirm) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Check confirmation text
    if ($confirm !== 'DELETE') {
        echo json_encode(['success' => false, 'message' => 'Please type DELETE to confirm']);
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
        
        // Begin transaction
        $pdo->beginTransaction();
        
        try {
            // Delete user's transactions
            $stmt = $pdo->prepare("DELETE FROM transactions WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            // Delete user's portfolio data (if table exists)
            try {
                $stmt = $pdo->prepare("DELETE FROM portfolio WHERE user_id = ?");
                $stmt->execute([$user_id]);
            } catch (PDOException $e) {
                // Table might not exist, continue
            }
            
            // Delete user's notification settings (if table exists)
            try {
                $stmt = $pdo->prepare("DELETE FROM user_notifications WHERE user_id = ?");
                $stmt->execute([$user_id]);
            } catch (PDOException $e) {
                // Table might not exist, continue
            }
            
            
            // Finally, delete the user account
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            // Commit transaction
            $pdo->commit();
            
            // Destroy session
            session_destroy();
            
            echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
            
        } catch (PDOException $e) {
            // Rollback on error
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete account: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>