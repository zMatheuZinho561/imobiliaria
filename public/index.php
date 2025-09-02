<?php
require_once '../includes/config.php';

// Configuração da paginação
$imoveis_por_pagina = 9;
$pagina_atual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_atual - 1) * $imoveis_por_pagina;

// Debug: verificar se há imóveis no banco
$debug_count = $pdo->query("SELECT COUNT(*) FROM imoveis")->fetchColumn();
echo "<!-- Total imóveis no banco: $debug_count -->";

// Debug: verificar estrutura da tabela
try {
    $debug_columns = $pdo->query("SHOW COLUMNS FROM imoveis")->fetchAll();
    echo "<!-- Colunas da tabela: " . implode(', ', array_column($debug_columns, 'Field')) . " -->";
} catch(Exception $e) {
    echo "<!-- Erro ao verificar colunas: " . $e->getMessage() . " -->";
}

// Verificar se as novas colunas existem
$column_exists = [];
try {
    $columns_check = $pdo->query("SHOW COLUMNS FROM imoveis")->fetchAll();
    $existing_columns = array_column($columns_check, 'Field');
    
    $column_exists['status'] = in_array('status', $existing_columns);
    $column_exists['tipo_negocio'] = in_array('tipo_negocio', $existing_columns);
    $column_exists['bairro'] = in_array('bairro', $existing_columns);
    $column_exists['tipo_imovel'] = in_array('tipo_imovel', $existing_columns);
    $column_exists['destaque'] = in_array('destaque', $existing_columns);
    
    echo "<!-- Colunas existentes: " . json_encode($column_exists) . " -->";
} catch(Exception $e) {
    echo "<!-- Erro ao verificar colunas: " . $e->getMessage() . " -->";
    $column_exists = [
        'status' => false,
        'tipo_negocio' => false,
        'bairro' => false,
        'tipo_imovel' => false,
        'destaque' => false
    ];
}

// Construir query com filtros
$where_conditions = ["1=1"];
$params = [];

// Adicionar filtro de status apenas se a coluna existir
if ($column_exists['status']) {
    if (empty($_GET['status_debug'])) {
        $where_conditions[] = "status IN ('ativo', 'disponivel')";
    }
}

// Filtros
if (!empty($_GET['cidade'])) {
    $where_conditions[] = "localizacao LIKE ?";
    $params[] = '%' . $_GET['cidade'] . '%';
}

if (!empty($_GET['tipo_negocio']) && $column_exists['tipo_negocio']) {
    $where_conditions[] = "tipo_negocio = ?";
    $params[] = $_GET['tipo_negocio'];
}

if (!empty($_GET['bairro']) && $column_exists['bairro']) {
    $where_conditions[] = "bairro LIKE ?";
    $params[] = '%' . $_GET['bairro'] . '%';
}

if (!empty($_GET['tipo_imovel'])) {
    if ($column_exists['tipo_imovel']) {
        $where_conditions[] = "tipo_imovel = ?";
        $params[] = $_GET['tipo_imovel'];
    } else {
        $where_conditions[] = "tipo = ?";
        $params[] = $_GET['tipo_imovel'];
    }
}

if (!empty($_GET['quartos'])) {
    $where_conditions[] = "quartos >= ?";
    $params[] = (int)$_GET['quartos'];
}

if (!empty($_GET['banheiros'])) {
    $where_conditions[] = "banheiros >= ?";
    $params[] = (int)$_GET['banheiros'];
}

if (!empty($_GET['garagem'])) {
    $where_conditions[] = "garagem >= ?";
    $params[] = (int)$_GET['garagem'];
}

if (!empty($_GET['preco_min'])) {
    $where_conditions[] = "preco >= ?";
    $params[] = (float)$_GET['preco_min'];
}

if (!empty($_GET['preco_max'])) {
    $where_conditions[] = "preco <= ?";
    $params[] = (float)$_GET['preco_max'];
}

$where_clause = implode(' AND ', $where_conditions);

// Contar total de resultados para paginação
$count_sql = "SELECT COUNT(*) FROM imoveis WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_imoveis = $count_stmt->fetchColumn();
$total_paginas = ceil($total_imoveis / $imoveis_por_pagina);

