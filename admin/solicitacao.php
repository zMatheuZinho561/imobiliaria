<?php
require_once "../includes/config.php";
// Verifica se o usuário está logado e é admin
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["usuario_role"] !== "admin") {
    header("Location: ../public/index.php");
    exit;
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $solicitacao_id = $_POST['solicitacao_id'] ?? 0;
    
    if ($action === 'update_status' && $solicitacao_id) {
        $novo_status = $_POST['novo_status'] ?? '';
        $observacoes = $_POST['observacoes'] ?? '';
        
        try {
            $stmt = $pdo->prepare("UPDATE solicitacoes_anuncio SET status = ?, observacoes_admin = ?, data_atualizacao = NOW() WHERE id = ?");
            $stmt->execute([$novo_status, $observacoes, $solicitacao_id]);
            
            $mensagem_sucesso = "Status atualizado com sucesso!";
        } catch (Exception $e) {
            $mensagem_erro = "Erro ao atualizar status: " . $e->getMessage();
        }
    }
    
    if ($action === 'approve_and_create' && $solicitacao_id) {
        try {
            $pdo->beginTransaction();
            
            // Buscar dados da solicitação
            $stmt = $pdo->prepare("SELECT * FROM solicitacoes_anuncio WHERE id = ?");
            $stmt->execute([$solicitacao_id]);
            $solicitacao = $stmt->fetch();
            
            if ($solicitacao) {
                // Criar imóvel baseado na solicitação
                $stmt = $pdo->prepare("
                    INSERT INTO imoveis (
                        titulo, descricao, preco, tipo, status, quartos, suites, banheiros, 
                        vagas, area_total, area_privativa, endereco, bairro, cidade, estado, 
                        cep, valor_condominio, iptu, created_at
                    ) VALUES (?, ?, ?, ?, 'disponivel', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $titulo = $solicitacao['tipo_imovel'] . ' em ' . $solicitacao['bairro'];
                $endereco = $solicitacao['rua'] . ', ' . $solicitacao['numero'];
                if ($solicitacao['complemento']) {
                    $endereco .= ' - ' . $solicitacao['complemento'];
                }
                
                // Usar valor de venda como preco principal, ou aluguel se não houver venda
                $preco = $solicitacao['valor_venda'] ?: $solicitacao['valor_aluguel'];
                $preco = preg_replace('/[^\d,.]/', '', $preco);
                $preco = str_replace(['.', ','], ['', '.'], $preco);
                
                $stmt->execute([
                    $titulo,
                    $solicitacao['descricao'],
                    $preco,
                    $solicitacao['tipo_imovel'],
                    $solicitacao['dormitorios'],
                    $solicitacao['suites'],
                    $solicitacao['banheiros'],
                    $solicitacao['vagas_garagem'],
                    $solicitacao['area_total'],
                    $solicitacao['area_privativa'],
                    $endereco,
                    $solicitacao['bairro'],
                    $solicitacao['cidade'],
                    $solicitacao['estado'],
                    $solicitacao['cep'],
                    $solicitacao['valor_condominio'],
                    $solicitacao['iptu']
                ]);
                
                // Atualizar status da solicitação
                $stmt = $pdo->prepare("UPDATE solicitacoes_anuncio SET status = 'aprovada', data_atualizacao = NOW() WHERE id = ?");
                $stmt->execute([$solicitacao_id]);
                
                $pdo->commit();
                $mensagem_sucesso = "Solicitação aprovada e imóvel criado com sucesso!";
            }
        } catch (Exception $e) {
            $pdo->rollback();
            $mensagem_erro = "Erro ao criar imóvel: " . $e->getMessage();
        }
    }
}

// Filtros
$filtro_status = $_GET['status'] ?? '';
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_data = $_GET['data'] ?? '';

// Construir query
$where_conditions = [];
$params = [];

if ($filtro_status) {
    $where_conditions[] = "status = ?";
    $params[] = $filtro_status;
}

if ($filtro_tipo) {
    $where_conditions[] = "tipo_imovel = ?";
    $params[] = $filtro_tipo;
}

if ($filtro_data) {
    $where_conditions[] = "DATE(created_at) = ?";
    $params[] = $filtro_data;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Buscar solicitações
$stmt = $pdo->prepare("
    SELECT * FROM solicitacoes_anuncio 
    $where_clause 
    ORDER BY created_at DESC
");
$stmt->execute($params);
$solicitacoes = $stmt->fetchAll();

// Estatísticas
$stats_pendentes = $pdo->query("SELECT COUNT(*) FROM solicitacoes_anuncio WHERE status = 'pendente'")->fetchColumn();
$stats_aprovadas = $pdo->query("SELECT COUNT(*) FROM solicitacoes_anuncio WHERE status = 'aprovada'")->fetchColumn();
$stats_rejeitadas = $pdo->query("SELECT COUNT(*) FROM solicitacoes_anuncio WHERE status = 'rejeitada'")->fetchColumn();
$stats_total = $pdo->query("SELECT COUNT(*) FROM solicitacoes_anuncio")->fetchColumn();

// Buscar tipos únicos para o filtro
$tipos_stmt = $pdo->query("SELECT DISTINCT tipo_imovel FROM solicitacoes_anuncio ORDER BY tipo_imovel");
$tipos_unicos = $tipos_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações de Anúncio - Imobiliária Premium</title>
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
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pendente {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
        .status-aprovada {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .status-rejeitada {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }
        .modal.active {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            margin: 2rem;
            width: 100%;
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
            <a href="dashboard.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-chart-line w-5 text-center"></i>
                <span class="font-medium">Dashboard</span>
            </a>
            <a href="imoveis.php" class="sidebar-item flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
                <i class="fas fa-building w-5 text-center"></i>
                <span class="font-medium">Imóveis</span>
            </a>
            <a href="solicitacoes.php" class="sidebar-item active flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-300 hover:bg-white/20">
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
        <!-- Header Superior -->
        <header class="bg-white/80 backdrop-blur-md shadow-lg sticky top-0 z-30 border-b border-white/20">
            <div class="px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Solicitações de Anúncio
                        </h1>
                        <p class="text-gray-600 mt-1">Gerencie as solicitações de anúncio dos proprietários</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total de solicitações</p>
                            <p class="font-semibold text-gray-700"><?= $stats_total ?></p>
                        </div>
                        <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Conteúdo -->
        <div class="p-8 space-y-8">
            <?php if (isset($mensagem_sucesso)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-2xl animate-fade-in">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($mensagem_sucesso) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($mensagem_erro)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-2xl animate-fade-in">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($mensagem_erro) ?>
                </div>
            <?php endif; ?>

            <!-- Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 animate-fade-in">
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total</p>
                            <p class="text-3xl font-bold text-indigo-600 mt-2"><?= $stats_total ?></p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-file-alt text-2xl text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Pendentes</p>
                            <p class="text-3xl font-bold text-orange-600 mt-2"><?= $stats_pendentes ?></p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-clock text-2xl text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Aprovadas</p>
                            <p class="text-3xl font-bold text-green-600 mt-2"><?= $stats_aprovadas ?></p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-check text-2xl text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 hover-lift transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Rejeitadas</p>
                            <p class="text-3xl font-bold text-red-600 mt-2"><?= $stats_rejeitadas ?></p>
                        </div>
                        <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-rose-500 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-times text-2xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-filter text-indigo-500"></i>
                    Filtros
                </h2>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <option value="pendente" <?= $filtro_status === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="aprovada" <?= $filtro_status === 'aprovada' ? 'selected' : '' ?>>Aprovada</option>
                            <option value="rejeitada" <?= $filtro_status === 'rejeitada' ? 'selected' : '' ?>>Rejeitada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Todos</option>
                            <?php foreach ($tipos_unicos as $tipo): ?>
                                <option value="<?= htmlspecialchars($tipo) ?>" <?= $filtro_tipo === $tipo ? 'selected' : '' ?>>
                                    <?= ucfirst(htmlspecialchars($tipo)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                        <input type="date" name="data" value="<?= htmlspecialchars($filtro_data) ?>" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-4 py-2 rounded-lg hover:from-indigo-600 hover:to-purple-600 transition-all">
                            <i class="fas fa-search mr-2"></i>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de Solicitações -->
            <div class="bg-white/80 backdrop-blur-md p-6 rounded-2xl shadow-lg border border-white/20 animate-fade-in">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-list text-indigo-500"></i>
                    Solicitações Recebidas
                </h2>

                <?php if (empty($solicitacoes)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-xl text-gray-500 mb-2">Nenhuma solicitação encontrada</p>
                        <p class="text-gray-400">As solicitações aparecerão aqui quando forem recebidas</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Solicitante</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Imóvel</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Localização</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Valor</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Data</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitacoes as $solicitacao): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-4">
                                            <div>
                                                <p class="font-medium text-gray-800"><?= htmlspecialchars($solicitacao['nome_proprietario']) ?></p>
                                                <p class="text-sm text-gray-500"><?= htmlspecialchars($solicitacao['telefone_proprietario']) ?></p>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div>
                                                <p class="font-medium text-gray-800"><?= ucfirst(htmlspecialchars($solicitacao['tipo_imovel'])) ?></p>
                                                <p class="text-sm text-gray-500"><?= htmlspecialchars($solicitacao['situacao_imovel']) ?></p>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4">
                                            <p class="text-sm text-gray-700"><?= htmlspecialchars($solicitacao['bairro']) ?></p>
                                            <p class="text-sm text-gray-500"><?= htmlspecialchars($solicitacao['cidade']) ?>/<?= htmlspecialchars($solicitacao['estado']) ?></p>
                                        </td>
                                        <td class="py-4 px-4">
                                            <?php if ($solicitacao['valor_venda']): ?>
                                                <p class="text-sm font-medium text-green-600">Venda: <?= htmlspecialchars($solicitacao['valor_venda']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($solicitacao['valor_aluguel']): ?>
                                                <p class="text-sm font-medium text-blue-600">Aluguel: <?= htmlspecialchars($solicitacao['valor_aluguel']) ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-4">
                                            <span class="status-badge status-<?= htmlspecialchars($solicitacao['status']) ?>">
                                                <?= ucfirst(htmlspecialchars($solicitacao['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <p class="text-sm text-gray-700"><?= date('d/m/Y', strtotime($solicitacao['created_at'])) ?></p>
                                            <p class="text-sm text-gray-500"><?= date('H:i', strtotime($solicitacao['created_at'])) ?></p>
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center gap-2">
                                                <button onclick="viewDetails(<?= $solicitacao['id'] ?>)" 
                                                        class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($solicitacao['status'] === 'pendente'): ?>
                                                    <button onclick="updateStatus(<?= $solicitacao['id'] ?>, 'aprovada')" 
                                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button onclick="updateStatus(<?= $solicitacao['id'] ?>, 'rejeitada')" 
                                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-sm transition-colors">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal de Detalhes -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div id="modalContent">
                <!-- Conteúdo será carregado via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal de Atualização de Status -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="p-6">
                <h3 class="text-xl font-bold mb-4">Atualizar Status</h3>
                <form id="statusForm" method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="solicitacao_id" id="statusSolicitacaoId">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Novo Status</label>
                        <select name="novo_status" id="novoStatus" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" required>
                            <option value="">Selecione o status</option>
                            <option value="pendente">Pendente</option>
                            <option value="aprovada">Aprovada</option>
                            <option value="rejeitada">Rejeitada</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                        <textarea name="observacoes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Adicione observações sobre a decisão..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeModal('statusModal')" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600 transition-colors">
                            Atualizar Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dados das solicitações para uso no JavaScript
        const solicitacoesData = <?= json_encode($solicitacoes) ?>;

        function viewDetails(id) {
            const solicitacao = solicitacoesData.find(s => s.id == id);
            if (!solicitacao) return;

            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = `
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Detalhes da Solicitação #${solicitacao.id}</h3>
                        <button onclick="closeModal('detailsModal')" class="text-gray-500 hover:text-gray-700 text-2xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dados do Proprietário -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-lg mb-3 text-indigo-600">
                                <i class="fas fa-user mr-2"></i>Dados do Proprietário
                            </h4>
                            <div class="space-y-2">
                                <p><strong>Nome:</strong> ${solicitacao.nome_proprietario}</p>
                                <p><strong>Telefone:</strong> ${solicitacao.telefone_proprietario}</p>
                                <p><strong>Email:</strong> ${solicitacao.email_proprietario || 'Não informado'}</p>
                                <p><strong>CPF:</strong> ${solicitacao.cpf_proprietario || 'Não informado'}</p>
                            </div>
                        </div>

                        <!-- Localização -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-lg mb-3 text-indigo-600">
                                <i class="fas fa-map-marker-alt mr-2"></i>Localização
                            </h4>
                            <div class="space-y-2">
                                <p><strong>CEP:</strong> ${solicitacao.cep}</p>
                                <p><strong>Estado:</strong> ${solicitacao.estado}</p>
                                <p><strong>Cidade:</strong> ${solicitacao.cidade}</p>
                                <p><strong>Bairro:</strong> ${solicitacao.bairro}</p>
                                <p><strong>Rua:</strong> ${solicitacao.rua}</p>
                                <p><strong>Número:</strong> ${solicitacao.numero}</p>
                                ${solicitacao.complemento ? `<p><strong>Complemento:</strong> ${solicitacao.complemento}</p>` : ''}
                                ${solicitacao.nome_condominio ? `<p><strong>Condomínio:</strong> ${solicitacao.nome_condominio}</p>` : ''}
                            </div>
                        </div>

                        <!-- Detalhes do Imóvel -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-lg mb-3 text-indigo-600">
                                <i class="fas fa-building mr-2"></i>Detalhes do Imóvel
                            </h4>
                            <div class="space-y-2">
                                <p><strong>Tipo:</strong> ${solicitacao.tipo_imovel}</p>
                                <p><strong>Situação:</strong> ${solicitacao.situacao_imovel}</p>
                                <p><strong>Finalidade:</strong> ${solicitacao.finalidade}</p>
                                <p><strong>Dormitórios:</strong> ${solicitacao.dormitorios || '0'}</p>
                                <p><strong>Suítes:</strong> ${solicitacao.suites || '0'}</p>
                                <p><strong>Banheiros:</strong> ${solicitacao.banheiros || '0'}</p>
                                <p><strong>Vagas:</strong> ${solicitacao.vagas_garagem || '0'}</p>
                                <p><strong>Área Total:</strong> ${solicitacao.area_total ? solicitacao.area_total + ' m²' : 'Não informado'}</p>
                                <p><strong>Área Privativa:</strong> ${solicitacao.area_privativa ? solicitacao.area_privativa + ' m²' : 'Não informado'}</p>
                            </div>
                        </div>

                        <!-- Valores -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-lg mb-3 text-indigo-600">
                                <i class="fas fa-dollar-sign mr-2"></i>Valores
                            </h4>
                            <div class="space-y-2">
                                <p><strong>Valor de Venda:</strong> ${solicitacao.valor_venda || 'Não informado'}</p>
                                <p><strong>Valor do Aluguel:</strong> ${solicitacao.valor_aluguel || 'Não informado'}</p>
                                <p><strong>Condomínio:</strong> ${solicitacao.valor_condominio || 'Não informado'}</p>
                                <p><strong>IPTU:</strong> ${solicitacao.iptu || 'Não informado'}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-3 text-indigo-600">
                            <i class="fas fa-align-left mr-2"></i>Descrição
                        </h4>
                        <p class="text-gray-700 leading-relaxed">${solicitacao.descricao}</p>
                    </div>

                    <!-- Status e Data -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-lg mb-3 text-indigo-600">Status</h4>
                            <span class="status-badge status-${solicitacao.status}">${solicitacao.status.charAt(0).toUpperCase() + solicitacao.status.slice(1)}</span>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-lg mb-3 text-indigo-600">Data da Solicitação</h4>
                            <p>${new Date(solicitacao.created_at).toLocaleString('pt-BR')}</p>
                        </div>
                    </div>

                    ${solicitacao.observacoes_admin ? `
                    <div class="mt-6 bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-3 text-yellow-700">
                            <i class="fas fa-sticky-note mr-2"></i>Observações do Admin
                        </h4>
                        <p class="text-yellow-700">${solicitacao.observacoes_admin}</p>
                    </div>
                    ` : ''}

                    <!-- Ações -->
                    <div class="mt-6 flex justify-end gap-3">
                        ${solicitacao.status === 'pendente' ? `
                            <button onclick="approveAndCreate(${solicitacao.id})" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                                <i class="fas fa-check mr-2"></i>Aprovar e Criar Imóvel
                            </button>
                        ` : ''}
                        <button onclick="openStatusModal(${solicitacao.id})" 
                                class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg transition-colors">
                            <i class="fas fa-edit mr-2"></i>Atualizar Status
                        </button>
                    </div>
                </div>
            `;
            
            document.getElementById('detailsModal').classList.add('active');
        }

        function updateStatus(id, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="solicitacao_id" value="${id}">
                <input type="hidden" name="novo_status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function openStatusModal(id) {
            document.getElementById('statusSolicitacaoId').value = id;
            closeModal('detailsModal');
            document.getElementById('statusModal').classList.add('active');
        }

        function approveAndCreate(id) {
            if (confirm('Tem certeza que deseja aprovar esta solicitação e criar o imóvel automaticamente?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="approve_and_create">
                    <input type="hidden" name="solicitacao_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Fechar modais clicando fora
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('active');
            }
        });

        // Fechar modais com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(modal => {
                    modal.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>