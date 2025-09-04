<?php
require_once "../includes/config.php";
// Verifica se o usuário está logado e é admin
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_role"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

// Busca todos os imóveis com filtros
$where = "1=1";
$params = [];

// filtro de busca
if (!empty($_GET['busca'])) {
    $where .= " AND (titulo LIKE :busca1 OR localizacao LIKE :busca2 OR descricao LIKE :busca3)";
    $params[':busca1'] = "%" . $_GET['busca'] . "%";
    $params[':busca2'] = "%" . $_GET['busca'] . "%";
    $params[':busca3'] = "%" . $_GET['busca'] . "%";
}

// filtro de status
if (!empty($_GET['status'])) {
    $where .= " AND status = :status";
    $params[':status'] = $_GET['status'];
}

// filtro de tipo
if (!empty($_GET['tipo'])) {
    $where .= " AND tipo = :tipo";
    $params[':tipo'] = $_GET['tipo'];
}

// filtro de preço
if (!empty($_GET['preco_min'])) {
    $where .= " AND preco >= :preco_min";
    $params[':preco_min'] = $_GET['preco_min'];
}

if (!empty($_GET['preco_max'])) {
    $where .= " AND preco <= :preco_max";
    $params[':preco_max'] = $_GET['preco_max'];
}

// Paginação
$items_per_page = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// Contar total de itens
$count_sql = "SELECT COUNT(*) FROM imoveis WHERE $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

