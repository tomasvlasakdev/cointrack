<?php
session_start();
include_once 'sidebar.php';
include_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /PCv/cointrack_2/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
}

header("Location: transactions.php");
exit;

?>