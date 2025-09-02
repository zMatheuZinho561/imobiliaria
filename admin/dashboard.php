<?php
require_once "../includes/config.php";
// Verifica se o usuário está logado e é admin
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_role"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

// Buscar dados para estatísticas
$total_imoveis = $pdo->query("SELECT COUNT(*) FROM imoveis")->fetchColumn();
$total_visualizacoes = $pdo->query("SELECT SUM(visualizacoes) FROM imoveis")->fetchColumn() ?: 0;
$total_contatos = $pdo->query("SELECT COUNT(*) FROM contatos")->fetchColumn();

// Estatísticas por status
$stats_status = $pdo->query("
    SELECT 
        status,
        COUNT(*) as total,
        AVG(preco) as preco_medio
    FROM imoveis 
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas por tipo
$stats_tipo = $pdo->query("
    SELECT 
        tipo,
        COUNT(*) as total,
        AVG(preco) as preco_medio
    FROM imoveis 
    GROUP BY tipo
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Imóveis mais visualizados
$imoveis_populares = $pdo->query("
    SELECT titulo, visualizacoes, preco, status
    FROM imoveis 
    WHERE visualizacoes > 0
    ORDER BY visualizacoes DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Contatos recentes
$contatos_recentes = $pdo->query("
    SELECT nome, email, telefone, created_at
    FROM contatos 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas de faturamento (simulado baseado nos preços)
$valor_total_imoveis = $pdo->query("SELECT SUM(preco) FROM imoveis WHERE status = 'disponivel'")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Imobiliária Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100 min-h-screen">

    <!-- Sidebar Melhorado -->
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
            <a href="dashboard.php" class="sidebar-item active flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-chart-line w-5 text-center"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="imoveis.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-building w-5 text-center"></i>
                <span class="font-medium">Imóveis</span>
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
        <!-- Header Superior -->
        <header class="bg-white/80 backdrop-blur-md shadow-lg sticky top-0 z-30 border-b border-white/20">
            <div class="px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Painel Administrativo
                        </h1>
                        <p class="text-gray-600 mt-1">Gerencie sua imobiliária com eficiência</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Último acesso</p>
                            <p class="font-semibold text-gray-700"><?= date('d/m/Y H:i') ?></p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Conteúdo do Dashboard -->
        <div class="p-8 space-y-8">
            <!-- Estatísticas Principais -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in">
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Imóveis</p>
                            <p class="text-3xl font-bold text-indigo-600 mt-2"><?= $total_imoveis ?></p>
                            <p class="text-sm text-gray-600 mt-1">Total cadastrados</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-home text-2xl text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Visualizações</p>
                            <p class="text-3xl font-bold text-green-600 mt-2"><?= number_format($total_visualizacoes) ?></p>
                            <p class="text-sm text-gray-600 mt-1">Total no site</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-eye text-2xl text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Contatos</p>
                            <p class="text-3xl font-bold text-orange-600 mt-2"><?= $total_contatos ?></p>
                            <p class="text-sm text-gray-600 mt-1">Leads recebidos</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-envelope text-2xl text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Valor Total</p>
                            <p class="text-2xl font-bold text-blue-600 mt-2">R$ <?= number_format($valor_total_imoveis / 1000000, 1, ',', '.') ?>M</p>
                            <p class="text-sm text-gray-600 mt-1">Portfólio disponível</p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-2xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-bolt text-indigo-500"></i>
                    Ações Rápidas
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="imovel_form.php" 
                       class="group bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white p-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-lg mb-1">Novo Imóvel</h3>
                                <p class="text-green-100 text-sm">Cadastrar propriedade</p>
                            </div>
                            <i class="fas fa-plus text-2xl group-hover:scale-110 transition-transform"></i>
                        </div>
                    </a>

                    <a href="imoveis.php" 
                       class="group bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white p-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-lg mb-1">Gerenciar</h3>
                                <p class="text-blue-100 text-sm">Editar imóveis</p>
                            </div>
                            <i class="fas fa-cog text-2xl group-hover:scale-110 transition-transform"></i>
                        </div>
                    </a>

                    <a href="contatos.php" 
                       class="group bg-gradient-to-r from-purple-500 to-violet-500 hover:from-purple-600 hover:to-violet-600 text-white p-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-lg mb-1">Contatos</h3>
                                <p class="text-purple-100 text-sm">Ver mensagens</p>
                            </div>
                            <i class="fas fa-comments text-2xl group-hover:scale-110 transition-transform"></i>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Gráficos e Dados -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Imóveis por Status -->
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-indigo-500"></i>
                        Imóveis por Status
                    </h3>
                    <div class="space-y-4">
                        <?php foreach ($stats_status as $stat): ?>
                            <?php 
                            $colors = [
                                'disponivel' => 'from-green-500 to-emerald-500',
                                'vendido' => 'from-red-500 to-rose-500',
                                'alugado' => 'from-blue-500 to-indigo-500'
                            ];
                            $color = $colors[$stat['status']] ?? 'from-gray-500 to-gray-600';
                            ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 bg-gradient-to-r <?= $color ?> rounded-full"></div>
                                    <span class="font-medium capitalize"><?= htmlspecialchars($stat['status']) ?></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-gray-800"><?= $stat['total'] ?></span>
                                    <p class="text-sm text-gray-500">imóveis</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tipos Mais Populares -->
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-building text-indigo-500"></i>
                        Tipos Mais Cadastrados
                    </h3>
                    <div class="space-y-4">
                        <?php foreach ($stats_tipo as $index => $tipo): ?>
                            <?php 
                            $colors = [
                                'from-blue-500 to-indigo-500',
                                'from-green-500 to-emerald-500',
                                'from-purple-500 to-violet-500',
                                'from-orange-500 to-red-500',
                                'from-pink-500 to-rose-500'
                            ];
                            $color = $colors[$index] ?? 'from-gray-500 to-gray-600';
                            ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div class="w-4 h-4 bg-gradient-to-r <?= $color ?> rounded-full"></div>
                                    <span class="font-medium capitalize"><?= htmlspecialchars($tipo['tipo']) ?></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-gray-800"><?= $tipo['total'] ?></span>
                                    <p class="text-sm text-gray-500">unidades</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tabelas de Dados -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Imóveis Mais Visualizados -->
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-fire text-red-500"></i>
                        Imóveis Populares
                    </h3>
                    <div class="space-y-3">
                        <?php if (empty($imoveis_populares)): ?>
                            <p class="text-gray-500 text-center py-8">Nenhuma visualização registrada ainda</p>
                        <?php else: ?>
                            <?php foreach ($imoveis_populares as $imovel): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-gray-800 truncate"><?= htmlspecialchars($imovel['titulo']) ?></h4>
                                        <p class="text-sm text-gray-500">R$ <?= number_format($imovel['preco'], 2, ',', '.') ?></p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <span class="text-lg font-bold text-indigo-600"><?= $imovel['visualizacoes'] ?></span>
                                        <p class="text-xs text-gray-500">views</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contatos Recentes -->
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-clock text-green-500"></i>
                        Contatos Recentes
                    </h3>
                    <div class="space-y-3">
                        <?php if (empty($contatos_recentes)): ?>
                            <p class="text-gray-500 text-center py-8">Nenhum contato recebido ainda</p>
                        <?php else: ?>
                            <?php foreach ($contatos_recentes as $contato): ?>
                                <div class="p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-gray-800"><?= htmlspecialchars($contato['nome']) ?></h4>
                                        <span class="text-sm text-gray-500">
                                            <?= date('d/m H:i', strtotime($contato['created_at'])) ?>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                            <?= htmlspecialchars($contato['email']) ?>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <i class="fas fa-phone text-gray-400"></i>
                                            <?= htmlspecialchars($contato['telefone']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>