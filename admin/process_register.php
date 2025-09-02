<?php
require_once "../includes/config.php";

$erro = '';
$sucesso = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome  = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $erro = "E-mail já cadastrado.";
        } else {
            $hash = password_hash($senha, PASSWORD_ARGON2ID);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$nome, $email, $hash]);
            $sucesso = "Usuário registrado com sucesso!";
        }
    }
}
?>