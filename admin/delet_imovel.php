<?php
require_once '../includes/auth.php';
require_login();
require_once "../includes/config.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM imoveis WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: imoveis.php");
exit;