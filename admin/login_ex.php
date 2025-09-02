<?php
// Configuração da sessão
session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'secure' => isset($_SERVER['HTTPS']),
    'samesite' => 'Strict'
]);
session_start();

require_once "../includes/config.php";

$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = $_POST["senha"];

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "E-mail inválido.";
    } else {
        // Busca usuário
        $stmt = $pdo->prepare("SELECT id, nome, senha, role FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario["senha"])) {
            // Sessão segura
            session_regenerate_id(true);

            $_SESSION["usuario_id"]   = $usuario["id"];
            $_SESSION["usuario_nome"] = $usuario["nome"];
            $_SESSION["usuario_role"] = strtolower($usuario["role"]); // força para minúsculo

            // 🚀 Debug temporário (remover depois)
          

            // Redireciona baseado no role
            if ($_SESSION["usuario_role"] === "admin") {
                header("Location: dashboard.php");
                exit;
            } else {
                header("Location: ../public/index.php");
                exit;
            }
        } else {
            $erro = "E-mail ou senha inválidos.";
        }
    }
}
?>