$sql = "SELECT * FROM imoveis WHERE $where ORDER BY id DESC LIMIT $items_per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca tipos únicos para o filtro
$stmt_tipos = $pdo->query("SELECT DISTINCT tipo FROM imoveis WHERE tipo IS NOT NULL ORDER BY tipo");
$tipos = $stmt_tipos->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Imóveis - Painel Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        }
        .glass-effect { 
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.125);
        }
        .animate-fade-in { 
            animation: fadeIn 0.6s ease-in-out; 
        }
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(20px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        .hover-lift:hover { 
            transform: translateY(-4px); 
        }
        .sidebar-item.active {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
    </style>
    <script>
        function confirmarExclusao(id, titulo) {
            if (confirm(`Tem certeza que deseja excluir o imóvel "${titulo}"?\n\nEsta ação não pode ser desfeita.`)) {
                window.location.href = `delete_imovel.php?id=${id}`;
            }
        }

        function toggleView() {
            const grid = document.getElementById('grid-view');
            const table = document.getElementById('table-view');
            const gridBtn = document.getElementById('grid-btn');
            const tableBtn = document.getElementById('table-btn');
            
            if (grid.classList.contains('hidden')) {
                grid.classList.remove('hidden');
                table.classList.add('hidden');
                gridBtn.classList.add('bg-indigo-500', 'text-white');
                gridBtn.classList.remove('bg-gray-200', 'text-gray-700');
                tableBtn.classList.remove('bg-indigo-500', 'text-white');
                tableBtn.classList.add('bg-gray-200', 'text-gray-700');
                localStorage.setItem('viewMode', 'grid');
            } else {
                grid.classList.add('hidden');
                table.classList.remove('hidden');
                tableBtn.classList.add('bg-indigo-500', 'text-white');
                tableBtn.classList.remove('bg-gray-200', 'text-gray-700');
                gridBtn.classList.remove('bg-indigo-500', 'text-white');
                gridBtn.classList.add('bg-gray-200', 'text-gray-700');
                localStorage.setItem('viewMode', 'table');
            }
        }

        function limparFiltros() {
            window.location.href = 'imoveis.php';
        }

        // Restaurar modo de visualização
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('viewMode');
            if (savedView === 'table') {
                toggleView();
            }
        });

        function alterarStatus(id, novoStatus) {
            if (confirm(`Confirma alterar o status para "${novoStatus}"?`)) {
                // Criar formulário para enviar dados
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'alterar_status_imovel.php';
                form.innerHTML = `
                    <input type="hidden" name="id" value="${id}">
                    <input type="hidden" name="status" value="${novoStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100 min-h-screen">

    <!-- Sidebar -->
    <aside class="fixed left-0 top-0 w-72 h-full gradient-bg text-white shadow-2xl z-40">
        <!-- Header da Sidebar -->
        <div class="p-6 border-b border-white/20">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-home text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="font-bold text-xl">Imobiliária</h1>
                    <p class="text-blue-200 text-sm">Painel Premium</p>
                </div>
            </div>
        </div>

        <!-- Navegação -->
        <nav class="flex-1 p-4 space-y-2">
            <a href="dashboard.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-chart-line w-5 text-center"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="imoveis.php" class="sidebar-item active flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-building w-5 text-center"></i>
                <span class="font-medium">Imóveis</span>
            </a>
            <a href="solicitacao.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-file-alt w-5 text-center"></i>
                <span class="font-medium">Solicitações</span>
            </a>
            <a href="contatos.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-envelope w-5 text-center"></i>
                <span class="font-medium">Contatos</span>
            </a>
            <a href="relatorios.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-chart-bar w-5 text-center"></i>
                <span class="font-medium">Relatórios</span>
            </a>
            <a href="../public/index.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-external-link-alt w-5 text-center"></i>
                <span class="font-medium">Ver Site</span>
            </a>
        </nav>

        <!-- Perfil do Usuário -->
        <div class="p-4 border-t border-white/20 mt-auto">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold truncate">Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></p>
                    <p class="text-blue-200 text-sm">Administrador</p>
                </div>
            </div>
            <a href="../public/logout.php" 
               class="w-full bg-red-500/80 hover:bg-red-500 text-white px-4 py-2 rounded-lg text-center block transition-all backdrop-blur-sm">
                <i class="fas fa-sign-out-alt mr-2"></i>Sair
            </a>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <main class="ml-72 min-h-screen">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-md shadow-lg sticky top-0 z-30 border-b border-white/20">
            <div class="px-8 py-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Gerenciar Imóveis
                        </h1>
                        <p class="text-gray-600 mt-1">
                            <?= $total_items ?> imóveis encontrados
                            <?php if ($total_pages > 1): ?>
                                • Página <?= $page ?> de <?= $total_pages ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="imovel_form.php" 
                       class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition-all duration-300 transform hover:scale-105 flex items-center gap-2 w-fit">
                        <i class="fas fa-plus"></i>
                        Novo Imóvel
                    </a>
                </div>
            </div>
        </header>

        <div class="p-8 space-y-8">
            <!-- Filtros Avançados -->
            <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                <div class="flex items-center gap-2 mb-6">
                    <i class="fas fa-filter text-indigo-500"></i>
                    <h2 class="text-xl font-bold text-gray-800">Filtros Avançados</h2>
                </div>
                
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <div class="relative">
                            <input type="text" name="busca" value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>" 
                                   placeholder="Título, localização ou descrição..." 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white/50 backdrop-blur-sm">
                            <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white/50 backdrop-blur-sm">
                            <option value="">Todos os tipos</option>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo) ?>" <?= ($_GET['tipo'] ?? '') === $tipo ? 'selected' : '' ?>>
                                    <?= ucfirst(htmlspecialchars($tipo)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white/50 backdrop-blur-sm">
                            <option value="">Todos os status</option>
                            <option value="disponivel" <?= ($_GET['status'] ?? '') === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                            <option value="vendido" <?= ($_GET['status'] ?? '') === 'vendido' ? 'selected' : '' ?>>Vendido</option>
                            <option value="alugado" <?= ($_GET['status'] ?? '') === 'alugado' ? 'selected' : '' ?>>Alugado</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preço Min.</label>
                        <input type="number" name="preco_min" value="<?= htmlspecialchars($_GET['preco_min'] ?? '') ?>" 
                               placeholder="R$ 0" 
                               class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white/50 backdrop-blur-sm">
                    </div>
                    
                    <div class="lg:col-span-5 flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preço Máx.</label>
                            <input type="number" name="preco_max" value="<?= htmlspecialchars($_GET['preco_max'] ?? '') ?>" 
                                   placeholder="R$ ∞" 
                                   class="w-full py-3 px-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white/50 backdrop-blur-sm">
                        </div>
                        
                        <div class="flex items-end gap-3">
                            <button type="submit" 
                                    class="bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white px-6 py-3 rounded-xl transition-all duration-300 transform hover:scale-105 font-semibold shadow-lg flex items-center gap-2">
                                <i class="fas fa-search"></i>
                                Filtrar
                            </button>
                            <button type="button" onclick="limparFiltros()" 
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-xl transition-all duration-300 font-semibold flex items-center gap-2">
                                <i class="fas fa-times"></i>
                                Limpar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Controles de Visualização -->
            <div class="bg-white/80 backdrop-blur-md p-4 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-gray-600">Visualização:</span>
                        <div class="flex rounded-xl overflow-hidden border border-gray-200 bg-gray-100">
                            <button id="grid-btn" onclick="toggleView()" 
                                    class="px-4 py-2 bg-indigo-500 text-white transition-all duration-300 flex items-center gap-2">
                                <i class="fas fa-th-large"></i>
                                <span class="hidden sm:inline">Cards</span>
                            </button>
                            <button id="table-btn" onclick="toggleView()" 
                                    class="px-4 py-2 bg-gray-200 text-gray-700 transition-all duration-300 flex items-center gap-2">
                                <i class="fas fa-list"></i>
                                <span class="hidden sm:inline">Tabela</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span>Disponível</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span>Vendido</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span>Alugado</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visualização em Cards -->
            <div id="grid-view" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-fade-in">
                <?php if (empty($imoveis)): ?>
                    <div class="col-span-full bg-white/80 backdrop-blur-md rounded-2xl shadow-lg p-16 text-center border border-white/20">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-home text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-600 mb-4">Nenhum imóvel encontrado</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">
                            Não há imóveis que correspondam aos filtros aplicados. Tente ajustar os critérios de busca.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="imovel_form.php" 
                               class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white px-6 py-3 rounded-xl font-semibold transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-plus mr-2"></i>Cadastrar Primeiro Imóvel
                            </a>
                            <button onclick="limparFiltros()" 
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all">
                                <i class="fas fa-filter mr-2"></i>Limpar Filtros
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($imoveis as $imovel): ?>
                        <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden group hover-lift border border-white/20">
                            <!-- Imagem -->
                            <div class="relative overflow-hidden h-48">
                                <?php if (!empty($imovel['imagem'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($imovel['imagem']) ?>" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                         onerror="this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center\'><i class=\'fas fa-image text-4xl text-gray-400\'></i></div>'">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                        <i class="fas fa-image text-4xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Status Badge -->
                                <div class="absolute top-3 right-3">
                                    <?php
                                    $statusStyles = [
                                        'disponivel' => 'bg-gradient-to-r from-green-500 to-emerald-500',
                                        'vendido' => 'bg-gradient-to-r from-red-500 to-rose-500',
                                        'alugado' => 'bg-gradient-to-r from-blue-500 to-indigo-500'
                                    ];
                                    $statusStyle = $statusStyles[$imovel['status']] ?? 'bg-gradient-to-r from-gray-500 to-gray-600';
                                    ?>
                                    <span class="<?= $statusStyle ?> text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg backdrop-blur-sm">
                                        <?= strtoupper(htmlspecialchars($imovel['status'])) ?>
                                    </span>
                                </div>
                                
                                <!-- Visualizações -->
                                <?php if (!empty($imovel['visualizacoes']) && $imovel['visualizacoes'] > 0): ?>
                                    <div class="absolute top-3 left-3">
                                        <span class="bg-black/70 text-white px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-sm flex items-center gap-1">
                                            <i class="fas fa-eye"></i>
                                            <?= $imovel['visualizacoes'] ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Conteúdo -->
                            <div class="p-6">
                                <h3 class="text-lg font-bold mb-2 text-gray-800 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                                    <?= htmlspecialchars($imovel['titulo']) ?>
                                </h3>
                                
                                <div class="flex items-center text-gray-600 mb-3">
                                    <i class="fas fa-map-marker-alt mr-2 text-indigo-500"></i>
                                    <span class="text-sm truncate"><?= htmlspecialchars($imovel['localizacao'] ?? $imovel['endereco'] ?? 'Endereço não informado') ?></span>
                                </div>
                                
                                <p class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent mb-4">
                                    R$ <?= number_format($imovel['preco'], 2, ',', '.') ?>
                                </p>
                                
                                <!-- Características -->
                                <div class="grid grid-cols-4 gap-2 mb-6 text-xs">
                                    <div class="text-center bg-gray-50 rounded-lg py-2">
                                        <i class="fas fa-expand-arrows-alt text-gray-500 mb-1"></i>
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($imovel['area'] ?? $imovel['area_total'] ?? '0') ?></div>
                                        <div class="text-gray-500">m²</div>
                                    </div>
                                    <div class="text-center bg-gray-50 rounded-lg py-2">
                                        <i class="fas fa-bed text-gray-500 mb-1"></i>
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($imovel['quartos'] ?? '0') ?></div>
                                        <div class="text-gray-500">quartos</div>
                                    </div>
                                    <div class="text-center bg-gray-50 rounded-lg py-2">
                                        <i class="fas fa-bath text-gray-500 mb-1"></i>
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($imovel['banheiros'] ?? '0') ?></div>
                                        <div class="text-gray-500">banh.</div>
                                    </div>
                                    <div class="text-center bg-gray-50 rounded-lg py-2">
                                        <i class="fas fa-car text-gray-500 mb-1"></i>
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($imovel['garagem'] ?? $imovel['vagas'] ?? '0') ?></div>
                                        <div class="text-gray-500">vagas</div>
                                    </div>
                                </div>
                                
                                <!-- Ações -->
                                <div class="flex gap-2">
                                    <a href="../public/imovel.php?id=<?= $imovel['id'] ?>" target="_blank" 
                                       class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-all text-center text-sm font-medium flex items-center justify-center gap-1">
                                        <i class="fas fa-external-link-alt"></i>
                                        Ver
                                    </a>
                                    <a href="imovel_form.php?id=<?= $imovel['id'] ?>" 
                                       class="flex-1 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white px-4 py-2 rounded-lg transition-all text-center text-sm font-medium flex items-center justify-center gap-1">
                                        <i class="fas fa-edit"></i>
                                        Editar
                                    </a>
                                    <button onclick="confirmarExclusao(<?= $imovel['id'] ?>, '<?= htmlspecialchars(addslashes($imovel['titulo'])) ?>')" 
                                            class="flex-1 bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white px-4 py-2 rounded-lg transition-all text-sm font-medium flex items-center justify-center gap-1">
                                        <i class="fas fa-trash"></i>
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Visualização em Tabela -->
            <div id="table-view" class="hidden bg-white/80 backdrop-blur-md rounded-2xl shadow-lg overflow-hidden border border-white/20 animate-fade-in">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-indigo-50 to-purple-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Imóvel</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Preço</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Detalhes</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Views</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (empty($imoveis)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <i class="fas fa-home text-2xl text-gray-400"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-600 mb-2">Nenhum imóvel encontrado</h3>
                                            <p class="text-gray-500 mb-6">Não há imóveis que correspondam aos filtros aplicados.</p>
                                            <div class="flex gap-3">
                                                <a href="imovel_form.php" 
                                                   class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-all">
                                                    <i class="fas fa-plus mr-2"></i>Novo Imóvel
                                                </a>
                                                <button onclick="limparFiltros()" 
                                                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium transition-all">
                                                    <i class="fas fa-filter mr-2"></i>Limpar Filtros
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($imoveis as $imovel): ?>
                                    <tr class="hover:bg-indigo-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <?php if (!empty($imovel['imagem'])): ?>
                                                    <img src="uploads/<?= htmlspecialchars($imovel['imagem']) ?>" 
                                                         class="w-20 h-16 object-cover rounded-xl mr-4 shadow-md"
                                                         onerror="this.parentElement.innerHTML='<div class=\'w-20 h-16 bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl mr-4 flex items-center justify-center shadow-md\'><i class=\'fas fa-home text-gray-400\'></i></div><div class=\'min-w-0 flex-1\'>' + this.nextElementSibling.innerHTML + '</div>';">
                                                <?php else: ?>
                                                    <div class="w-20 h-16 bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl mr-4 flex items-center justify-center shadow-md">
                                                        <i class="fas fa-home text-gray-400"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-semibold text-gray-900 truncate max-w-xs" title="<?= htmlspecialchars($imovel['titulo']) ?>">
                                                        <?= htmlspecialchars($imovel['titulo']) ?>
                                                    </div>
                                                    <div class="text-sm text-gray-600 truncate max-w-xs flex items-center gap-1" title="<?= htmlspecialchars($imovel['localizacao'] ?? $imovel['endereco'] ?? '') ?>">
                                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                                        <?= htmlspecialchars($imovel['localizacao'] ?? $imovel['endereco'] ?? 'Endereço não informado') ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">
                                                <?= ucfirst(htmlspecialchars($imovel['tipo'] ?? 'N/A')) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-lg font-bold text-green-600">
                                                R$ <?= number_format($imovel['preco'], 2, ',', '.') ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            $statusStyles = [
                                                'disponivel' => 'bg-green-100 text-green-800 border-green-200',
                                                'vendido' => 'bg-red-100 text-red-800 border-red-200',
                                                'alugado' => 'bg-blue-100 text-blue-800 border-blue-200'
                                            ];
                                            $statusStyle = $statusStyles[$imovel['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                            ?>
                                            <div class="relative">
                                                <select onchange="alterarStatus(<?= $imovel['id'] ?>, this.value)" 
                                                        class="<?= $statusStyle ?> px-3 py-1 rounded-full text-sm font-semibold border appearance-none cursor-pointer bg-transparent">
                                                    <option value="disponivel" <?= $imovel['status'] === 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                                                    <option value="vendido" <?= $imovel['status'] === 'vendido' ? 'selected' : '' ?>>Vendido</option>
                                                    <option value="alugado" <?= $imovel['status'] === 'alugado' ? 'selected' : '' ?>>Alugado</option>
                                                </select>
                                                <i class="fas fa-chevron-down absolute right-2 top-1/2 transform -translate-y-1/2 text-xs pointer-events-none"></i>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            <div class="flex flex-wrap gap-3">
                                                <span class="flex items-center gap-1" title="Área">
                                                    <i class="fas fa-expand-arrows-alt text-gray-400"></i>
                                                    <?= htmlspecialchars($imovel['area'] ?? $imovel['area_total'] ?? '0') ?>m²
                                                </span>
                                                <span class="flex items-center gap-1" title="Quartos">
                                                    <i class="fas fa-bed text-gray-400"></i>
                                                    <?= htmlspecialchars($imovel['quartos'] ?? '0') ?>Q
                                                </span>
                                                <span class="flex items-center gap-1" title="Banheiros">
                                                    <i class="fas fa-bath text-gray-400"></i>
                                                    <?= htmlspecialchars($imovel['banheiros'] ?? '0') ?>B
                                                </span>
                                                <span class="flex items-center gap-1" title="Garagem">
                                                    <i class="fas fa-car text-gray-400"></i>
                                                    <?= htmlspecialchars($imovel['garagem'] ?? $imovel['vagas'] ?? '0') ?>G
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-1 text-gray-600">
                                                <i class="fas fa-eye text-gray-400"></i>
                                                <span class="font-semibold"><?= $imovel['visualizacoes'] ?? 0 ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <a href="../public/imovel.php?id=<?= $imovel['id'] ?>" target="_blank" 
                                                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-all text-sm font-medium flex items-center gap-1" 
                                                   title="Visualizar imóvel">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                                <a href="imovel_form.php?id=<?= $imovel['id'] ?>" 
                                                   class="bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white px-3 py-2 rounded-lg transition-all text-sm font-medium flex items-center gap-1" 
                                                   title="Editar imóvel">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="confirmarExclusao(<?= $imovel['id'] ?>, '<?= htmlspecialchars(addslashes($imovel['titulo'])) ?>')" 
                                                        class="bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white px-3 py-2 rounded-lg transition-all text-sm font-medium flex items-center gap-1" 
                                                        title="Excluir imóvel">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginação -->
            <?php if ($total_pages > 1): ?>
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            Mostrando <?= (($page - 1) * $items_per_page) + 1 ?> a <?= min($page * $items_per_page, $total_items) ?> de <?= $total_items ?> imóveis
                        </div>
                        
                        <nav class="flex items-center gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" 
                                   class="px-3 py-2 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-all">
                                    <i class="fas fa-angle-double-left"></i>
                                </a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                                   class="px-3 py-2 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-all">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                   class="px-4 py-2 <?= $i === $page ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white' : 'bg-white hover:bg-gray-50 border border-gray-200 text-gray-700' ?> rounded-lg text-sm font-medium transition-all">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                                   class="px-3 py-2 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-all">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" 
                                   class="px-3 py-2 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-all">
                                    <i class="fas fa-angle-double-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Estatísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-fade-in">
                <?php
                // Calcular estatísticas
                $stats_sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'disponivel' THEN 1 END) as disponiveis,
                    COUNT(CASE WHEN status = 'vendido' THEN 1 END) as vendidos,
                    COUNT(CASE WHEN status = 'alugado' THEN 1 END) as alugados,
                    AVG(preco) as preco_medio,
                    SUM(visualizacoes) as total_visualizacoes
                    FROM imoveis";
                $stats_stmt = $pdo->query($stats_sql);
                $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total de Imóveis</p>
                            <p class="text-3xl font-bold text-indigo-600"><?= $stats['total'] ?></p>
                            <p class="text-sm text-gray-500 mt-1">
                                <?= $stats['disponiveis'] ?> disponíveis • <?= $stats['vendidos'] ?> vendidos • <?= $stats['alugados'] ?> alugados
                            </p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-building text-2xl text-indigo-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Preço Médio</p>
                            <p class="text-3xl font-bold text-green-600">R$ <?= number_format($stats['preco_medio'] ?? 0, 0, ',', '.') ?></p>
                            <p class="text-sm text-gray-500 mt-1">Baseado em todos os imóveis</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover:shadow-xl transition-all">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 mb-1">Total de Visualizações</p>
                            <p class="text-3xl font-bold text-blue-600"><?= number_format($stats['total_visualizacoes'] ?? 0) ?></p>
                            <p class="text-sm text-gray-500 mt-1">Em todos os imóveis</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-eye text-2xl text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast de Sucesso/Erro -->
    <?php if (isset($_SESSION['sucesso'])): ?>
        <div id="toast-sucesso" class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-2 animate-fade-in">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['sucesso']) ?></span>
            <button onclick="this.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('toast-sucesso');
                if (toast) toast.remove();
            }, 5000);
        </script>
        <?php unset($_SESSION['sucesso']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['erro'])): ?>
        <div id="toast-erro" class="fixed top-4 right-4 z-50 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-2 animate-fade-in">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['erro']) ?></span>
            <button onclick="this.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('toast-erro');
                if (toast) toast.remove();
            }, 5000);
        </script>
        <?php unset($_SESSION['erro']); ?>
    <?php endif; ?>

</body>
</html>