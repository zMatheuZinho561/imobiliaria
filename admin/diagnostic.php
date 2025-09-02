<?php
require_once "../includes/config.php";
session_start();

echo "<h1>Diagnóstico do Sistema de Imóveis</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; background: #e8f5e8; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .error { color: red; background: #ffe8e8; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .warning { color: orange; background: #fff8e1; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .info { color: blue; background: #e8f4ff; padding: 10px; margin: 10px 0; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// 1. Verificar conexão com banco
try {
    echo "<h2>1. Conexão com Banco de Dados</h2>";
    $pdo->query("SELECT 1");
    echo "<div class='success'>✓ Conexão com banco estabelecida com sucesso</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro na conexão: " . $e->getMessage() . "</div>";
    exit;
}

// 2. Verificar se tabela existe e sua estrutura
try {
    echo "<h2>2. Estrutura da Tabela 'imoveis'</h2>";
    
    $stmt = $pdo->query("DESCRIBE imoveis");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "<div class='error'>✗ Tabela 'imoveis' não existe!</div>";
        echo "<div class='info'>Execute este SQL para criar a tabela:</div>";
        echo "<pre>
CREATE TABLE imoveis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    tipo VARCHAR(100),
    preco DECIMAL(10,2),
    area VARCHAR(50),
    quartos INT,
    banheiros INT,
    garagem INT,
    localizacao VARCHAR(255),
    imagem VARCHAR(255),
    status VARCHAR(50) DEFAULT 'Disponível',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
        </pre>";
    } else {
        echo "<div class='success'>✓ Tabela 'imoveis' existe</div>";
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro ao verificar estrutura: " . $e->getMessage() . "</div>";
}

// 3. Contar registros na tabela
try {
    echo "<h2>3. Dados na Tabela</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM imoveis");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($total == 0) {
        echo "<div class='warning'>⚠ Nenhum imóvel cadastrado na tabela</div>";
    } else {
        echo "<div class='success'>✓ Total de imóveis cadastrados: {$total}</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro ao contar registros: " . $e->getMessage() . "</div>";
}

