<?php
require_once "../includes/config.php";
// Verifica se o usuário está logado e é admin
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_role"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

$id = $_GET['id'] ?? null;
$titulo = $descricao = $preco = $localizacao = $bairro = $tipo_imovel = $tipo_negocio = $area = $quartos = $banheiros = $garagem = $status = $destaque = $imagem = "";

// Editar
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM imoveis WHERE id = ?");
    $stmt->execute([$id]);
    $imovel = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($imovel) {
        $titulo = $imovel['titulo'];
        $descricao = $imovel['descricao'];
        $preco = $imovel['preco'];
        $localizacao = $imovel['localizacao'];
        $bairro = $imovel['bairro'] ?? '';
        $tipo_imovel = $imovel['tipo_imovel'] ?? '';
        $tipo_negocio = $imovel['tipo_negocio'] ?? 'venda';
        $area = $imovel['area'];
        $quartos = $imovel['quartos'];
        $banheiros = $imovel['banheiros'];
        $garagem = $imovel['garagem'];
        $status = $imovel['status'] ?? 'ativo';
        $destaque = $imovel['destaque'] ?? 0;
        $imagem = $imovel['imagem'] ?? "";
    }
}

// Salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $preco = str_replace(['.', ','], ['', '.'], $_POST['preco']); // Converte formato brasileiro
    $localizacao = $_POST['localizacao'];
    $bairro = $_POST['bairro'];
    $tipo_imovel = $_POST['tipo_imovel'];
    $tipo_negocio = $_POST['tipo_negocio'];
    $area = $_POST['area'];
    $quartos = $_POST['quartos'];
    $banheiros = $_POST['banheiros'];
    $garagem = $_POST['garagem'];
    $status = $_POST['status'];
    $destaque = isset($_POST['destaque']) ? 1 : 0;

    // Upload de imagem
    $nomeImagem = $imagem;
    if (!empty($_FILES['imagem']['name'])) {
        $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = time() . '_' . uniqid() . '.' . $extensao;
        
        // Criar diretório se não existir
        if (!is_dir("../public/uploads")) {
            mkdir("../public/uploads", 0777, true);
        }
        
        move_uploaded_file($_FILES['imagem']['tmp_name'], "../public/uploads/" . $nomeImagem);
    } 

    try {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE imoveis SET titulo=?, descricao=?, preco=?, localizacao=?, bairro=?, tipo_imovel=?, tipo_negocio=?, area=?, quartos=?, banheiros=?, garagem=?, status=?, destaque=?, imagem=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$titulo, $descricao, $preco, $localizacao, $bairro, $tipo_imovel, $tipo_negocio, $area, $quartos, $banheiros, $garagem, $status, $destaque, $nomeImagem, $id]);
            $mensagem = "Imóvel atualizado com sucesso!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO imoveis (titulo, descricao, preco, localizacao, bairro, tipo_imovel, tipo_negocio, area, quartos, banheiros, garagem, status, destaque, imagem, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$titulo, $descricao, $preco, $localizacao, $bairro, $tipo_imovel, $tipo_negocio, $area, $quartos, $banheiros, $garagem, $status, $destaque, $nomeImagem]);
            $mensagem = "Imóvel cadastrado com sucesso!";
        }
        
        $_SESSION['success'] = $mensagem;
        header("Location: imoveis.php");
        exit;
    } catch(Exception $e) {
        $erro = "Erro ao salvar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id ? 'Editar' : 'Novo'; ?> Imóvel - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .preview-image {
            transition: all 0.3s ease;
        }
        .preview-image:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-building mr-2 text-blue-600"></i>
                        <?php echo $id ? 'Editar Imóvel' : 'Novo Imóvel'; ?>
                    </h1>
                </div>
                <nav class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 font-medium">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <a href="imoveis.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i>Voltar
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Mensagens -->
        <?php if (isset($erro)): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-circle text-red-400 mr-2 mt-0.5"></i>
                    <p class="text-red-700"><?= $erro ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulário -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">
                    <i class="fas fa-edit mr-2"></i>
                    Informações do Imóvel
                </h2>
            </div>

            <form method="POST" enctype="multipart/form-data" class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Coluna Esquerda -->
                    <div class="space-y-6">
                        <!-- Informações Básicas -->
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                Informações Básicas
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Título *</label>
                                    <input type="text" name="titulo" value="<?= htmlspecialchars($titulo) ?>" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                                           placeholder="Ex: Casa com 3 quartos no centro" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrição *</label>
                                    <textarea name="descricao" rows="4" 
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                                              placeholder="Descreva as principais características do imóvel..." required><?= htmlspecialchars($descricao) ?></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Negócio *</label>
                                        <select name="tipo_negocio" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                            <option value="venda" <?= $tipo_negocio == 'venda' ? 'selected' : '' ?>>Venda</option>
                                            <option value="aluguel" <?= $tipo_negocio == 'aluguel' ? 'selected' : '' ?>>Aluguel</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo do Imóvel *</label>
                                        <select name="tipo_imovel" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                            <option value="">Selecione...</option>
                                            <option value="casa" <?= $tipo_imovel == 'casa' ? 'selected' : '' ?>>Casa</option>
                                            <option value="apartamento" <?= $tipo_imovel == 'apartamento' ? 'selected' : '' ?>>Apartamento</option>
                                            <option value="sobrado" <?= $tipo_imovel == 'sobrado' ? 'selected' : '' ?>>Sobrado</option>
                                            <option value="chacara" <?= $tipo_imovel == 'chacara' ? 'selected' : '' ?>>Chácara</option>
                                            <option value="terreno" <?= $tipo_imovel == 'terreno' ? 'selected' : '' ?>>Terreno</option>
                                            <option value="comercial" <?= $tipo_imovel == 'comercial' ? 'selected' : '' ?>>Comercial</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Localização -->
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                Localização
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cidade *</label>
                                    <input type="text" name="localizacao" value="<?= htmlspecialchars($localizacao) ?>" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="Ex: Cambé - PR" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                                    <input type="text" name="bairro" value="<?= htmlspecialchars($bairro) ?>" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="Ex: Centro">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna Direita -->
                    <div class="space-y-6">
                        <!-- Características -->
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-home text-green-500 mr-2"></i>
                                Características
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Preço (R$) *</label>
                                    <input type="text" name="preco" value="<?= htmlspecialchars($preco) ?>" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="Ex: 350000.00" required>
                                    <p class="text-xs text-gray-500 mt-1">Use ponto para decimais (ex: 350000.00)</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Área (m²)</label>
                                    <input type="number" name="area" value="<?= htmlspecialchars($area) ?>" step="0.01"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="Ex: 120.50">
                                </div>

                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Quartos</label>
                                        <input type="number" name="quartos" value="<?= htmlspecialchars($quartos) ?>" min="0"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Banheiros</label>
                                        <input type="number" name="banheiros" value="<?= htmlspecialchars($banheiros) ?>" min="0"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Garagem</label>
                                        <input type="number" name="garagem" value="<?= htmlspecialchars($garagem) ?>" min="0"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status e Configurações -->
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-cogs text-purple-500 mr-2"></i>
                                Status e Configurações
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                        <option value="ativo" <?= $status == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                        <option value="inativo" <?= $status == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                        <option value="vendido" <?= $status == 'vendido' ? 'selected' : '' ?>>Vendido</option>
                                        <option value="alugado" <?= $status == 'alugado' ? 'selected' : '' ?>>Alugado</option>
                                    </select>
                                </div>

                                <div class="flex items-center">
                                    <input type="checkbox" name="destaque" value="1" <?= $destaque ? 'checked' : '' ?> 
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-gray-700">
                                        <i class="fas fa-star text-yellow-500 mr-1"></i>
                                        Imóvel em Destaque
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Upload de Imagem -->
                        <div class="bg-gray-50 rounded-xl p-5">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-camera text-indigo-500 mr-2"></i>
                                Imagem do Imóvel
                            </h3>
                            
                            <?php if ($imagem): ?>
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 mb-2">Imagem atual:</p>
                                    <img src="../public/uploads/<?= htmlspecialchars($imagem) ?>" 
                                         alt="Imagem atual" 
                                         class="preview-image w-full h-48 object-cover rounded-lg border-2 border-gray-200">
                                </div>
                            <?php endif; ?>

                            <input type="file" name="imagem" accept="image/*"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-2">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB</p>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-between items-center pt-8 border-t border-gray-200 mt-8">
                    <a href="imoveis.php" 
                       class="inline-flex items-center px-6 py-3 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-lg font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Cancelar
                    </a>
                    
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-semibold transition-all transform hover:scale-105">
                        <i class="fas fa-save mr-2"></i>
                        <?= $id ? 'Atualizar Imóvel' : 'Cadastrar Imóvel' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Formatação de preço em tempo real
        document.querySelector('input[name="preco"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.replace(/(\d+)(\d{2})$/, '$1.$2');
            }
            e.target.value = value;
        });

        // Preview da imagem
        document.querySelector('input[name="imagem"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const existingPreview = document.querySelector('.preview-image');
                    if (existingPreview) {
                        existingPreview.src = e.target.result;
                    } else {
                        const preview = document.createElement('img');
                        preview.src = e.target.result;
                        preview.className = 'preview-image w-full h-48 object-cover rounded-lg border-2 border-gray-200 mt-2';
                        e.target.parentNode.appendChild(preview);
                    }
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>