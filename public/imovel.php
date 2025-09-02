<?php
require_once '../includes/config.php';

// Pega o ID da URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM imoveis WHERE id = ?");
$stmt->execute([$id]);
$imovel = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$imovel){
    die("Imóvel não encontrado!");
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
    $column_exists['data_cadastro'] = in_array('data_cadastro', $existing_columns);
    $column_exists['condominio'] = in_array('condominio', $existing_columns);
    $column_exists['iptu'] = in_array('iptu', $existing_columns);
} catch(Exception $e) {
    $column_exists = [
        'status' => false,
        'tipo_negocio' => false,
        'bairro' => false,
        'tipo_imovel' => false,
        'destaque' => false,
        'data_cadastro' => false,
        'condominio' => false,
        'iptu' => false
    ];
}

// Buscar múltiplas imagens (se existir tabela de fotos)
$fotos = [];
try {
    // Verificar se existe uma tabela de fotos relacionadas
    $stmt_fotos = $pdo->prepare("SHOW TABLES LIKE 'imovel_fotos'");
    $stmt_fotos->execute();
    $tabela_fotos_existe = $stmt_fotos->rowCount() > 0;
    
    if ($tabela_fotos_existe) {
        $stmt_fotos = $pdo->prepare("SELECT * FROM imovel_fotos WHERE imovel_id = ? ORDER BY ordem ASC, id ASC");
        $stmt_fotos->execute([$id]);
        $fotos = $stmt_fotos->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Se não tiver fotos na tabela separada, usar a imagem principal
    if (empty($fotos) && !empty($imovel['imagem'])) {
        $fotos[] = ['arquivo' => $imovel['imagem'], 'legenda' => 'Foto principal'];
    }
} catch(Exception $e) {
    // Fallback para imagem única
    if (!empty($imovel['imagem'])) {
        $fotos[] = ['arquivo' => $imovel['imagem'], 'legenda' => 'Foto principal'];
    }
}

// Buscar imóveis similares
$imoveis_similares = [];
try {
    $sql_similares = "SELECT * FROM imoveis WHERE id != ? ";
    $params_similares = [$id];
    
    // Filtrar por tipo se disponível
    if ($column_exists['tipo_imovel'] && !empty($imovel['tipo_imovel'])) {
        $sql_similares .= "AND tipo_imovel = ? ";
        $params_similares[] = $imovel['tipo_imovel'];
    } elseif (!empty($imovel['tipo'])) {
        $sql_similares .= "AND tipo = ? ";
        $params_similares[] = $imovel['tipo'];
    }
    
    // Filtrar por faixa de preço similar (±30%)
    if (!empty($imovel['preco'])) {
        $preco_min = $imovel['preco'] * 0.7;
        $preco_max = $imovel['preco'] * 1.3;
        $sql_similares .= "AND preco BETWEEN ? AND ? ";
        $params_similares[] = $preco_min;
        $params_similares[] = $preco_max;
    }
    
    if ($column_exists['status']) {
        $sql_similares .= "AND status IN ('ativo', 'disponivel') ";
    }
    
    $sql_similares .= "ORDER BY RAND() LIMIT 3";
    
    $stmt_similares = $pdo->prepare($sql_similares);
    $stmt_similares->execute($params_similares);
    $imoveis_similares = $stmt_similares->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $imoveis_similares = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($imovel['titulo']); ?> - Imobiliária Premium</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($imovel['descricao'] ?? '', 0, 160)); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <style>
        .glass-effect { backdrop-filter: blur(16px) saturate(180%); }
        .animate-fade-in { animation: fadeIn 0.6s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .swiper-button-next, .swiper-button-prev {
            color: white !important;
            background: rgba(0,0,0,0.5) !important;
            width: 44px !important;
            height: 44px !important;
            border-radius: 50% !important;
            margin-top: -22px !important;
        }
        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 18px !important;
        }
        .swiper-pagination-bullet {
            background: white !important;
            opacity: 0.7 !important;
        }
        .swiper-pagination-bullet-active {
            background: #3B82F6 !important;
            opacity: 1 !important;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-50 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white/90 backdrop-blur-md shadow-lg sticky top-0 z-50 border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex-shrink-0">
                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            <i class="fas fa-home mr-2 text-blue-600"></i>Imobiliária Premium
                        </span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar aos Imóveis
                    </a>
                    <a href="../admin/login.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all transform hover:scale-105">
                        <i class="fas fa-user mr-1"></i>Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="bg-white/50 py-4 border-b border-gray-200/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div>
                            <a href="index.php" class="text-gray-500 hover:text-gray-700 transition-colors">
                                <i class="fas fa-home mr-1"></i>Início
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <a href="index.php" class="text-gray-500 hover:text-gray-700 transition-colors">Imóveis</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-gray-700 font-medium truncate max-w-xs"><?php echo htmlspecialchars($imovel['titulo']); ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Coluna Principal - Fotos e Detalhes -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Galeria de Fotos -->
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden animate-fade-in">
                    <?php if (!empty($fotos)): ?>
                        <div class="swiper property-gallery">
                            <div class="swiper-wrapper">
                                <?php foreach($fotos as $foto): ?>
                                    <div class="swiper-slide">
                                        <div class="relative h-96 md:h-[500px]">
                                            <img src="uploads/<?php echo htmlspecialchars($foto['arquivo']); ?>" 
                                                 alt="<?php echo htmlspecialchars($foto['legenda'] ?? $imovel['titulo']); ?>" 
                                                 class="w-full h-full object-cover"
                                                 onerror="this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center\'><i class=\'fas fa-home text-6xl text-gray-400\'></i></div>'">
                                            
                                            <!-- Badge de Tipo de Negócio -->
                                            <?php if ($column_exists['tipo_negocio'] && !empty($imovel['tipo_negocio'])): ?>
                                                <div class="absolute top-4 left-4">
                                                    <span class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg glass-effect">
                                                        <?php echo ucfirst($imovel['tipo_negocio']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Badge de Destaque -->
                                            <?php if ($column_exists['destaque'] && $imovel['destaque'] == 1): ?>
                                                <div class="absolute top-4 right-4">
                                                    <span class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white px-3 py-1 rounded-full text-sm font-medium shadow-lg glass-effect">
                                                        <i class="fas fa-star mr-1"></i>Destaque
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-pagination"></div>
                        </div>
                    <?php else: ?>
                        <div class="h-96 md:h-[500px] bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                            <div class="text-center text-gray-500">
                                <i class="fas fa-home text-6xl mb-4"></i>
                                <p class="text-lg">Nenhuma imagem disponível</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Informações Principais -->
                <div class="bg-white rounded-3xl shadow-xl p-8 animate-fade-in">
                    <div class="mb-6">
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($imovel['titulo']); ?></h1>
                        <div class="flex items-center text-gray-600 mb-4">
                            <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                            <span><?php echo htmlspecialchars($imovel['localizacao']); ?></span>
                        </div>
                        
                        <?php if ($column_exists['bairro'] && !empty($imovel['bairro'])): ?>
                            <div class="flex items-center text-gray-600 mb-4">
                                <i class="fas fa-location-dot mr-2 text-blue-600"></i>
                                <span>Bairro: <?php echo htmlspecialchars($imovel['bairro']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                            R$ <?php echo number_format($imovel['preco'], 2, ',', '.'); ?>
                        </div>
                    </div>

                    <!-- Características do Imóvel -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl text-center border border-blue-100">
                            <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $imovel['quartos']; ?></div>
                            <div class="text-gray-600 text-sm font-medium">
                                <i class="fas fa-bed mr-1 text-blue-500"></i>Quartos
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl text-center border border-blue-100">
                            <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $imovel['banheiros']; ?></div>
                            <div class="text-gray-600 text-sm font-medium">
                                <i class="fas fa-bath mr-1 text-blue-500"></i>Banheiros
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl text-center border border-blue-100">
                            <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $imovel['garagem']; ?></div>
                            <div class="text-gray-600 text-sm font-medium">
                                <i class="fas fa-car mr-1 text-blue-500"></i>Vagas
                            </div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl text-center border border-blue-100">
                            <div class="text-3xl font-bold text-blue-600 mb-2"><?php echo $imovel['area']; ?></div>
                            <div class="text-gray-600 text-sm font-medium">
                                <i class="fas fa-ruler mr-1 text-blue-500"></i>m²
                            </div>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Descrição
                        </h2>
                        <div class="prose max-w-none text-gray-700 leading-relaxed">
                            <?php echo nl2br(htmlspecialchars($imovel['descricao'])); ?>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <?php if (($column_exists['condominio'] && !empty($imovel['condominio'])) || 
                              ($column_exists['iptu'] && !empty($imovel['iptu'])) || 
                              ($column_exists['tipo_imovel'] && !empty($imovel['tipo_imovel']))): ?>
                        <div class="border-t border-gray-200 pt-8">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">
                                <i class="fas fa-list mr-2 text-blue-600"></i>Informações Adicionais
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php if ($column_exists['tipo_imovel'] && !empty($imovel['tipo_imovel'])): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-building mr-2 text-blue-500"></i>
                                        <span class="font-medium">Tipo:</span>
                                        <span class="ml-2"><?php echo htmlspecialchars($imovel['tipo_imovel']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($column_exists['condominio'] && !empty($imovel['condominio'])): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-dollar-sign mr-2 text-blue-500"></i>
                                        <span class="font-medium">Condomínio:</span>
                                        <span class="ml-2">R$ <?php echo number_format($imovel['condominio'], 2, ',', '.'); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($column_exists['iptu'] && !empty($imovel['iptu'])): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-file-invoice-dollar mr-2 text-blue-500"></i>
                                        <span class="font-medium">IPTU:</span>
                                        <span class="ml-2">R$ <?php echo number_format($imovel['iptu'], 2, ',', '.'); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($column_exists['data_cadastro'] && !empty($imovel['data_cadastro'])): ?>
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar mr-2 text-blue-500"></i>
                                        <span class="font-medium">Cadastrado em:</span>
                                        <span class="ml-2"><?php echo date('d/m/Y', strtotime($imovel['data_cadastro'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar - Contato e Imóveis Similares -->
            <div class="lg:col-span-1 space-y-8">
                
                <!-- Card de Contato -->
                <div class="bg-white rounded-3xl shadow-xl p-8 sticky top-24 animate-fade-in">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Interessado?</h2>
                        <p class="text-gray-600">Entre em contato conosco</p>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- WhatsApp -->
                        <a href="https://wa.me/5599999999999?text=Olá, tenho interesse no imóvel: <?php echo urlencode($imovel['titulo']); ?> - R$ <?php echo number_format($imovel['preco'], 2, ',', '.'); ?>" 
                           target="_blank"
                           class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white py-4 px-6 rounded-2xl font-medium transition-all transform hover:scale-105 flex items-center justify-center shadow-lg">
                            <i class="fab fa-whatsapp text-2xl mr-3"></i>
                            <div>
                                <div class="text-sm opacity-90">Falar no</div>
                                <div class="font-bold">WhatsApp</div>
                            </div>
                        </a>
                        
                        <!-- Telefone -->
                        <a href="tel:+5599999999999" 
                           class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white py-4 px-6 rounded-2xl font-medium transition-all transform hover:scale-105 flex items-center justify-center shadow-lg">
                            <i class="fas fa-phone text-xl mr-3"></i>
                            <div>
                                <div class="text-sm opacity-90">Ligar para</div>
                                <div class="font-bold">(99) 99999-9999</div>
                            </div>
                        </a>
                        
                        <!-- Email -->
                        <a href="mailto:contato@imobiliaria.com?subject=Interesse no imóvel: <?php echo urlencode($imovel['titulo']); ?>" 
                           class="w-full bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white py-4 px-6 rounded-2xl font-medium transition-all transform hover:scale-105 flex items-center justify-center shadow-lg">
                            <i class="fas fa-envelope text-xl mr-3"></i>
                            <div>
                                <div class="text-sm opacity-90">Enviar</div>
                                <div class="font-bold">E-mail</div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Atendimento especializado
                        </p>
                    </div>
                </div>

                <!-- Imóveis Similares -->
                <?php if (!empty($imoveis_similares)): ?>
                    <div class="bg-white rounded-3xl shadow-xl p-8 animate-fade-in">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                            <i class="fas fa-home mr-2 text-blue-600"></i>Imóveis Similares
                        </h3>
                        
                        <div class="space-y-6">
                            <?php foreach($imoveis_similares as $similar): ?>
                                <a href="imovel.php?id=<?php echo $similar['id']; ?>" 
                                   class="block group hover:transform hover:scale-105 transition-all duration-300">
                                    <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-2xl overflow-hidden border border-gray-200 group-hover:border-blue-300 group-hover:shadow-lg">
                                        <?php if (!empty($similar['imagem'])): ?>
                                            <div class="relative h-32">
                                                <img src="uploads/<?php echo htmlspecialchars($similar['imagem']); ?>" 
                                                     alt="<?php echo htmlspecialchars($similar['titulo']); ?>"
                                                     class="w-full h-full object-cover"
                                                     onerror="this.parentElement.innerHTML='<div class=\'w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center\'><i class=\'fas fa-home text-2xl text-gray-400\'></i></div>'">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300"></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="p-4">
                                            <h4 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
                                                <?php echo htmlspecialchars($similar['titulo']); ?>
                                            </h4>
                                            <p class="text-sm text-gray-600 mb-2 flex items-center">
                                                <i class="fas fa-map-marker-alt mr-1 text-blue-500"></i>
                                                <?php echo htmlspecialchars(substr($similar['localizacao'], 0, 30) . (strlen($similar['localizacao']) > 30 ? '...' : '')); ?>
                                            </p>
                                            <div class="text-lg font-bold text-blue-600">
                                                R$ <?php echo number_format($similar['preco'], 2, ',', '.'); ?>
                                            </div>
                                            <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                                                <span><i class="fas fa-bed mr-1"></i><?php echo $similar['quartos']; ?> quartos</span>
                                                <span><i class="fas fa-bath mr-1"></i><?php echo $similar['banheiros']; ?> banh.</span>
                                                <span><i class="fas fa-car mr-1"></i><?php echo $similar['garagem']; ?> vagas</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-6 text-center">
                            <a href="index.php" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-medium transition-colors">
                                Ver todos os imóveis
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-900 to-blue-900 text-white py-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4">
                        <i class="fas fa-home mr-2"></i>Imobiliária Premium
                    </h3>
                    <p class="text-gray-300 mb-4">
                        Sua parceira ideal na busca pelo imóvel dos sonhos. Oferecemos as melhores oportunidades do mercado imobiliário.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-xl font-bold mb-4">Contato</h4>
                    <div class="space-y-2 text-gray-300">
                        <p><i class="fas fa-phone mr-2"></i> (99) 99999-9999</p>
                        <p><i class="fas fa-envelope mr-2"></i> contato@imobiliaria.com</p>
                        <p><i class="fas fa-map-marker-alt mr-2"></i> Rua Principal, 123 - Centro</p>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-xl font-bold mb-4">Redes Sociais</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white text-2xl transition-colors">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white text-2xl transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white text-2xl transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white text-2xl transition-colors">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-300">
                <p>&copy; <?php echo date('Y'); ?> Imobiliária Premium. Todos os direitos reservados.</p>
                <p class="mt-2 text-sm">Desenvolvido com <i class="fas fa-heart text-red-500"></i> para oferecer a melhor experiência</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Inicializar Swiper para galeria de fotos
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($fotos)): ?>
            const swiper = new Swiper('.property-gallery', {
                loop: true,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                speed: 600,
            });
            <?php endif; ?>

            // Scroll suave para links internos
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Animação de fade-in para elementos
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.animate-fade-in').forEach(el => {
                observer.observe(el);
            });

            // Funcionalidade de compartilhamento (opcional)
            function shareProperty() {
                if (navigator.share) {
                    navigator.share({
                        title: '<?php echo addslashes($imovel['titulo']); ?>',
                        text: 'Confira este imóvel incrível!',
                        url: window.location.href
                    }).then(() => {
                        console.log('Compartilhado com sucesso');
                    }).catch((error) => {
                        console.log('Erro ao compartilhar:', error);
                    });
                } else {
                    // Fallback para navegadores que não suportam Web Share API
                    copyToClipboard(window.location.href);
                    showNotification('Link copiado para a área de transferência!');
                }
            }

            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    console.log('Link copiado!');
                });
            }

            function showNotification(message) {
                // Criar notificação simples
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, 3000);
            }

            // Adicionar botão de compartilhar se necessário
            const shareBtn = document.createElement('button');
            shareBtn.innerHTML = '<i class="fas fa-share-alt mr-2"></i>Compartilhar';
            shareBtn.className = 'w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white py-3 px-6 rounded-2xl font-medium transition-all transform hover:scale-105 flex items-center justify-center shadow-lg mt-4';
            shareBtn.onclick = shareProperty;
            
            // Adicionar o botão ao card de contato (opcional)
            // document.querySelector('.sticky').querySelector('.space-y-4').appendChild(shareBtn);

            // Lazy loading para imagens
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }

            // Melhorar acessibilidade
            document.querySelectorAll('button, a').forEach(element => {
                element.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            // Adicionar loading state para links externos
            document.querySelectorAll('a[target="_blank"]').forEach(link => {
                link.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (icon) {
                        const originalClass = icon.className;
                        icon.className = 'fas fa-spinner fa-spin';
                        setTimeout(() => {
                            icon.className = originalClass;
                        }, 2000);
                    }
                });
            });

            // Adicionar efeito de hover suave nos cards
            document.querySelectorAll('.group').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            console.log('Página do imóvel carregada com sucesso!');
        });

        // Função para formatar números (utilitária)
        function formatNumber(num) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(num);
        }

        // Função para validar formulários (se houver)
        function validateForm(formElement) {
            const inputs = formElement.querySelectorAll('input[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('border-red-500');
                    isValid = false;
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            return isValid;
        }

        // Adicionar máscara para telefone (se houver campos)
        function phoneMask(input) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            input.value = value;
        }
    </script>

    <!-- Schema.org para SEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "RealEstateListing",
        "name": "<?php echo addslashes($imovel['titulo']); ?>",
        "description": "<?php echo addslashes(substr($imovel['descricao'] ?? '', 0, 200)); ?>",
        "price": "<?php echo $imovel['preco']; ?>",
        "priceCurrency": "BRL",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "<?php echo addslashes($imovel['localizacao']); ?>"
            <?php if ($column_exists['bairro'] && !empty($imovel['bairro'])): ?>
            ,"addressLocality": "<?php echo addslashes($imovel['bairro']); ?>"
            <?php endif; ?>
        },
        "floorSize": {
            "@type": "QuantitativeValue",
            "value": "<?php echo $imovel['area']; ?>",
            "unitCode": "MTK"
        },
        "numberOfRooms": "<?php echo $imovel['quartos']; ?>",
        "numberOfBathroomsTotal": "<?php echo $imovel['banheiros']; ?>",
        <?php if (!empty($fotos)): ?>
        "image": [
            <?php 
            $imageUrls = [];
            foreach($fotos as $foto) {
                $imageUrls[] = '"' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/uploads/' . addslashes($foto['arquivo']) . '"';
            }
            echo implode(',', $imageUrls);
            ?>
        ],
        <?php endif; ?>
        "offers": {
            "@type": "Offer",
            "price": "<?php echo $imovel['preco']; ?>",
            "priceCurrency": "BRL",
            "availability": "https://schema.org/InStock"
        }
    }
    </script>

</body>
</html>