// Construir ORDER BY baseado nas colunas disponíveis
$order_by = "id DESC";
if ($column_exists['destaque']) {
    $order_by = "COALESCE(destaque, 0) DESC, id DESC";
}

$sql = "SELECT * FROM imoveis WHERE $where_clause ORDER BY $order_by LIMIT $imoveis_por_pagina OFFSET $offset";
echo "<!-- SQL Query: $sql -->";
echo "<!-- Total: $total_imoveis, Páginas: $total_paginas, Página atual: $pagina_atual -->";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<!-- Imóveis encontrados: " . count($imoveis) . " -->";
} catch(Exception $e) {
    echo "<!-- Erro na query: " . $e->getMessage() . " -->";
    $imoveis = [];
}

// Buscar dados únicos para filtros
try {
    $cidades_query = "SELECT DISTINCT localizacao FROM imoveis WHERE localizacao IS NOT NULL AND localizacao != ''";
    if ($column_exists['status']) {
        $cidades_query .= " AND status IN ('ativo', 'disponivel')";
    }
    $cidades_query .= " ORDER BY localizacao";
    $cidades = $pdo->query($cidades_query)->fetchAll(PDO::FETCH_COLUMN);
    
    if ($column_exists['bairro']) {
        $bairros_query = "SELECT DISTINCT bairro FROM imoveis WHERE bairro IS NOT NULL AND bairro != ''";
        if ($column_exists['status']) {
            $bairros_query .= " AND status IN ('ativo', 'disponivel')";
        }
        $bairros_query .= " ORDER BY bairro";
        $bairros = $pdo->query($bairros_query)->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $bairros = [];
    }
    
    if ($column_exists['tipo_imovel']) {
        $tipos_query = "SELECT DISTINCT tipo_imovel FROM imoveis WHERE tipo_imovel IS NOT NULL AND tipo_imovel != ''";
        if ($column_exists['status']) {
            $tipos_query .= " AND status IN ('ativo', 'disponivel')";
        }
        $tipos_query .= " ORDER BY tipo_imovel";
        $tipos_imovel = $pdo->query($tipos_query)->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $tipos_query = "SELECT DISTINCT tipo FROM imoveis WHERE tipo IS NOT NULL AND tipo != ''";
        if ($column_exists['status']) {
            $tipos_query .= " AND status IN ('ativo', 'disponivel')";
        }
        $tipos_query .= " ORDER BY tipo";
        $tipos_imovel = $pdo->query($tipos_query)->fetchAll(PDO::FETCH_COLUMN);
    }
} catch(Exception $e) {
    echo "<!-- Erro ao buscar dados para filtros: " . $e->getMessage() . " -->";
    $cidades = [];
    $bairros = [];
    $tipos_imovel = [];
}

