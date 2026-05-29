<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guia de Entrada - {{ $entryGuide->guide_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #2c3e50;
            background-color: white;
            padding: 0;
            margin: 0;
        }
        
        .guide-container {
            width: 100%;
            background: white;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background-color: #f8f9fa;
            color: #495057;
            padding: 15px;
            border-bottom: 2px solid #495057;
        }
        
        .company-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .company-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            object-fit: cover;
        }
        
        .company-info h1 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .company-info p {
            font-size: 9px;
            margin: 1px 0;
            line-height: 1.2;
        }
        
        .guide-title {
            background-color: #495057;
            color: white;
            padding: 12px;
            text-align: center;
            margin: 0;
        }
        
        .guide-title h2 {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .guide-number {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .content {
            padding: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-section {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border-left: 3px solid #495057;
        }
        
        .section-title {
            font-weight: 600;
            color: #495057;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 3px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 8px;
            text-transform: uppercase;
            width: 80px;
            flex-shrink: 0;
        }
        
        .info-value {
            font-size: 9px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .qr-section {
            text-align: center;
            background-color: white;
            border: 2px dashed #495057;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
        }
        
        .qr-code {
            margin: 10px auto;
        }
        
        .qr-code img {
            width: 120px;
            height: 120px;
        }
        
        .qr-instructions {
            font-size: 8px;
            color: #6c757d;
            margin-top: 8px;
            line-height: 1.4;
        }
        
        .validity-section {
            background-color: #fff3cd;
            border: 1px solid #ffecb5;
            border-radius: 5px;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
        }
        
        .validity-title {
            font-weight: 600;
            color: #856404;
            font-size: 9px;
            margin-bottom: 5px;
        }
        
        .validity-dates {
            display: flex;
            justify-content: space-around;
            margin-top: 8px;
        }
        
        .validity-item {
            text-align: center;
        }
        
        .validity-label {
            font-size: 7px;
            color: #856404;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .validity-value {
            font-size: 9px;
            color: #856404;
            font-weight: 700;
        }
        
        .purpose-section {
            margin: 15px 0;
        }
        
        .purpose-title {
            font-weight: 600;
            color: #495057;
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .purpose-text {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 8px;
            font-size: 9px;
            line-height: 1.4;
            min-height: 40px;
        }
        
        .instructions-section {
            background-color: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 5px;
            padding: 10px;
            margin-top: 15px;
        }
        
        .instructions-title {
            font-weight: 600;
            color: #0c5460;
            font-size: 9px;
            margin-bottom: 5px;
        }
        
        .instructions-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .instructions-list li {
            font-size: 8px;
            color: #0c5460;
            margin-bottom: 3px;
            padding-left: 12px;
            position: relative;
        }
        
        .instructions-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            font-weight: bold;
        }
        
        .footer {
            background-color: #f8f9fa;
            color: #495057;
            padding: 10px;
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 7px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 15px;
            font-size: 7px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-used {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-cancelled {
            background-color: #f5f5f5;
            color: #6c757d;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .guide-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="guide-container">
        <div class="header">
            <div class="company-header">
                <div class="company-logo">
                    <img src="{{ public_path('companylogo.png') }}" alt="Logo {{ $entryGuide->destination->name }}" class="logo-img">
                    <div class="company-info">
                        <h1>{{ $entryGuide->destination->name }}</h1>
                        <p><strong>NUIT:</strong> {{ $entryGuide->destination->company_nuit ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $entryGuide->destination->company_email ?? 'N/A' }}</p>
                        <p><strong>Tel:</strong> {{ $entryGuide->destination->company_mobile ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="status-info">
                    <span class="status-badge status-{{ $entryGuide->status }}">
                        {{ $entryGuide->status_label }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="guide-title">
            <h2>GUIA DE ENTRADA</h2>
            <div class="guide-number">{{ $entryGuide->guide_number }}</div>
        </div>
        
        <div class="content">
            <div class="info-grid">
                <div class="info-section">
                    <div class="section-title">Dados do Visitante</div>
                    <div class="info-row">
                        <span class="info-label">Nome:</span>
                        <span class="info-value">{{ $entryGuide->guest_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Documento:</span>
                        <span class="info-value">{{ $entryGuide->guest_document }}</span>
                    </div>
                    @if($entryGuide->guest_phone)
                    <div class="info-row">
                        <span class="info-label">Telefone:</span>
                        <span class="info-value">{{ $entryGuide->guest_phone }}</span>
                    </div>
                    @endif
                    @if($entryGuide->guest_email)
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $entryGuide->guest_email }}</span>
                    </div>
                    @endif
                </div>
                
                <div class="info-section">
                    <div class="section-title">Dados do Anfitrião</div>
                    <div class="info-row">
                        <span class="info-label">Nome:</span>
                        <span class="info-value">{{ $entryGuide->host_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Unidade:</span>
                        <span class="info-value">{{ $entryGuide->host_unit }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Criado por:</span>
                        <span class="info-value">{{ $entryGuide->creator->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Data:</span>
                        <span class="info-value">{{ $entryGuide->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="validity-section">
                <div class="validity-title">PERÍODO DE VALIDADE</div>
                <div class="validity-dates">
                    <div class="validity-item">
                        <div class="validity-label">Válido de</div>
                        <div class="validity-value">{{ $entryGuide->valid_from->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="validity-item">
                        <div class="validity-label">Válido até</div>
                        <div class="validity-value">{{ $entryGuide->valid_until->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
            
            @if($entryGuide->purpose)
            <div class="purpose-section">
                <div class="purpose-title">Motivo da Visita</div>
                <div class="purpose-text">{{ $entryGuide->purpose }}</div>
            </div>
            @endif
            
            <div class="qr-section">
                <h3 style="font-size: 10px; color: #495057; margin-bottom: 5px;">Código de Verificação</h3>
                <div class="qr-code">
                    @if($entryGuide->qr_code_path && Storage::exists($entryGuide->qr_code_path))
                        <img src="data:image/png;base64,{{ base64_encode(Storage::get($entryGuide->qr_code_path)) }}" alt="QR Code">
                    @else
                        <div style="width: 120px; height: 120px; background: #f8f9fa; border: 2px dashed #dee2e6; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                            <span style="color: #6c757d; font-size: 8px;">QR Code indisponível</span>
                        </div>
                    @endif
                </div>
                <div class="qr-instructions">
                    Apresente este código QR na portaria para liberação de acesso.
                </div>
            </div>
            
            <div class="instructions-section">
                <div class="instructions-title">Instruções Importantes</div>
                <ul class="instructions-list">
                    <li>Esta guia é pessoal e intransferível</li>
                    <li>Apresente documento de identificação junto com esta guia</li>
                    <li>Respeite o horário de validade indicado</li>
                    <li>É obrigatório registrar a entrada e saída na portaria</li>
                    <li>Em caso de dúvidas, contacte o anfitrião responsável</li>
                    <li>Guias expiradas ou canceladas não serão aceitas</li>
                </ul>
            </div>
            
            @if($entryGuide->observations)
            <div class="purpose-section">
                <div class="purpose-title">Observações</div>
                <div class="purpose-text">{{ $entryGuide->observations }}</div>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>Guia gerada automaticamente em {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>{{ $entryGuide->destination->name }} - Sistema de Controle de Acesso</p>
        </div>
    </div>
</body>
</html>