<?php
require_once "../includes/config.php";
session_start();

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_role"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

// Verifica se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['erro'] = "ID do imóvel não fornecido ou inválido.";
    header("Location: imoveis.php");
    exit;
}

$imovel_id = (int)$_GET['id'];

try {
    // Buscar informações do imóvel antes de excluir
    $stmt = $pdo->prepare("SELECT titulo, imagem FROM imoveis WHERE id = ?");
    $stmt->execute([$imovel_id]);
    $imovel = $stmt->fetch();
    
    if (!$imovel) {
        $_SESSION['erro'] = "Imóvel não encontrado.";
        header("Location: imoveis.php");
        exit;
    }
   
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Excluir registros relacionados primeiro (se houver)
    // Por exemplo, favoritos, visualizações, etc.
    $pdo->prepare("DELETE FROM favoritos WHERE imovel_id = ?")->execute([$imovel_id]);
    $pdo->prepare("DELETE FROM contatos WHERE imovel_id = ?")->execute([$imovel_id]);
    
    // Excluir o imóvel
    $stmt = $pdo->prepare("DELETE FROM imoveis WHERE id = ?");
    $result = $stmt->execute([$imovel_id]);
    
    if ($result) {
        // Remover imagem do servidor se existir
        if (!empty($imovel['imagem'])) {
            $caminho_imagem = "uploads/" . $imovel['imagem'];
            if (file_exists($caminho_imagem)) {
                unlink($caminho_imagem);
            }
        }
        
        // Confirmar transação
        $pdo->commit();
        
        $_SESSION['sucesso'] = "Imóvel '" . htmlspecialchars($imovel['titulo']) . "' excluído com sucesso!";
    } else {
        $pdo->rollback();
        $_SESSION['erro'] = "Erro ao excluir o imóvel.";
    }
    
} catch (Exception $e) {
    $pdo->rollback();
    $_SESSION['erro'] = "Erro ao excluir imóvel: " . $e->getMessage();
}

// Redirecionar de volta para a lista de imóveis
header("Location: imoveis.php");
exit;
?>