// Função para manter parâmetros GET na paginação
function build_pagination_url($pagina, $params_to_exclude = ['pagina']) {
    $current_params = $_GET;
    foreach ($params_to_exclude as $param) {
        unset($current_params[$param]);
    }
    $current_params['pagina'] = $pagina;
    return '?' . http_build_query($current_params);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imobiliária - Encontre seu imóvel ideal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .filter-card { backdrop-filter: blur(10px); }
        .glass-effect { backdrop-filter: blur(16px) saturate(180%); }
        .animate-fade-in { animation: fadeIn 0.6s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .hover-lift:hover { transform: translateY(-8px); }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-md shadow-lg sticky top-0 z-50 border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            <i class="fas fa-home mr-2 text-blue-600"></i>Imobiliária Premium
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="home.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Início
                    </a>
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Imóveis
                    </a>
                    <a href="../admin/login.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all transform hover:scale-105">
                        <i class="fas fa-user mr-1"></i>Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section com Filtros -->
    <div class="relative bg-gradient-to-r from-blue-600 via-indigo-700 to-purple-700 py-20">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 animate-fade-in">
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight">
                    Encontre seu 
                    <span class="bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent">
                        imóvel ideal
                    </span>
                </h1>
                <p class="text-xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
                    Descubra as melhores oportunidades de imóveis em Cambé e região com nossa plataforma premium
                </p>
            </div>

            <!-- Filtros Aprimorados -->
            <div class="glass-effect bg-white/15 rounded-3xl p-8 filter-card border border-white/20 shadow-2xl">
                <form method="GET" action="" class="space-y-6">
                    <?php if (isset($_GET['debug'])): ?>
                        <input type="hidden" name="debug" value="1">
                    <?php endif; ?>
                    <?php if (isset($_GET['status_debug'])): ?>
                        <input type="hidden" name="status_debug" value="1">
                    <?php endif; ?>
                    
                    <!-- Filtros Principais -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <label class="block text-white text-sm font-semibold mb-2 flex items-center">
                                <i class="fas fa-map-marker-alt mr-2 text-yellow-400"></i>Cidade
                            </label>
                            <select name="cidade" class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm focus:bg-white focus:ring-2 focus:ring-yellow-400 transition-all text-gray-800 font-medium">
                                <option value="">🏙️ Todas as cidades</option>
                                <?php foreach($cidades as $cidade): ?>
                                    <option value="<?= htmlspecialchars($cidade) ?>" <?= ($_GET['localizacao'] ?? '') == $cidade ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cidade) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-white text-sm font-semibold mb-2 flex items-center">
                                <i class="fas fa-handshake mr-2 text-green-400"></i>Tipo de negócio
                            </label>
                            <select name="tipo_negocio" class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm focus:bg-white focus:ring-2 focus:ring-green-400 transition-all text-gray-800 font-medium">
                                <option value="">💼 Venda e Aluguel</option>
                                <option value="venda" <?= ($_GET['tipo_negocio'] ?? '') == 'venda' ? 'selected' : '' ?>>💰 Venda</option>
                                <option value="aluguel" <?= ($_GET['tipo_negocio'] ?? '') == 'aluguel' ? 'selected' : '' ?>>🏠 Aluguel</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-white text-sm font-semibold mb-2 flex items-center">
                                <i class="fas fa-location-dot mr-2 text-purple-400"></i>Bairro
                            </label>
                            <?php if ($column_exists['bairro'] && !empty($bairros)): ?>
                                <select name="bairro" class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm focus:bg-white focus:ring-2 focus:ring-purple-400 transition-all text-gray-800 font-medium">
                                    <option value="">📍 Todos os bairros</option>
                                    <?php foreach($bairros as $bairro): ?>
                                        <option value="<?= htmlspecialchars($bairro) ?>" <?= ($_GET['bairro'] ?? '') == $bairro ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($bairro) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" name="bairro" value="<?= htmlspecialchars($_GET['bairro'] ?? '') ?>" 
                                       placeholder="📍 Digite o bairro" 
                                       class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm focus:bg-white focus:ring-2 focus:ring-purple-400 transition-all text-gray-800 font-medium">
                            <?php endif; ?>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-white text-sm font-semibold mb-2 flex items-center">
                                <i class="fas fa-building mr-2 text-orange-400"></i>Tipo do Imóvel
                            </label>
                            <select name="tipo_imovel" class="w-full px-4 py-3 rounded-xl border-0 bg-white/90 backdrop-blur-sm focus:bg-white focus:ring-2 focus:ring-orange-400 transition-all text-gray-800 font-medium">
                                <option value="">🏘️ Todos os tipos</option>
                                <?php if (!empty($tipos_imovel)): ?>
                                    <?php foreach($tipos_imovel as $tipo): ?>
                                        <option value="<?= htmlspecialchars($tipo) ?>" <?= ($_GET['tipo_imovel'] ?? '') == $tipo ? 'selected' : '' ?>>
                                            <?= ucfirst(htmlspecialchars($tipo)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="casa" <?= ($_GET['tipo_imovel'] ?? '') == 'casa' ? 'selected' : '' ?>>🏠 Casa</option>
                                    <option value="apartamento" <?= ($_GET['tipo_imovel'] ?? '') == 'apartamento' ? 'selected' : '' ?>>🏢 Apartamento</option>
                                    <option value="sobrado" <?= ($_GET['tipo_imovel'] ?? '') == 'sobrado' ? 'selected' : '' ?>>🏘️ Sobrado</option>
                                    <option value="terreno" <?= ($_GET['tipo_imovel'] ?? '') == 'terreno' ? 'selected' : '' ?>>🗻 Terreno</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Filtros Adicionais -->
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 pt-6 border-t border-white/20">
                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">🛏️ Quartos</label>
                            <select name="quartos" class="w-full px-3 py-2 rounded-lg border-0 bg-white/90 focus:bg-white focus:ring-2 focus:ring-blue-300 transition-all font-medium">
                                <option value="">Qualquer</option>
                                <option value="1" <?= ($_GET['quartos'] ?? '') == '1' ? 'selected' : '' ?>>1+</option>
                                <option value="2" <?= ($_GET['quartos'] ?? '') == '2' ? 'selected' : '' ?>>2+</option>
                                <option value="3" <?= ($_GET['quartos'] ?? '') == '3' ? 'selected' : '' ?>>3+</option>
                                <option value="4" <?= ($_GET['quartos'] ?? '') == '4' ? 'selected' : '' ?>>4+</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">🚿 Banheiros</label>
                            <select name="banheiros" class="w-full px-3 py-2 rounded-lg border-0 bg-white/90 focus:bg-white focus:ring-2 focus:ring-blue-300 transition-all font-medium">
                                <option value="">Qualquer</option>
                                <option value="1" <?= ($_GET['banheiros'] ?? '') == '1' ? 'selected' : '' ?>>1+</option>
                                <option value="2" <?= ($_GET['banheiros'] ?? '') == '2' ? 'selected' : '' ?>>2+</option>
                                <option value="3" <?= ($_GET['banheiros'] ?? '') == '3' ? 'selected' : '' ?>>3+</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">🚗 Garagem</label>
                            <select name="garagem" class="w-full px-3 py-2 rounded-lg border-0 bg-white/90 focus:bg-white focus:ring-2 focus:ring-blue-300 transition-all font-medium">
                                <option value="">Qualquer</option>
                                <option value="1" <?= ($_GET['garagem'] ?? '') == '1' ? 'selected' : '' ?>>1+</option>
                                <option value="2" <?= ($_GET['garagem'] ?? '') == '2' ? 'selected' : '' ?>>2+</option>
                                <option value="3" <?= ($_GET['garagem'] ?? '') == '3' ? 'selected' : '' ?>>3+</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">💰 Preço Min.</label>
                            <input type="number" name="preco_min" value="<?= htmlspecialchars($_GET['preco_min'] ?? '') ?>" 
                                   placeholder="R$ 0" class="w-full px-3 py-2 rounded-lg border-0 bg-white/90 focus:bg-white focus:ring-2 focus:ring-blue-300 transition-all font-medium">
                        </div>

                        <div>
                            <label class="block text-white text-sm font-semibold mb-2">💎 Preço Máx.</label>
                            <input type="number" name="preco_max" value="<?= htmlspecialchars($_GET['preco_max'] ?? '') ?>" 
                                   placeholder="R$ ∞" class="w-full px-3 py-2 rounded-lg border-0 bg-white/90 focus:bg-white focus:ring-2 focus:ring-blue-300 transition-all font-medium">
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-bold py-4 px-8 rounded-xl transition-all transform hover:scale-105 shadow-lg">
                            <i class="fas fa-search mr-2"></i>Buscar Imóveis Premium
                        </button>
                        <a href="index.php<?= isset($_GET['debug']) ? '?debug' : '' ?>" class="flex-1 bg-white/20 hover:bg-white/30 text-white font-semibold py-4 px-8 rounded-xl text-center transition-all border border-white/30 backdrop-blur-sm">
                            <i class="fas fa-eraser mr-2"></i>Limpar Filtros
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Resultados -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Header dos Resultados -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-4">
            <div>
                <h2 class="text-4xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-2">
                    Imóveis Encontrados
                </h2>
                <p class="text-xl text-gray-600">
                    <span class="font-bold text-blue-600"><?= $total_imoveis ?></span> propriedades disponíveis
                    <?php if($pagina_atual > 1 || $total_paginas > 1): ?>
                        • Página <span class="font-bold text-blue-600"><?= $pagina_atual ?></span> de <span class="font-bold text-blue-600"><?= $total_paginas ?></span>
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if($total_imoveis > 0): ?>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">Mostrando</span>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-semibold">
                        <?= min($offset + 1, $total_imoveis) ?>-<?= min($offset + $imoveis_por_pagina, $total_imoveis) ?>
                    </span>
                    <span class="text-sm text-gray-500">de <?= $total_imoveis ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Grid de Imóveis -->
        <?php if (empty($imoveis)): ?>
            <div class="text-center py-20">
                <div class="glass-effect bg-white/60 backdrop-blur-md rounded-3xl p-12 max-w-2xl mx-auto border border-white/20">
                    <i class="fas fa-search text-8xl text-gray-300 mb-6"></i>
                    <h3 class="text-3xl font-bold text-gray-700 mb-4">Nenhum imóvel encontrado</h3>
                    <p class="text-gray-600 mb-8 text-lg leading-relaxed">
                        <?php if ($debug_count > 0): ?>
                            Existem <?= $debug_count ?> imóveis no banco, mas nenhum atende aos filtros aplicados.
                        <?php else: ?>
                            Não há imóveis cadastrados no sistema no momento.
                        <?php endif ?>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="index.php<?= isset($_GET['debug']) ? '?debug' : '' ?>" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-4 rounded-xl inline-flex items-center justify-center transition-all transform hover:scale-105 font-semibold">
                            <i class="fas fa-home mr-2"></i>Ver Todos os Imóveis
                        </a>
                        <?php if (!isset($_GET['debug'])): ?>
                            <a href="?debug" class="bg-yellow-500 hover:bg-yellow-600 text-black px-8 py-4 rounded-xl inline-flex items-center justify-center transition-all transform hover:scale-105 font-semibold">
                                <i class="fas fa-bug mr-2"></i>Modo Debug
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                <?php foreach($imoveis as $index => $imovel): ?>
                    <div class="bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 overflow-hidden group hover-lift animate-fade-in border border-gray-100" style="animation-delay: <?= $index * 0.1 ?>s">
                        <!-- Imagem -->
                        <div class="relative overflow-hidden">
                            <?php if(!empty($imovel['imagem'])): ?>
                                <img src="uploads/<?= htmlspecialchars($imovel['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($imovel['titulo']) ?>" 
                                     class="w-full h-64 object-cover group-hover:scale-110 transition-transform duration-700"
                                     onerror="this.parentElement.innerHTML='<div class=\'w-full h-64 bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center\'><i class=\'fas fa-home text-5xl text-gray-400\'></i></div>'">
                            <?php else: ?>
                                <div class="w-full h-64 bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                    <i class="fas fa-home text-5xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badges Melhorados -->
                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                <span class="<?= ($column_exists['tipo_negocio'] && ($imovel['tipo_negocio'] ?? 'venda') == 'aluguel') ? 'bg-gradient-to-r from-orange-500 to-red-500' : 'bg-gradient-to-r from-green-500 to-emerald-500' ?> text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg backdrop-blur-sm">
                                    <?php 
                                    if ($column_exists['tipo_negocio']) {
                                        echo ($imovel['tipo_negocio'] ?? 'venda') == 'venda' ? '💰 VENDA' : '🏠 ALUGUEL';
                                    } else {
                                        echo '💰 VENDA';
                                    }
                                    ?>
                                </span>
                                
                                <?php 
                                $tipo_display = '';
                                if ($column_exists['tipo_imovel'] && !empty($imovel['tipo_imovel'])) {
                                    $tipo_display = $imovel['tipo_imovel'];
                                } elseif (!empty($imovel['tipo'])) {
                                    $tipo_display = $imovel['tipo'];
                                }
                                ?>
                                <?php if($tipo_display): ?>
                                    <span class="bg-black/80 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg backdrop-blur-sm">
                                        <?php
                                        $icons = [
                                            'casa' => '🏠',
                                            'apartamento' => '🏢',
                                            'sobrado' => '🏘️',
                                            'terreno' => '🗻',
                                            'chacara' => '🌾'
                                        ];
                                        $icon = $icons[strtolower($tipo_display)] ?? '🏠';
                                        echo $icon . ' ' . strtoupper(htmlspecialchars($tipo_display));
                                        ?>
                                    </span>
                                <?php endif; ?>
                                
                                <?php if($column_exists['destaque'] && !empty($imovel['destaque']) && $imovel['destaque']): ?>
                                    <span class="bg-gradient-to-r from-yellow-400 to-orange-400 text-black px-4 py-2 rounded-full text-sm font-bold shadow-lg animate-pulse">
                                        ⭐ DESTAQUE
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- WhatsApp Button Melhorado -->
                            <div class="absolute top-4 right-4">
                                <a href="https://wa.me/5511999999999?text=Olá! Tenho interesse no imóvel: <?= urlencode($imovel['titulo']) ?>" 
                                   target="_blank"
                                   class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white w-12 h-12 rounded-full flex items-center justify-center transition-all transform hover:scale-110 shadow-lg backdrop-blur-sm">
                                    <i class="fab fa-whatsapp text-xl"></i>
                                </a>
                            </div>

                            <!-- Overlay gradiente -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </div>

                        <!-- Conteúdo do Card -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                <?= htmlspecialchars($imovel['titulo']) ?>
                            </h3>
                            
                            <div class="flex items-center text-gray-600 mb-4">
                                <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
                                <span class="text-sm font-medium">
                                    <?php 
                                    $localizacao_display = '';
                                    if ($column_exists['bairro'] && !empty($imovel['bairro'])) {
                                        $localizacao_display = $imovel['bairro'];
                                        if (!empty($imovel['localizacao'])) {
                                            $localizacao_display .= ' - ' . $imovel['localizacao'];
                                        }
                                    } else {
                                        $localizacao_display = $imovel['localizacao'] ?? 'Localização não informada';
                                    }
                                    echo htmlspecialchars($localizacao_display);
                                    ?>
                                </span>
                            </div>

                            <div class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-6">
                                R$ <?= number_format($imovel['preco'], 2, ',', '.') ?>
                                <span class="text-sm text-gray-500 font-normal">
                                    <?php 
                                    if ($column_exists['tipo_negocio']) {
                                        echo ($imovel['tipo_negocio'] ?? 'venda') == 'aluguel' ? '/mês' : '';
                                    }
                                    ?>
                                </span>
                            </div>

                            <!-- Detalhes Melhorados -->
                            <div class="grid grid-cols-4 gap-3 mb-6">
                                <div class="text-center bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-3 border border-blue-100">
                                    <div class="text-blue-600 text-xs mb-1 font-semibold">Quartos</div>
                                    <div class="flex items-center justify-center">
                                        <i class="fas fa-bed text-blue-500 mr-1"></i>
                                        <span class="font-bold text-lg"><?= $imovel['quartos'] ?? '—' ?></span>
                                    </div>
                                </div>
                                <div class="text-center bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-3 border border-green-100">
                                    <div class="text-green-600 text-xs mb-1 font-semibold">Banheiros</div>
                                    <div class="flex items-center justify-center">
                                        <i class="fas fa-bath text-green-500 mr-1"></i>
                                        <span class="font-bold text-lg"><?= $imovel['banheiros'] ?? '—' ?></span>
                                    </div>
                                </div>
                                <div class="text-center bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl p-3 border border-purple-100">
                                    <div class="text-purple-600 text-xs mb-1 font-semibold">Garagem</div>
                                    <div class="flex items-center justify-center">
                                        <i class="fas fa-car text-purple-500 mr-1"></i>
                                        <span class="font-bold text-lg"><?= $imovel['garagem'] ?? '—' ?></span>
                                    </div>
                                </div>
                                <div class="text-center bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-3 border border-orange-100">
                                    <div class="text-orange-600 text-xs mb-1 font-semibold">Área</div>
                                    <div class="flex items-center justify-center">
                                        <i class="fas fa-ruler-combined text-orange-500 mr-1"></i>
                                        <span class="font-bold text-sm"><?= $imovel['area'] ?? '—' ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Botão Ver Detalhes Melhorado -->
                            <a href="imovel.php?id=<?= $imovel['id'] ?>" 
                               class="block w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700 text-white text-center py-4 rounded-2xl font-bold transition-all duration-300 transform hover:scale-105 shadow-lg group">
                                <i class="fas fa-eye mr-2 group-hover:animate-pulse"></i>Ver Detalhes Premium
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginação Premium -->
            <?php if ($total_paginas > 1): ?>
                <div class="flex justify-center items-center space-x-2 mt-12">
                    <div class="bg-white/80 backdrop-blur-md rounded-2xl p-4 shadow-lg border border-white/20">
                        <div class="flex items-center space-x-2">
                            <!-- Primeira página -->
                            <?php if ($pagina_atual > 1): ?>
                                <a href="<?= build_pagination_url(1) ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-blue-500 hover:text-white transition-all text-gray-600 font-semibold">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="<?= build_pagination_url($pagina_atual - 1) ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-blue-500 hover:text-white transition-all text-gray-600 font-semibold">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php endif; ?>

                            <!-- Páginas numeradas -->
                            <?php
                            $inicio = max(1, $pagina_atual - 2);
                            $fim = min($total_paginas, $pagina_atual + 2);
                            
                            if ($inicio > 1): ?>
                                <span class="px-2 text-gray-400">...</span>
                            <?php endif;
                            
                            for ($i = $inicio; $i <= $fim; $i++): ?>
                                <a href="<?= build_pagination_url($i) ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-xl font-bold transition-all
                                          <?= $i == $pagina_atual 
                                              ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-lg' 
                                              : 'bg-gray-100 hover:bg-blue-500 hover:text-white text-gray-600' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor;
                            
                            if ($fim < $total_paginas): ?>
                                <span class="px-2 text-gray-400">...</span>
                            <?php endif; ?>

                            <!-- Última página -->
                            <?php if ($pagina_atual < $total_paginas): ?>
                                <a href="<?= build_pagination_url($pagina_atual + 1) ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-blue-500 hover:text-white transition-all text-gray-600 font-semibold">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="<?= build_pagination_url($total_paginas) ?>" 
                                   class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 hover:bg-blue-500 hover:text-white transition-all text-gray-600 font-semibold">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Info da paginação -->
                <div class="text-center mt-6">
                    <p class="text-gray-600">
                        Mostrando <span class="font-semibold text-blue-600"><?= min($offset + 1, $total_imoveis) ?></span> 
                        a <span class="font-semibold text-blue-600"><?= min($offset + $imoveis_por_pagina, $total_imoveis) ?></span> 
                        de <span class="font-semibold text-blue-600"><?= $total_imoveis ?></span> imóveis
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer Premium -->
    <footer class="bg-gradient-to-r from-gray-900 via-blue-900 to-indigo-900 text-white py-16 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div class="space-y-4">
                    <h3 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">
                        <i class="fas fa-home mr-2 text-blue-400"></i>Imobiliária Premium
                    </h3>
                    <p class="text-gray-300 leading-relaxed">
                        Encontre seu imóvel ideal com a melhor experiência do mercado imobiliário. 
                        Excelência em cada negócio.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-blue-600 hover:bg-blue-500 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-pink-600 hover:bg-pink-500 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-green-600 hover:bg-green-500 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-blue-300">Contato</h4>
                    <div class="space-y-3 text-gray-300">
                        <p class="flex items-center"><i class="fas fa-phone mr-3 text-blue-400"></i>(43) 9999-9999</p>
                        <p class="flex items-center"><i class="fas fa-envelope mr-3 text-blue-400"></i>contato@imobiliaria.com</p>
                        <p class="flex items-center"><i class="fas fa-map-marker-alt mr-3 text-blue-400"></i>Cambé - PR</p>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-blue-300">Serviços</h4>
                    <div class="space-y-3">
                        <a href="#" class="block text-gray-300 hover:text-white transition-colors">Compra e Venda</a>
                        <a href="#" class="block text-gray-300 hover:text-white transition-colors">Locação</a>
                        <a href="#" class="block text-gray-300 hover:text-white transition-colors">Avaliação</a>
                        <a href="#" class="block text-gray-300 hover:text-white transition-colors">Consultoria</a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-blue-300">Institucional</h4>
                    <div class="space-y-3">
                        <a href="#" class="block text-gray-300 hover:text-white transition-colors">Sobre Nós</a>
                        <a href="#" class="block text-gray-300 hover:text-white transition-colors">Nossa Equipe</a>
                        <a href="#" class="block text-gray-300 hover:text-white transition-colors">Trabalhe Conosco</a>
                        <a href="#" class="block text-gray-300 hover:text-white transition-colors">Política de Privacidade</a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; 2024 Imobiliária Premium. Todos os direitos reservados. 
                    <span class="text-blue-400">Feito com ❤️ para você encontrar seu lar ideal.</span>
                </p>
            </div>
        </div>
    </footer>

</body>
</html>