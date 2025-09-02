<?php
require_once '../includes/config.php';

// Buscar imóveis em destaque
$stmt_destaques = $pdo->prepare("SELECT * FROM imoveis WHERE status = 'ativo' AND destaque = 1 ORDER BY created_at DESC LIMIT 6");
$stmt_destaques->execute();
$imoveis_destaque = $stmt_destaques->fetchAll(PDO::FETCH_ASSOC);

// Buscar imóveis recentes
$stmt_recentes = $pdo->prepare("SELECT * FROM imoveis WHERE status = 'ativo' ORDER BY created_at DESC LIMIT 8");
$stmt_recentes->execute();
$imoveis_recentes = $stmt_recentes->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$stats = [];
$stats['total_imoveis'] = $pdo->query("SELECT COUNT(*) FROM imoveis WHERE status = 'ativo'")->fetchColumn();
$stats['total_vendidos'] = $pdo->query("SELECT COUNT(*) FROM imoveis WHERE status = 'vendido'")->fetchColumn();
$stats['total_alugados'] = $pdo->query("SELECT COUNT(*) FROM imoveis WHERE status = 'alugado'")->fetchColumn();
$stats['anos_experiencia'] = 15; // Ajuste conforme necessário
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imobiliária - Sua Casa dos Sonhos Te Espera</title>
    <meta name="description" content="Encontre o imóvel perfeito em Cambé e região. Casa, apartamentos, terrenos e mais. Venda e aluguel com as melhores condições.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-bg {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.9), rgba(99, 102, 241, 0.8)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="rgba(255,255,255,0.1)" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
            background-position: center;
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .gradient-text {
            background: linear-gradient(135deg, #3B82F6, #8B5CF6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .parallax {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .glass {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-white">

    <!-- Navigation -->
    <nav class="fixed w-full top-0 z-50 bg-white/95 backdrop-blur-md shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-2xl font-bold gradient-text">
                            <i class="fas fa-home mr-2"></i>Imobiliária
                        </span>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#inicio" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Início</a>
                    <a href="#destaques" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Destaques</a>
                    <a href="#sobre" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Sobre</a>
                    <a href="../public/contato.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors">Contato</a>
                    <a href="index.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2 rounded-full font-semibold transition-all transform hover:scale-105">
                        Ver Todos os Imóveis
                    </a>
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button class="text-gray-700 hover:text-blue-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="hero-bg min-h-screen flex items-center relative overflow-hidden">
        <!-- Floating elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="floating absolute top-20 left-10 w-20 h-20 bg-white/10 rounded-full"></div>
            <div class="floating absolute top-40 right-20 w-16 h-16 bg-white/10 rounded-full" style="animation-delay: -1s;"></div>
            <div class="floating absolute bottom-40 left-1/4 w-12 h-12 bg-white/10 rounded-full" style="animation-delay: -2s;"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center">
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight">
                    Sua Casa dos
                    <span class="block text-yellow-300">Sonhos Te Espera</span>
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto leading-relaxed">
                    Descubra o imóvel perfeito em Cambé e região. Casas, apartamentos, terrenos e mais, 
                    com as melhores condições de venda e aluguel.
                </p>
                
                <!-- Quick Search -->
                <div class="glass rounded-2xl p-6 max-w-4xl mx-auto mb-8">
                    <form action="imoveis.php" method="GET" class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <select name="tipo_negocio" class="w-full px-4 py-3 rounded-lg bg-white/90 border-0 focus:ring-2 focus:ring-white">
                                <option value="">Comprar ou Alugar</option>
                                <option value="venda">Comprar</option>
                                <option value="aluguel">Alugar</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <select name="tipo_imovel" class="w-full px-4 py-3 rounded-lg bg-white/90 border-0 focus:ring-2 focus:ring-white">
                                <option value="">Tipo do Imóvel</option>
                                <option value="casa">Casa</option>
                                <option value="apartamento">Apartamento</option>
                                <option value="sobrado">Sobrado</option>
                                <option value="terreno">Terreno</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <input type="text" name="cidade" placeholder="Cidade" 
                                   class="w-full px-4 py-3 rounded-lg bg-white/90 border-0 focus:ring-2 focus:ring-white">
                        </div>
                        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-8 py-3 rounded-lg font-semibold transition-colors">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </form>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="../public/imovel.php" class="bg-white text-blue-600 px-8 py-4 rounded-full font-semibold text-lg hover:bg-gray-100 transition-all transform hover:scale-105 shadow-lg">
                        <i class="fas fa-home mr-2"></i>Ver Todos os Imóveis
                    </a>
                    <a href="#contato" class="border-2 border-white text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white hover:text-blue-600 transition-all transform hover:scale-105">
                        <i class="fas fa-phone mr-2"></i>Falar com Corretor
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Estatísticas -->
    <section class="py-20 bg-gradient-to-r from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-blue-600 mb-2">
                        <?= number_format($stats['total_imoveis']) ?>+
                    </div>
                    <div class="text-gray-600 font-medium">Imóveis Disponíveis</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-green-600 mb-2">
                        <?= number_format($stats['total_vendidos']) ?>+
                    </div>
                    <div class="text-gray-600 font-medium">Imóveis Vendidos</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-orange-600 mb-2">
                        <?= number_format($stats['total_alugados']) ?>+
                    </div>
                    <div class="text-gray-600 font-medium">Imóveis Alugados</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl md:text-5xl font-bold text-purple-600 mb-2">
                        <?= $stats['anos_experiencia'] ?>+
                    </div>
                    <div class="text-gray-600 font-medium">Anos de Experiência</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Imóveis em Destaque -->
    <section id="destaques" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Imóveis em <span class="gradient-text">Destaque</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Selecionamos os melhores imóveis para você. Confira nossas oportunidades especiais.
                </p>
            </div>

            <?php if (empty($imoveis_destaque)): ?>
                <div class="text-center py-16">
                    <i class="fas fa-home text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-2xl font-semibold text-gray-600 mb-4">Em breve, imóveis incríveis!</h3>
                    <p class="text-gray-500 mb-6">Estamos preparando uma seleção especial de imóveis para você.</p>
                    <a href="imoveis.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg inline-flex items-center transition-colors">
                        <i class="fas fa-eye mr-2"></i>Ver Todos os Imóveis
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    <?php foreach($imoveis_destaque as $imovel): ?>
                        <div class="card-hover bg-white rounded-2xl shadow-lg overflow-hidden">
                            <!-- Badge de Destaque -->
                            <div class="relative">
                                <?php if(!empty($imovel['imagem'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($imovel['imagem']) ?>" 
                                         alt="<?= htmlspecialchars($imovel['titulo']) ?>" 
                                         class="w-full h-64 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-64 bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                        <i class="fas fa-home text-5xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Badges -->
                                <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                                    <span class="bg-yellow-400 text-gray-900 px-3 py-1 rounded-full text-sm font-bold uppercase">
                                        <i class="fas fa-star mr-1"></i>Destaque
                                    </span>
                                    <span class="<?= ($imovel['tipo_negocio'] ?? 'venda') == 'venda' ? 'bg-green-600' : 'bg-orange-600' ?> text-white px-3 py-1 rounded-full text-sm font-semibold uppercase">
                                        <?= ($imovel['tipo_negocio'] ?? 'venda') == 'venda' ? 'Venda' : 'Aluguel' ?>
                                    </span>
                                </div>

                                <!-- WhatsApp -->
                                <div class="absolute top-4 right-4">
                                    <a href="https://wa.me/5511999999999?text=Olá! Tenho interesse no imóvel em destaque: <?= urlencode($imovel['titulo']) ?>" 
                                       target="_blank"
                                       class="bg-green-500 hover:bg-green-600 text-white w-12 h-12 rounded-full flex items-center justify-center transition-colors shadow-lg">
                                        <i class="fab fa-whatsapp text-xl"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-800 mb-2 line-clamp-2">
                                    <?= htmlspecialchars($imovel['titulo']) ?>
                                </h3>
                                
                                <div class="flex items-center text-gray-600 mb-4">
                                    <i class="fas fa-map-marker-alt mr-2 text-red-500"></i>
                                    <span class="text-sm">
                                        <?= htmlspecialchars($imovel['bairro'] ?? $imovel['localizacao'] ?? 'Localização não informada') ?>
                                    </span>
                                </div>

                                <div class="text-3xl font-bold text-blue-600 mb-4">
                                    R$ <?= number_format($imovel['preco'], 2, ',', '.') ?>
                                    <span class="text-sm text-gray-500 font-normal">
                                        <?= ($imovel['tipo_negocio'] ?? 'venda') == 'aluguel' ? '/mês' : '' ?>
                                    </span>
                                </div>

                                <!-- Características -->
                                <div class="grid grid-cols-4 gap-3 mb-6">
                                    <div class="text-center">
                                        <i class="fas fa-bed text-blue-500 text-lg mb-1"></i>
                                        <div class="text-sm font-semibold"><?= $imovel['quartos'] ?? '—' ?></div>
                                        <div class="text-xs text-gray-500">Quartos</div>
                                    </div>
                                    <div class="text-center">
                                        <i class="fas fa-bath text-blue-500 text-lg mb-1"></i>
                                        <div class="text-sm font-semibold"><?= $imovel['banheiros'] ?? '—' ?></div>
                                        <div class="text-xs text-gray-500">Banheiros</div>
                                    </div>
                                    <div class="text-center">
                                        <i class="fas fa-car text-blue-500 text-lg mb-1"></i>
                                        <div class="text-sm font-semibold"><?= $imovel['garagem'] ?? '—' ?></div>
                                        <div class="text-xs text-gray-500">Garagem</div>
                                    </div>
                                    <div class="text-center">
                                        <i class="fas fa-ruler-combined text-blue-500 text-lg mb-1"></i>
                                        <div class="text-sm font-semibold"><?= $imovel['area'] ?? '—' ?></div>
                                        <div class="text-xs text-gray-500">m²</div>
                                    </div>
                                </div>

                                <a href="imovel.php?id=<?= $imovel['id'] ?>" 
                                   class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-center py-3 rounded-xl font-semibold transition-all transform hover:scale-[1.02]">
                                    <i class="fas fa-eye mr-2"></i>Ver Detalhes
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="text-center">
                <a href="imoveis.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-4 rounded-full font-semibold text-lg transition-all transform hover:scale-105 shadow-lg">
                    <i class="fas fa-th-large mr-2"></i>Ver Todos os Imóveis
                </a>
            </div>
        </div>
    </section>

    <!-- Imóveis Recentes -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Últimos <span class="gradient-text">Lançamentos</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Confira os imóveis mais recentes do nosso portfólio. Oportunidades que acabaram de chegar!
                </p>
            </div>

            <?php if (!empty($imoveis_recentes)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach(array_slice($imoveis_recentes, 0, 8) as $imovel): ?>
                        <div class="card-hover bg-white rounded-xl shadow-md overflow-hidden">
                            <div class="relative">
                                <?php if(!empty($imovel['imagem'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($imovel['imagem']) ?>" 
                                         alt="<?= htmlspecialchars($imovel['titulo']) ?>" 
                                         class="w-full h-48 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                        <i class="fas fa-home text-3xl text-gray-400"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="absolute top-3 left-3">
                                    <span class="<?= ($imovel['tipo_negocio'] ?? 'venda') == 'venda' ? 'bg-green-600' : 'bg-orange-600' ?> text-white px-2 py-1 rounded-full text-xs font-semibold uppercase">
                                        <?= ($imovel['tipo_negocio'] ?? 'venda') == 'venda' ? 'Venda' : 'Aluguel' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="font-bold text-gray-800 mb-2 text-sm line-clamp-2">
                                    <?= htmlspecialchars($imovel['titulo']) ?>
                                </h3>
                                
                                <div class="text-lg font-bold text-blue-600 mb-3">
                                    R$ <?= number_format($imovel['preco'], 0, ',', '.') ?>
                                </div>

                                <div class="grid grid-cols-3 gap-2 text-xs text-gray-600 mb-3">
                                    <div class="text-center">
                                        <i class="fas fa-bed text-blue-500"></i>
                                        <div><?= $imovel['quartos'] ?? '—' ?></div>
                                    </div>
                                    <div class="text-center">
                                        <i class="fas fa-bath text-blue-500"></i>
                                        <div><?= $imovel['banheiros'] ?? '—' ?></div>
                                    </div>
                                    <div class="text-center">
                                        <i class="fas fa-car text-blue-500"></i>
                                        <div><?= $imovel['garagem'] ?? '—' ?></div>
                                    </div>
                                </div>

                                <a href="imovel.php?id=<?= $imovel['id'] ?>" 
                                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-lg text-sm font-semibold transition-colors">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Sobre Nós -->
    <section id="sobre" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div>
                    <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-6">
                        Por que escolher nossa <span class="gradient-text">Imobiliária</span>?
                    </h2>
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                        Com mais de 15 anos de experiência no mercado imobiliário de Cambé e região, 
                        oferecemos um atendimento personalizado e as melhores oportunidades para você 
                        encontrar o imóvel dos seus sonhos.
                    </p>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-blue-100 rounded-full p-3 mr-4">
                                <i class="fas fa-award text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-2">Experiência Comprovada</h3>
                                <p class="text-gray-600">Mais de uma década ajudando famílias a realizarem o sonho da casa própria.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-green-100 rounded-full p-3 mr-4">
                                <i class="fas fa-handshake text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-2">Atendimento Personalizado</h3>
                                <p class="text-gray-600">Cada cliente é único. Oferecemos soluções sob medida para suas necessidades.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="bg-purple-100 rounded-full p-3 mr-4">
                                <i class="fas fa-shield-alt text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-2">Segurança e Transparência</h3>
                                <p class="text-gray-600">Processos transparentes e documentação completa para sua segurança.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="grid grid-cols-2 gap-4">
                        <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'><rect fill='%23f0f4f8' width='400' height='300'/><text fill='%23718096' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='20'>Casa Moderna</text></svg>" 
                             alt="Casa Moderna" class="rounded-lg shadow-lg">
                        <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'><rect fill='%23e2e8f0' width='400' height='300'/><text fill='%234a5568' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='20'>Apartamento</text></svg>" 
                             alt="Apartamento" class="rounded-lg shadow-lg mt-8">
                        <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'><rect fill='%23cbd5e0' width='400' height='300'/><text fill='%232d3748' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='20'>Sobrado</text></svg>" 
                             alt="Sobrado" class="rounded-lg shadow-lg">
                        <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'><rect fill='%23a0aec0' width='400' height='300'/><text fill='%231a202c' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-size='20'>Terreno</text></svg>" 
                             alt="Terreno" class="rounded-lg shadow-lg mt-8">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contato -->
    <section id="contato" class="py-20 bg-gradient-to-br from-blue-600 to-indigo-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
                    Entre em <span class="text-yellow-300">Contato</span>
                </h2>
                <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                    Pronto para encontrar seu imóvel ideal? Nossa equipe está aqui para ajudar você!
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <!-- WhatsApp -->
                <a href="https://wa.me/5511999999999" target="_blank" 
                   class="glass rounded-2xl p-8 text-center hover:bg-white/20 transition-all transform hover:scale-105">
                    <div class="bg-green-500 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fab fa-whatsapp text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">WhatsApp</h3>
                    <p class="text-blue-100 mb-4">Fale conosco agora mesmo</p>
                    <span class="text-yellow-300 font-semibold">(43) 9 9999-9999</span>
                </a>

                <!-- Telefone -->
                <div class="glass rounded-2xl p-8 text-center">
                    <div class="bg-blue-500 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-phone text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Telefone</h3>
                    <p class="text-blue-100 mb-4">Ligue para nós</p>
                    <span class="text-yellow-300 font-semibold">(43) 3254-1234</span>
                </div>

                <!-- Endereço -->
                <div class="glass rounded-2xl p-8 text-center">
                    <div class="bg-red-500 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">Endereço</h3>
                    <p class="text-blue-100 mb-4">Visite nossa loja</p>
                    <span class="text-yellow-300 font-semibold">Cambé - PR</span>
                </div>
            </div>

            <!-- CTA Final -->
            <div class="text-center">
                <a href="imoveis.php" 
                   class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 px-10 py-4 rounded-full font-bold text-xl transition-all transform hover:scale-105 shadow-lg inline-flex items-center">
                    <i class="fas fa-home mr-3"></i>
                    Encontrar Meu Imóvel Agora
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo e Descrição -->
                <div class="md:col-span-2">
                    <div class="flex items-center mb-4">
                        <span class="text-3xl font-bold gradient-text">
                            <i class="fas fa-home mr-2"></i>Imobiliária
                        </span>
                    </div>
                    <p class="text-gray-400 leading-relaxed mb-6">
                        Sua parceira de confiança no mercado imobiliário. Realizamos sonhos e conectamos 
                        pessoas ao lar perfeito há mais de 15 anos em Cambé e região.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-blue-600 hover:bg-blue-700 w-10 h-10 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-pink-600 hover:bg-pink-700 w-10 h-10 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://wa.me/5511999999999" class="bg-green-600 hover:bg-green-700 w-10 h-10 rounded-full flex items-center justify-center transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Links Úteis -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Links Úteis</h4>
                    <ul class="space-y-2">
                        <li><a href="#inicio" class="text-gray-400 hover:text-white transition-colors">Início</a></li>
                        <li><a href="imoveis.php" class="text-gray-400 hover:text-white transition-colors">Imóveis</a></li>
                        <li><a href="#sobre" class="text-gray-400 hover:text-white transition-colors">Sobre Nós</a></li>
                        <li><a href="#contato" class="text-gray-400 hover:text-white transition-colors">Contato</a></li>
                        <li><a href="../admin/login.php" class="text-gray-400 hover:text-white transition-colors">Área Admin</a></li>
                    </ul>
                </div>

                <!-- Contato -->
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contato</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="fas fa-phone text-blue-500 mr-3"></i>
                            <span class="text-gray-400">(43) 3254-1234</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fab fa-whatsapp text-green-500 mr-3"></i>
                            <span class="text-gray-400">(43) 9 9999-9999</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-red-500 mr-3"></i>
                            <span class="text-gray-400">contato@imobiliaria.com</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt text-purple-500 mr-3"></i>
                            <span class="text-gray-400">Cambé - PR</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; 2024 Imobiliária. Todos os direitos reservados. 
                    <span class="text-blue-400">Desenvolvido com ❤️ para conectar você ao seu lar ideal.</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Scroll to Top Button -->
    <button id="scrollToTop" class="fixed bottom-6 right-6 bg-blue-600 hover:bg-blue-700 text-white w-12 h-12 rounded-full shadow-lg opacity-0 invisible transition-all transform hover:scale-110">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Scripts -->
    <script>
        // Smooth scroll for navigation links
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

        // Scroll to top button
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.remove('opacity-0', 'invisible');
                scrollToTopBtn.classList.add('opacity-100', 'visible');
            } else {
                scrollToTopBtn.classList.add('opacity-0', 'invisible');
                scrollToTopBtn.classList.remove('opacity-100', 'visible');
            }
        });

        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Mobile menu toggle (adicionar se necessário)
        const mobileMenuBtn = document.querySelector('.md\\:hidden button');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                // Implementar menu mobile se necessário
                console.log('Mobile menu clicked');
            });
        }

        // Animação de entrada dos cards quando entram na viewport
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Aplicar animação aos cards
        document.querySelectorAll('.card-hover').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Contador animado para estatísticas
        const animateCounters = () => {
            const counters = document.querySelectorAll('[data-counter]');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-counter'));
                const duration = 2000;
                const increment = target / (duration / 16);
                let current = 0;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        counter.textContent = Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
            });
        };

        // Iniciar contadores quando a seção de estatísticas aparecer
        const statsSection = document.querySelector('.grid.grid-cols-2.md\\:grid-cols-4');
        if (statsSection) {
            const statsObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounters();
                        statsObserver.unobserve(entry.target);
                    }
                });
            });
            statsObserver.observe(statsSection);
        }
    </script>