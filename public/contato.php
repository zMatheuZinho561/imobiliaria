<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anuncie seu Imóvel - ImóvelPro</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --background-color: #f8fafc;
            --card-background: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --border-hover: #cbd5e1;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container {
            background: var(--card-background);
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            position: relative;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-color), var(--success-color));
        }

        .form-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .form-header h2 {
            font-size: 1.8rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .form-content {
            padding: 2.5rem;
        }

        .progress-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .progress-step {
            display: flex;
            align-items: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .progress-step.active {
            color: var(--primary-color);
        }

        .progress-step .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .progress-step.active .step-number {
            background: var(--primary-color);
            color: white;
        }

        .progress-step.completed .step-number {
            background: var(--success-color);
            color: white;
        }

        .progress-step:not(:last-child)::after {
            content: '';
            width: 60px;
            height: 2px;
            background: var(--border-color);
            margin: 0 1rem;
        }

        .form-section {
            display: none;
            animation: slideIn 0.3s ease;
        }

        .form-section.active {
            display: block;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .section-title {
            display: flex;
            align-items: center;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .section-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-grid.full-width {
            grid-template-columns: 1fr;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .required {
            color: var(--error-color);
            font-weight: 700;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control:hover {
            border-color: var(--border-hover);
        }

        .form-control.error {
            border-color: var(--error-color);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .checkbox-group {
            display: flex;
            gap: 2rem;
            margin: 1rem 0;
            flex-wrap: wrap;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            transition: all 0.3s ease;
            background: white;
        }

        .checkbox-item:hover {
            border-color: var(--primary-color);
            background: rgba(37, 99, 235, 0.05);
        }

        .checkbox-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--primary-color);
            cursor: pointer;
        }

        .checkbox-item label {
            cursor: pointer;
            margin: 0;
            font-weight: 500;
        }

        .form-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-secondary {
            background: var(--secondary-color);
            color: white;
        }

        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border: 2px solid rgba(16, 185, 129, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 2px solid rgba(239, 68, 68, 0.2);
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .help-text {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
            font-style: italic;
        }

        .form-summary {
            background: rgba(37, 99, 235, 0.05);
            border: 2px solid rgba(37, 99, 235, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .summary-item {
            display: flex;
            flex-direction: column;
        }

        .summary-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .summary-value {
            color: var(--text-primary);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header h1 {
                font-size: 2rem;
            }

            .form-content {
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .checkbox-group {
                flex-direction: column;
                gap: 1rem;
            }

            .form-navigation {
                flex-direction: column;
                gap: 1rem;
            }

            .progress-step:not(:last-child)::after {
                width: 40px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-home"></i> ImóvelPro</h1>
            <p>Anuncie seu imóvel de forma rápida e profissional. Nossa equipe especializada irá avaliar e aprovar sua solicitação.</p>
        </div>

        <div class="form-container">
            <div class="form-header">
                <h2>Anuncie seu Imóvel</h2>
                <p>Preencha as informações abaixo e nossa equipe entrará em contato em até 24 horas</p>
            </div>

            <div class="form-content">
                <div class="progress-indicator">
                    <div class="progress-step active" data-step="1">
                        <span class="step-number">1</span>
                        <span>Seus Dados</span>
                    </div>
                    <div class="progress-step" data-step="2">
                        <span class="step-number">2</span>
                        <span>Localização</span>
                    </div>
                    <div class="progress-step" data-step="3">
                        <span class="step-number">3</span>
                        <span>Detalhes</span>
                    </div>
                    <div class="progress-step" data-step="4">
                        <span class="step-number">4</span>
                        <span>Resumo</span>
                    </div>
                </div>

                <div id="alert-container"></div>

                <form id="property-form">
                    <!-- Seção 1: Dados Pessoais -->
                    <div class="form-section active" data-section="1">
                        <div class="section-title">
                            <i class="fas fa-user"></i>
                            Seus Dados Pessoais
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="owner-name">Nome Completo <span class="required">*</span></label>
                                <input type="text" id="owner-name" name="ownerName" class="form-control" placeholder="Digite seu nome completo" required>
                            </div>
                            <div class="form-group">
                                <label for="owner-phone">Telefone <span class="required">*</span></label>
                                <input type="tel" id="owner-phone" name="ownerPhone" class="form-control" placeholder="(00) 00000-0000" required>
                                <div class="help-text">Utilizaremos este número para entrar em contato</div>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="owner-email">E-mail</label>
                                <input type="email" id="owner-email" name="ownerEmail" class="form-control" placeholder="seu@email.com">
                                <div class="help-text">E-mail para comunicações importantes</div>
                            </div>
                            <div class="form-group">
                                <label for="owner-cpf">CPF</label>
                                <input type="text" id="owner-cpf" name="ownerCpf" class="form-control" placeholder="000.000.000-00">
                            </div>
                        </div>
                    </div>

                    <!-- Seção 2: Localização -->
                    <div class="form-section" data-section="2">
                        <div class="section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Localização do Imóvel
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="cep">CEP <span class="required">*</span></label>
                                <input type="text" id="cep" name="cep" class="form-control" placeholder="00000-000" required>
                                <div class="help-text">Digite o CEP para preenchimento automático</div>
                            </div>
                            <div class="form-group">
                                <label for="state">Estado <span class="required">*</span></label>
                                <select id="state" name="state" class="form-control" required>
                                    <option value="">Selecione o estado</option>
                                    <option value="AC">Acre</option>
                                    <option value="AL">Alagoas</option>
                                    <option value="AP">Amapá</option>
                                    <option value="AM">Amazonas</option>
                                    <option value="BA">Bahia</option>
                                    <option value="CE">Ceará</option>
                                    <option value="DF">Distrito Federal</option>
                                    <option value="ES">Espírito Santo</option>
                                    <option value="GO">Goiás</option>
                                    <option value="MA">Maranhão</option>
                                    <option value="MT">Mato Grosso</option>
                                    <option value="MS">Mato Grosso do Sul</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="PA">Pará</option>
                                    <option value="PB">Paraíba</option>
                                    <option value="PR">Paraná</option>
                                    <option value="PE">Pernambuco</option>
                                    <option value="PI">Piauí</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="RN">Rio Grande do Norte</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="RO">Rondônia</option>
                                    <option value="RR">Roraima</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="SE">Sergipe</option>
                                    <option value="TO">Tocantins</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="city">Cidade <span class="required">*</span></label>
                                <input type="text" id="city" name="city" class="form-control" placeholder="Nome da cidade" required>
                            </div>
                            <div class="form-group">
                                <label for="neighborhood">Bairro <span class="required">*</span></label>
                                <input type="text" id="neighborhood" name="neighborhood" class="form-control" placeholder="Nome do bairro" required>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="street">Rua/Avenida <span class="required">*</span></label>
                                <input type="text" id="street" name="street" class="form-control" placeholder="Nome da rua ou avenida" required>
                            </div>
                            <div class="form-group">
                                <label for="number">Número <span class="required">*</span></label>
                                <input type="text" id="number" name="number" class="form-control" placeholder="123" required>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="complement">Complemento</label>
                                <input type="text" id="complement" name="complement" class="form-control" placeholder="Apto 101, Casa 2, etc.">
                            </div>
                            <div class="form-group">
                                <label for="building-name">Nome do Condomínio/Edifício</label>
                                <input type="text" id="building-name" name="buildingName" class="form-control" placeholder="Nome do condomínio">
                            </div>
                        </div>
                    </div>

                    <!-- Seção 3: Detalhes do Imóvel -->
                    <div class="form-section" data-section="3">
                        <div class="section-title">
                            <i class="fas fa-building"></i>
                            Detalhes do Imóvel
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="property-type">Tipo do Imóvel <span class="required">*</span></label>
                                <select id="property-type" name="propertyType" class="form-control" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="apartamento">Apartamento</option>
                                    <option value="casa">Casa</option>
                                    <option value="sobrado">Sobrado</option>
                                    <option value="chacara">Chácara</option>
                                    <option value="terreno">Terreno</option>
                                    <option value="comercial">Comercial</option>
                                    <option value="industrial">Industrial</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="property-status">Situação do Imóvel <span class="required">*</span></label>
                                <select id="property-status" name="propertyStatus" class="form-control" required>
                                    <option value="">Selecione a situação</option>
                                    <option value="novo">Novo</option>
                                    <option value="usado">Usado</option>
                                    <option value="em-construcao">Em construção</option>
                                    <option value="reforma">Precisa de reforma</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Finalidade <span class="required">*</span></label>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="for-sale" name="purpose" value="venda">
                                    <label for="for-sale">Venda</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="for-rent" name="purpose" value="aluguel">
                                    <label for="for-rent">Aluguel</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="sale-value">Valor de Venda</label>
                                <input type="text" id="sale-value" name="saleValue" class="form-control" placeholder="R$ 0,00">
                            </div>
                            <div class="form-group">
                                <label for="rent-value">Valor do Aluguel</label>
                                <input type="text" id="rent-value" name="rentValue" class="form-control" placeholder="R$ 0,00">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="bedrooms">Dormitórios</label>
                                <input type="number" id="bedrooms" name="bedrooms" class="form-control" placeholder="0" min="0" max="20">
                            </div>
                            <div class="form-group">
                                <label for="suites">Suítes</label>
                                <input type="number" id="suites" name="suites" class="form-control" placeholder="0" min="0" max="10">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="bathrooms">Banheiros</label>
                                <input type="number" id="bathrooms" name="bathrooms" class="form-control" placeholder="0" min="0" max="20">
                            </div>
                            <div class="form-group">
                                <label for="parking-spaces">Vagas de Garagem</label>
                                <input type="number" id="parking-spaces" name="parkingSpaces" class="form-control" placeholder="0" min="0" max="10">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="total-area">Área Total (m²)</label>
                                <input type="text" id="total-area" name="totalArea" class="form-control" placeholder="100">
                            </div>
                            <div class="form-group">
                                <label for="private-area">Área Privativa (m²)</label>
                                <input type="text" id="private-area" name="privateArea" class="form-control" placeholder="80">
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="condo-fee">Valor do Condomínio</label>
                                <input type="text" id="condo-fee" name="condoFee" class="form-control" placeholder="R$ 0,00">
                            </div>
                            <div class="form-group">
                                <label for="iptu">IPTU Anual</label>
                                <input type="text" id="iptu" name="iptu" class="form-control" placeholder="R$ 0,00">
                            </div>
                        </div>

                        <div class="form-grid full-width">
                            <div class="form-group">
                                <label for="description">Descrição e Diferenciais <span class="required">*</span></label>
                                <textarea id="description" name="description" class="form-control" placeholder="Descreva os principais diferenciais do seu imóvel, como localização, acabamento, vista, proximidade de pontos importantes, etc." required></textarea>
                                <div class="help-text">Mínimo de 50 caracteres para uma descrição completa</div>
                            </div>
                        </div>
                    </div>

                    <!-- Seção 4: Resumo -->
                    <div class="form-section" data-section="4">
                        <div class="section-title">
                            <i class="fas fa-check-circle"></i>
                            Resumo da Solicitação
                        </div>

                        <div class="form-summary">
                            <div class="summary-title">Confira os dados informados</div>
                            <div id="form-summary-content">
                                <!-- Conteúdo será preenchido via JavaScript -->
                            </div>
                        </div>

                        <div class="alert alert-success">
                            <i class="fas fa-info-circle"></i>
                            Após o envio, nossa equipe analisará sua solicitação e entrará em contato em até 24 horas úteis.
                        </div>
                    </div>

                    <div class="form-navigation">
                        <button type="button" id="prev-btn" class="btn btn-secondary" style="display: none;">
                            <i class="fas fa-chevron-left"></i>
                            Anterior
                        </button>
                        
                        <div style="flex: 1; text-align: center;">
                            <span id="step-indicator" class="help-text">Passo 1 de 4</span>
                        </div>

                        <button type="button" id="next-btn" class="btn btn-primary">
                            Próximo
                            <i class="fas fa-chevron-right"></i>
                        </button>

                        <button type="submit" id="submit-btn" class="btn btn-primary" style="display: none;">
                            <i class="fas fa-paper-plane"></i>
                            Enviar Solicitação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <script>
        // Estado da aplicação
        let currentStep = 1;
        const totalSteps = 4;
        let formData = {};

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            updateStepDisplay();
        });

        // Event Listeners
        function initializeEventListeners() {
            // Navegação
            document.getElementById('next-btn').addEventListener('click', nextStep);
            document.getElementById('prev-btn').addEventListener('click', prevStep);
            
            // Formatação de campos
            setupFieldFormatting();
            
            // Validação em tempo real
            setupRealTimeValidation();
            
            // Busca de CEP
            document.getElementById('cep').addEventListener('blur', searchCEP);
            
            // Submissão do formulário
            document.getElementById('property-form').addEventListener('submit', submitForm);
            
            // Atualização automática de campos dependentes
            setupDependentFields();
        }

        // Formatação de campos
        function setupFieldFormatting() {
            // CPF
            document.getElementById('owner-cpf').addEventListener('input', function() {
                this.value = formatCPF(this.value);
            });

            // Telefone
            document.getElementById('owner-phone').addEventListener('input', function() {
                this.value = formatPhone(this.value);
            });

            // CEP
            document.getElementById('cep').addEventListener('input', function() {
                this.value = formatCEP(this.value);
            });

            // Valores monetários
            ['sale-value', 'rent-value', 'condo-fee', 'iptu'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', function() {
                        this.value = formatCurrency(this.value);
                    });
                }
            });

            // Áreas
            ['total-area', 'private-area'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('input', function() {
                        this.value = this.value.replace(/[^\d.,]/g, '');
                    });
                }
            });
        }

        // Validação em tempo real
        function setupRealTimeValidation() {
            const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                input.addEventListener('input', function() {
                    if (this.classList.contains('error')) {
                        validateField(this);
                    }
                });
            });
        }

        // Configurar campos dependentes
        function setupDependentFields() {
            // Atualizar finalidade baseado nos valores
            ['sale-value', 'rent-value'].forEach(id => {
                document.getElementById(id).addEventListener('input', function() {
                    updatePurposeCheckboxes();
                });
            });

            // Validação de pelo menos uma finalidade
            document.querySelectorAll('input[name="purpose"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    validatePurpose();
                });
            });
        }

        // Funções de formatação
        function formatCPF(value) {
            value = value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            }
            return value;
        }

        function formatPhone(value) {
            value = value.replace(/\D/g, '');
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
            return value;
        }

        function formatCEP(value) {
            value = value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
            }
            return value;
        }

        function formatCurrency(value) {
            value = value.replace(/\D/g, '');
            if (value.length === 0) return '';
            
            const numericValue = parseInt(value) / 100;
            return 'R$ ' + numericValue.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Busca de CEP
        async function searchCEP() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length !== 8) return;

            try {
                showLoadingOverlay(true);
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (!data.erro) {
                    document.getElementById('street').value = data.logradouro || '';
                    document.getElementById('neighborhood').value = data.bairro || '';
                    document.getElementById('city').value = data.localidade || '';
                    document.getElementById('state').value = data.uf || '';
                    
                    showAlert('success', 'Endereço encontrado automaticamente!');
                } else {
                    showAlert('error', 'CEP não encontrado. Preencha os dados manualmente.');
                }
            } catch (error) {
                showAlert('error', 'Erro ao buscar CEP. Preencha os dados manualmente.');
            } finally {
                showLoadingOverlay(false);
            }
        }

        // Validação de campos
        function validateField(field) {
            let isValid = true;
            const value = field.value.trim();

            // Remover classe de erro
            field.classList.remove('error');

            // Validações específicas
            if (field.hasAttribute('required') && !value) {
                isValid = false;
            } else if (field.type === 'email' && value && !isValidEmail(value)) {
                isValid = false;
            } else if (field.id === 'owner-cpf' && value && !isValidCPF(value)) {
                isValid = false;
            } else if (field.id === 'description' && value && value.length < 50) {
                isValid = false;
            }

            // Aplicar classe de erro se inválido
            if (!isValid) {
                field.classList.add('error');
            }

            return isValid;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidCPF(cpf) {
            cpf = cpf.replace(/\D/g, '');
            if (cpf.length !== 11) return false;
            
            // Verificar se todos os dígitos são iguais
            if (/^(\d)\1{10}$/.test(cpf)) return false;
            
            // Validar dígitos verificadores
            for (let t = 9; t < 11; t++) {
                let d = 0;
                for (let c = 0; c < t; c++) {
                    d += cpf[c] * ((t + 1) - c);
                }
                d = ((10 * d) % 11) % 10;
                if (cpf[t] != d) return false;
            }
            return true;
        }

        function validatePurpose() {
            const purposeCheckboxes = document.querySelectorAll('input[name="purpose"]');
            const isChecked = Array.from(purposeCheckboxes).some(cb => cb.checked);
            
            purposeCheckboxes.forEach(cb => {
                cb.parentElement.style.borderColor = isChecked ? '' : 'var(--error-color)';
            });
            
            return isChecked;
        }

        function updatePurposeCheckboxes() {
            const saleValue = document.getElementById('sale-value').value;
            const rentValue = document.getElementById('rent-value').value;
            const saleCheckbox = document.getElementById('for-sale');
            const rentCheckbox = document.getElementById('for-rent');

            if (saleValue && !saleCheckbox.checked) {
                saleCheckbox.checked = true;
            }
            if (rentValue && !rentCheckbox.checked) {
                rentCheckbox.checked = true;
            }
        }

        // Navegação entre passos
        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateStepDisplay();
                    if (currentStep === totalSteps) {
                        generateSummary();
                    }
                }
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepDisplay();
            }
        }

        function validateCurrentStep() {
            const currentSection = document.querySelector(`.form-section[data-section="${currentStep}"]`);
            const requiredFields = currentSection.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            // Validações específicas por passo
            if (currentStep === 3) {
                if (!validatePurpose()) {
                    showAlert('error', 'Selecione pelo menos uma finalidade (Venda ou Aluguel)');
                    isValid = false;
                }
            }

            if (!isValid) {
                showAlert('error', 'Preencha todos os campos obrigatórios corretamente');
            }

            return isValid;
        }

        function updateStepDisplay() {
            // Atualizar seções visíveis
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.add('active');

            // Atualizar indicador de progresso
            document.querySelectorAll('.progress-step').forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index + 1 < currentStep) {
                    step.classList.add('completed');
                } else if (index + 1 === currentStep) {
                    step.classList.add('active');
                }
            });

            // Atualizar botões de navegação
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const submitBtn = document.getElementById('submit-btn');

            prevBtn.style.display = currentStep > 1 ? 'inline-flex' : 'none';
            nextBtn.style.display = currentStep < totalSteps ? 'inline-flex' : 'none';
            submitBtn.style.display = currentStep === totalSteps ? 'inline-flex' : 'none';

            // Atualizar indicador de passo
            document.getElementById('step-indicator').textContent = `Passo ${currentStep} de ${totalSteps}`;
        }

        // Gerar resumo
        function generateSummary() {
            const formElement = document.getElementById('property-form');
            const formData = new FormData(formElement);
            
            // Coletar dados dos checkboxes
            const purposes = Array.from(document.querySelectorAll('input[name="purpose"]:checked'))
                .map(cb => cb.value === 'venda' ? 'Venda' : 'Aluguel');

            const summaryData = {
                'Proprietário': formData.get('ownerName') || 'Não informado',
                'Telefone': formData.get('ownerPhone') || 'Não informado',
                'E-mail': formData.get('ownerEmail') || 'Não informado',
                'Endereço': `${formData.get('street') || ''}, ${formData.get('number') || ''} - ${formData.get('neighborhood') || ''}, ${formData.get('city') || ''}/${formData.get('state') || ''}`,
                'CEP': formData.get('cep') || 'Não informado',
                'Tipo do Imóvel': getSelectText('property-type') || 'Não informado',
                'Situação': getSelectText('property-status') || 'Não informado',
                'Finalidade': purposes.length > 0 ? purposes.join(', ') : 'Não informado',
                'Valor de Venda': formData.get('saleValue') || 'Não informado',
                'Valor do Aluguel': formData.get('rentValue') || 'Não informado',
                'Dormitórios': formData.get('bedrooms') || '0',
                'Suítes': formData.get('suites') || '0',
                'Banheiros': formData.get('bathrooms') || '0',
                'Vagas': formData.get('parkingSpaces') || '0',
                'Área Total': formData.get('totalArea') ? formData.get('totalArea') + ' m²' : 'Não informado',
                'Área Privativa': formData.get('privateArea') ? formData.get('privateArea') + ' m²' : 'Não informado',
                'Condomínio': formData.get('condoFee') || 'Não informado',
                'IPTU': formData.get('iptu') || 'Não informado'
            };

            const summaryContainer = document.getElementById('form-summary-content');
            summaryContainer.innerHTML = `
                <div class="summary-grid">
                    ${Object.entries(summaryData).map(([label, value]) => `
                        <div class="summary-item">
                            <div class="summary-label">${label}</div>
                            <div class="summary-value">${value}</div>
                        </div>
                    `).join('')}
                </div>
                ${formData.get('description') ? `
                    <div class="summary-item" style="grid-column: 1 / -1; margin-top: 1rem;">
                        <div class="summary-label">Descrição</div>
                        <div class="summary-value">${formData.get('description')}</div>
                    </div>
                ` : ''}
            `;
        }

        function getSelectText(selectId) {
            const select = document.getElementById(selectId);
            return select.options[select.selectedIndex]?.text || '';
        }

        // Submissão do formulário
        async function submitForm(e) {
            e.preventDefault();
            
            showLoadingOverlay(true);
            
            try {
                // Simular envio (aqui você integraria com sua API)
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                // Coletar dados do formulário
                const formData = new FormData(e.target);
                const purposes = Array.from(document.querySelectorAll('input[name="purpose"]:checked'))
                    .map(cb => cb.value);
                
                const submissionData = {
                    id: Date.now(),
                    timestamp: new Date().toLocaleString('pt-BR'),
                    status: 'pending',
                    ownerName: formData.get('ownerName'),
                    ownerPhone: formData.get('ownerPhone'),
                    ownerEmail: formData.get('ownerEmail'),
                    ownerCpf: formData.get('ownerCpf'),
                    cep: formData.get('cep'),
                    state: formData.get('state'),
                    city: formData.get('city'),
                    neighborhood: formData.get('neighborhood'),
                    street: formData.get('street'),
                    number: formData.get('number'),
                    complement: formData.get('complement'),
                    buildingName: formData.get('buildingName'),
                    propertyType: formData.get('propertyType'),
                    propertyStatus: formData.get('propertyStatus'),
                    purposes: purposes,
                    saleValue: formData.get('saleValue'),
                    rentValue: formData.get('rentValue'),
                    bedrooms: formData.get('bedrooms'),
                    suites: formData.get('suites'),
                    bathrooms: formData.get('bathrooms'),
                    parkingSpaces: formData.get('parkingSpaces'),
                    totalArea: formData.get('totalArea'),
                    privateArea: formData.get('privateArea'),
                    condoFee: formData.get('condoFee'),
                    iptu: formData.get('iptu'),
                    description: formData.get('description')
                };

                // Salvar no localStorage para o painel admin
                const existingSubmissions = JSON.parse(localStorage.getItem('propertySubmissions') || '[]');
                existingSubmissions.push(submissionData);
                localStorage.setItem('propertySubmissions', JSON.stringify(existingSubmissions));
                
                showSuccessPage(submissionData.id);
                
            } catch (error) {
                showAlert('error', 'Erro ao enviar solicitação. Tente novamente.');
            } finally {
                showLoadingOverlay(false);
            }
        }

        // Página de sucesso
        function showSuccessPage(submissionId) {
            document.querySelector('.form-container').innerHTML = `
                <div class="form-header">
                    <h2 style="color: var(--success-color);">
                        <i class="fas fa-check-circle"></i>
                        Solicitação Enviada com Sucesso!
                    </h2>
                    <p>Protocolo: #${submissionId}</p>
                </div>
                <div class="form-content" style="text-align: center;">
                    <div class="alert alert-success" style="margin-bottom: 2rem;">
                        <i class="fas fa-info-circle"></i>
                        Sua solicitação foi recebida e será analisada por nossa equipe especializada.
                    </div>
                    
                    <div style="margin: 2rem 0;">
                        <h3 style="color: var(--text-primary); margin-bottom: 1rem;">Próximos Passos:</h3>
                        <div style="text-align: left; max-width: 500px; margin: 0 auto;">
                            <div style="display: flex; align-items: center; margin-bottom: 1rem; padding: 1rem; background: rgba(37, 99, 235, 0.05); border-radius: 10px;">
                                <div style="width: 40px; height: 40px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; font-weight: 600;">1</div>
                                <div>
                                    <strong>Análise Técnica</strong><br>
                                    <small style="color: var(--text-secondary);">Nossa equipe irá avaliar as informações do seu imóvel</small>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 1rem; padding: 1rem; background: rgba(16, 185, 129, 0.05); border-radius: 10px;">
                                <div style="width: 40px; height: 40px; background: var(--success-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; font-weight: 600;">2</div>
                                <div>
                                    <strong>Contato</strong><br>
                                    <small style="color: var(--text-secondary);">Entraremos em contato em até 24 horas úteis</small>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; margin-bottom: 1rem; padding: 1rem; background: rgba(245, 158, 11, 0.05); border-radius: 10px;">
                                <div style="width: 40px; height: 40px; background: var(--warning-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem; font-weight: 600;">3</div>
                                <div>
                                    <strong>Publicação</strong><br>
                                    <small style="color: var(--text-secondary);">Após aprovação, seu anúncio ficará disponível online</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 3rem;">
                        <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                            <i class="fas fa-plus"></i>
                            Anunciar Outro Imóvel
                        </button>
                    </div>
                </div>
            `;
        }

        // Utilitários
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            alertContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    <i class="fas ${icon}"></i>
                    ${message}
                </div>
            `;
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function showLoadingOverlay(show) {
            const overlay = document.getElementById('loading-overlay');
            overlay.style.display = show ? 'flex' : 'none';
        }
    </script>
</body>
</html>