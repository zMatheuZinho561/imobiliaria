<?php
require_once "../includes/config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Validar dados obrigatórios
    $required_fields = [
        'ownerName' => 'Nome do proprietário',
        'ownerPhone' => 'Telefone',
        'cep' => 'CEP',
        'state' => 'Estado',
        'city' => 'Cidade',
        'neighborhood' => 'Bairro',
        'street' => 'Rua',
        'number' => 'Número',
        'propertyType' => 'Tipo do imóvel',
        'propertyStatus' => 'Situação do imóvel',
        'description' => 'Descrição'
    ];

    $missing_fields = [];
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $label;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception('Campos obrigatórios não preenchidos: ' . implode(', ', $missing_fields));
    }

    // Validar finalidade (pelo menos uma deve estar selecionada)
    $finalidades = $_POST['purpose'] ?? [];
    if (empty($finalidades)) {
        throw new Exception('Selecione pelo menos uma finalidade (Venda ou Aluguel)');
    }

    // Validar se tem pelo menos um valor (venda ou aluguel)
    $valor_venda = $_POST['saleValue'] ?? '';
    $valor_aluguel = $_POST['rentValue'] ?? '';
    
    if (empty($valor_venda) && empty($valor_aluguel)) {
        throw new Exception('Informe pelo menos um valor (venda ou aluguel)');
    }

    // Validar descrição mínima
    if (strlen($_POST['description']) < 50) {
        throw new Exception('A descrição deve ter pelo menos 50 caracteres');
    }

    // Processar finalidades
    $finalidade_str = implode(', ', array_map(function($f) {
        return $f === 'venda' ? 'Venda' : 'Aluguel';
    }, $finalidades));

    // Preparar dados para inserção
    $dados = [
        'nome_proprietario' => trim($_POST['ownerName']),
        'telefone_proprietario' => trim($_POST['ownerPhone']),
        'email_proprietario' => trim($_POST['ownerEmail'] ?? ''),
        'cpf_proprietario' => trim($_POST['ownerCpf'] ?? ''),
        'cep' => trim($_POST['cep']),
        'estado' => trim($_POST['state']),
        'cidade' => trim($_POST['city']),
        'bairro' => trim($_POST['neighborhood']),
        'rua' => trim($_POST['street']),
        'numero' => trim($_POST['number']),
        'complemento' => trim($_POST['complement'] ?? ''),
        'nome_condominio' => trim($_POST['buildingName'] ?? ''),
        'tipo_imovel' => trim($_POST['propertyType']),
        'situacao_imovel' => trim($_POST['propertyStatus']),
        'finalidade' => $finalidade_str,
        'valor_venda' => $valor_venda,
        'valor_aluguel' => $valor_aluguel,
        'dormitorios' => intval($_POST['bedrooms'] ?? 0),
        'suites' => intval($_POST['suites'] ?? 0),
        'banheiros' => intval($_POST['bathrooms'] ?? 0),
        'vagas_garagem' => intval($_POST['parkingSpaces'] ?? 0),
        'area_total' => trim($_POST['totalArea'] ?? ''),
        'area_privativa' => trim($_POST['privateArea'] ?? ''),
        'valor_condominio' => trim($_POST['condoFee'] ?? ''),
        'iptu' => trim($_POST['iptu'] ?? ''),
        'descricao' => trim($_POST['description']),
        'status' => 'pendente'
    ];

    // Inserir no banco de dados
    $sql = "INSERT INTO solicitacoes_anuncio (
        nome_proprietario, telefone_proprietario, email_proprietario, cpf_proprietario,
        cep, estado, cidade, bairro, rua, numero, complemento, nome_condominio,
        tipo_imovel, situacao_imovel, finalidade, valor_venda, valor_aluguel,
        dormitorios, suites, banheiros, vagas_garagem, area_total, area_privativa,
        valor_condominio, iptu, descricao, status, created_at
    ) VALUES (
        :nome_proprietario, :telefone_proprietario, :email_proprietario, :cpf_proprietario,
        :cep, :estado, :cidade, :bairro, :rua, :numero, :complemento, :nome_condominio,
        :tipo_imovel, :situacao_imovel, :finalidade, :valor_venda, :valor_aluguel,
        :dormitorios, :suites, :banheiros, :vagas_garagem, :area_total, :area_privativa,
        :valor_condominio, :iptu, :descricao, :status, NOW()
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($dados);

    $solicitacao_id = $pdo->lastInsertId();

    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Solicitação enviada com sucesso!',
        'solicitacao_id' => $solicitacao_id,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>