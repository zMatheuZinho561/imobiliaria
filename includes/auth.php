<?php
// Configuração segura da sessão
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// Headers de segurança
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; base-uri 'self'");

// Expiração por inatividade (15 minutos)
const SESSION_IDLE_SEC = 900;
if (isset($_SESSION['ultimo_acesso'])) {
    if (time() - $_SESSION['ultimo_acesso'] > SESSION_IDLE_SEC) {
        session_unset();
        session_destroy();
        header("Location: /public/login.php?timeout=1");
        exit;
    }
}
$_SESSION['ultimo_acesso'] = time();

// Função para exigir login
function require_login($role = null) {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: /public/login.php");
        exit;
    }
    if ($role && ($_SESSION['usuario_role'] ?? '') !== $role) {
        header("HTTP/1.1 403 Forbidden");
        exit("Acesso negado.");
    }
}