// 4. Mostrar últimos registros
try {
    echo "<h2>4. Últimos Registros Cadastrados</h2>";
    $stmt = $pdo->query("SELECT * FROM imoveis ORDER BY id DESC LIMIT 5");
    $imoveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($imoveis)) {
        echo "<div class='warning'>⚠ Nenhum registro encontrado</div>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Título</th><th>Tipo</th><th>Status</th><th>Preço</th><th>Criado em</th></tr>";
        foreach ($imoveis as $imovel) {
            echo "<tr>";
            echo "<td>{$imovel['id']}</td>";
            echo "<td>" . htmlspecialchars($imovel['titulo']) . "</td>";
            echo "<td>" . htmlspecialchars($imovel['tipo']) . "</td>";
            echo "<td>" . htmlspecialchars($imovel['status']) . "</td>";
            echo "<td>R$ " . number_format($imovel['preco'], 2, ',', '.') . "</td>";
            echo "<td>" . ($imovel['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro ao buscar registros: " . $e->getMessage() . "</div>";
}

// 5. Testar consulta similar à do index
try {
    echo "<h2>5. Teste da Consulta do Index (Imóveis Disponíveis)</h2>";
    $stmt = $pdo->query("SELECT * FROM imoveis WHERE status = 'Disponível' ORDER BY id DESC");
    $imoveis_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>Imóveis com status 'Disponível': " . count($imoveis_disponiveis) . "</div>";
    
    if (!empty($imoveis_disponiveis)) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Título</th><th>Status</th><th>Localização</th></tr>";
        foreach ($imoveis_disponiveis as $imovel) {
            echo "<tr>";
            echo "<td>{$imovel['id']}</td>";
            echo "<td>" . htmlspecialchars($imovel['titulo']) . "</td>";
            echo "<td>" . htmlspecialchars($imovel['status']) . "</td>";
            echo "<td>" . htmlspecialchars($imovel['localizacao']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro na consulta do index: " . $e->getMessage() . "</div>";
}

// 6. Verificar problemas de charset
try {
    echo "<h2>6. Configuração de Charset</h2>";
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set_%'");
    $charset_vars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Variável</th><th>Valor</th></tr>";
    foreach ($charset_vars as $var) {
        echo "<tr>";
        echo "<td>{$var['Variable_name']}</td>";
        echo "<td>{$var['Value']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro ao verificar charset: " . $e->getMessage() . "</div>";
}

// 7. Verificar se há problemas nos dados
try {
    echo "<h2>7. Verificação de Dados Problemáticos</h2>";
    
    // Verificar registros com campos obrigatórios vazios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM imoveis WHERE titulo IS NULL OR titulo = ''");
    $sem_titulo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM imoveis WHERE status IS NULL OR status = ''");
    $sem_status = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($sem_titulo > 0) {
        echo "<div class='warning'>⚠ {$sem_titulo} imóveis sem título</div>";
    }
    if ($sem_status > 0) {
        echo "<div class='warning'>⚠ {$sem_status} imóveis sem status</div>";
    }
    
    // Verificar status únicos
    $stmt = $pdo->query("SELECT DISTINCT status FROM imoveis");
    $status_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='info'>Status encontrados na tabela: " . implode(', ', array_map('htmlspecialchars', $status_list)) . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro na verificação de dados: " . $e->getMessage() . "</div>";
}

// 8. Teste de inserção
echo "<h2>8. Teste de Inserção</h2>";
try {
    $stmt = $pdo->prepare("INSERT INTO imoveis (titulo, descricao, tipo, preco, area, quartos, banheiros, garagem, localizacao, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $teste_data = [
        'Teste de Inserção - ' . date('Y-m-d H:i:s'),
        'Descrição de teste',
        'Casa',
        250000.00,
        '120',
        3,
        2,
        2,
        'Localização Teste',
        'Disponível'
    ];
    
    if ($stmt->execute($teste_data)) {
        $inserted_id = $pdo->lastInsertId();
        echo "<div class='success'>✓ Teste de inserção bem-sucedido. ID inserido: {$inserted_id}</div>";
        
        // Remover o registro de teste
        $stmt = $pdo->prepare("DELETE FROM imoveis WHERE id = ?");
        $stmt->execute([$inserted_id]);
        echo "<div class='info'>Registro de teste removido</div>";
    } else {
        echo "<div class='error'>✗ Falha no teste de inserção</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro no teste de inserção: " . $e->getMessage() . "</div>";
}

// 9. Mostrar configuração do PDO
echo "<h2>9. Configuração do PDO</h2>";
try {
    $attrs = [
        PDO::ATTR_AUTOCOMMIT,
        PDO::ATTR_ERRMODE,
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
    ];
    
    echo "<table>";
    echo "<tr><th>Atributo</th><th>Valor</th></tr>";
    foreach ($attrs as $attr) {
        try {
            $value = $pdo->getAttribute($attr);
            echo "<tr><td>{$attr}</td><td>{$value}</td></tr>";
        } catch (Exception $e) {
            echo "<tr><td>{$attr}</td><td>Não disponível</td></tr>";
        }
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Erro ao verificar configuração PDO: " . $e->getMessage() . "</div>";
}

echo "<h2>10. Recomendações</h2>";
echo "<div class='info'>
<strong>Se os imóveis não aparecem no index, verifique:</strong><br>
1. Se o status está sendo salvo como 'Disponível' (com acento)<br>
2. Se a consulta no index.php usa o mesmo status<br>
3. Se não há espaços extras nos campos<br>
4. Se o formulário está enviando dados corretamente<br>
5. Se há filtros ativos que estão escondendo os imóveis
</div>";

?>

<!-- Formulário para testar inserção manual -->
<h2>11. Teste Manual de Inserção</h2>
<form method="post" style="background: #f9f9f9; padding: 20px; border-radius: 5px;">
    <h3>Testar Inserção de Imóvel</h3>
    <table>
        <tr><td>Título:</td><td><input type="text" name="titulo" required style="width: 300px; padding: 5px;"></td></tr>
        <tr><td>Descrição:</td><td><textarea name="descricao" style="width: 300px; height: 60px; padding: 5px;"></textarea></td></tr>
        <tr><td>Tipo:</td><td><input type="text" name="tipo" value="Casa" style="width: 300px; padding: 5px;"></td></tr>
        <tr><td>Preço:</td><td><input type="number" name="preco" step="0.01" required style="width: 300px; padding: 5px;"></td></tr>
        <tr><td>Área:</td><td><input type="text" name="area" style="width: 300px; padding: 5px;"></td></tr>
        <tr><td>Quartos:</td><td><input type="number" name="quartos" style="width: 300px; padding: 5px;"></td></tr>
        <tr><td>Banheiros:</td><td><input type="number" name="banheiros" style="width: 300px; padding: 5px;"></td></tr>
        <tr><td>Garagem:</td><td><input type="number" name="garagem" style="width: 300px; padding: 5px;"></td></tr>
        <tr><td>Localização:</td><td><input type="text" name="localizacao" style="width: 300px; padding: 5px;"></td></tr>
        <tr><td>Status:</td><td>
            <select name="status" style="width: 300px; padding: 5px;">
                <option value="Disponível">Disponível</option>
                <option value="Vendido">Vendido</option>
                <option value="Alugado">Alugado</option>
            </select>
        </td></tr>
        <tr><td colspan="2"><input type="submit" name="test_insert" value="Inserir Teste" style="padding: 10px 20px; background: green; color: white; border: none; border-radius: 5px; cursor: pointer;"></td></tr>
    </table>
</form>

<?php
// Processar inserção de teste
if (isset($_POST['test_insert'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO imoveis (titulo, descricao, tipo, preco, area, quartos, banheiros, garagem, localizacao, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $result = $stmt->execute([
            $_POST['titulo'],
            $_POST['descricao'],
            $_POST['tipo'],
            $_POST['preco'],
            $_POST['area'],
            $_POST['quartos'],
            $_POST['banheiros'],
            $_POST['garagem'],
            $_POST['localizacao'],
            $_POST['status']
        ]);
        
        if ($result) {
            $inserted_id = $pdo->lastInsertId();
            echo "<div class='success'>✓ Imóvel inserido com sucesso! ID: {$inserted_id}</div>";
            echo "<div class='info'>Agora verifique se ele aparece no index.php</div>";
        } else {
            echo "<div class='error'>✗ Falha na inserção</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>✗ Erro na inserção: " . $e->getMessage() . "</div>";
    }
}
?>