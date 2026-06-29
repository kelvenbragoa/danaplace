<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura de Taxas - {{ $invoice->invoice_number }} - {{ $destination->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #2c3e50;
            background-color: white;
            padding: 0;
        }
        
        .invoice-container {
            width: 100%;
            background: white;
        }
        
        .header {
            background-color: #34495e;
            color: white;
            padding: 20px;
        }
        
        .company-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .company-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-img {
            width: 70px;
            height: 70px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            object-fit: cover;
        }
        
        .company-info {
            text-align: right;
        }
        
        .company-info h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .company-info p {
            font-size: 13px;
            opacity: 0.9;
            margin: 3px 0;
            line-height: 1.4;
        }
        
        .invoice-title {
            background-color: #ecf0f1;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .invoice-badge {
            text-align: center;
        }
        
        .invoice-badge h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .invoice-number {
            font-size: 20px;
            font-weight: 700;
            color: #34495e;
        }
        
        .content {
            padding: 30px;
        }
        
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }
        
        .detail-group {
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #7f8c8d;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            display: block;
        }
        
        .detail-value {
            font-size: 13px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-draft { background-color: #95a5a6; }
        .status-issued { background-color: #3498db; }
        .status-paid { background-color: #27ae60; }
        .status-partially-paid { background-color: #f39c12; }
        .status-overdue { background-color: #e74c3c; }
        .status-cancelled { background-color: #34495e; }
        
        .summary-section {
            margin-bottom: 20px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border: 1px solid #ecf0f1;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .summary-table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            padding: 8px 12px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .summary-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f8f9fa;
            font-size: 13px;
        }
        
        .summary-table .label {
            font-weight: 500;
            color: #7f8c8d;
        }
        
        .summary-table .value {
            font-weight: 700;
            color: #2c3e50;
            text-align: right;
        }
        
        .summary-table .value.total { color: #3498db; }
        .summary-table .value.paid { color: #27ae60; }
        .summary-table .value.pending { color: #f39c12; }
        .summary-table .value.progress { color: #9b59b6; }
        
        .progress-mini {
            display: inline-block;
            width: 60px;
            height: 8px;
            background-color: #ecf0f1;
            border-radius: 4px;
            margin-left: 10px;
            position: relative;
            vertical-align: middle;
        }
        
        .progress-mini .progress-fill {
            height: 100%;
            background-color: #27ae60;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 6px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background-color: #34495e;
            color: white;
            padding: 8px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .items-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
            font-size: 12px;
        }
        
        .equipment-group {
            background-color: #ebf3fd;
            font-weight: 600;
            color: #2980b9;
        }
        
        .equipment-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .fee-name {
            font-size: 11px;
            color: #7f8c8d;
            font-style: italic;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-success {
            color: #27ae60;
        }
        
        .text-warning {
            color: #f39c12;
        }
        
        .text-danger {
            color: #e74c3c;
        }
        
        .font-bold {
            font-weight: 700;
        }
        
        .client-info {
            background-color: #f1f8ff;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
        }
        
        .client-info h3 {
            color: #2c3e50;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .client-info p {
            margin: 2px 0;
            font-size: 13px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #7f8c8d;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-header">
                <div class="company-logo">
                    <img src="{{ public_path('companylogo.png') }}" alt="Logo Dana Place" class="logo-img">
                </div>
                
                <div class="company-info">
                    <h1>Dana Place</h1>
                    <p>NUIT: 8300202</p>
                    <p>Maputo - Ponta de Ouro</p>
                    <p>Tel: 840127200</p>
                </div>
            </div>
        </div>
        
        <div class="invoice-title">
            <div class="invoice-badge">
                <h2>Fatura de Taxas</h2>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            </div>
        </div>
        
        <div class="content">
            <!-- Informações do Cliente -->
            <div class="client-info">
                <h3>Cliente: {{ $destination->name }}</h3>
                <p><strong>Contacto:</strong> {{ $destination->phone ?? 'Não informado' }}</p>
                <p><strong>Email:</strong> {{ $destination->email ?? 'Não informado' }}</p>
                <p><strong>Endereço:</strong> {{ $destination->address ?? 'Não informado' }}</p>
            </div>

            <!-- Detalhes da Fatura -->
            <div class="invoice-details">
                <div class="details-left">
                    <div class="detail-group">
                        <span class="detail-label">Período de Referência</span>
                        <span class="detail-value">{{ $invoice->period_description }}</span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Status da Fatura</span>
                        <div class="detail-value">
                            @php
                                $statusClasses = [
                                    'draft' => 'status-draft',
                                    'issued' => 'status-issued',
                                    'paid' => 'status-paid',
                                    'partially_paid' => 'status-partially-paid',
                                    'overdue' => 'status-overdue',
                                    'cancelled' => 'status-cancelled'
                                ];
                                
                                $statusTexts = [
                                    'draft' => 'Rascunho',
                                    'issued' => 'Emitida',
                                    'paid' => 'Paga',
                                    'partially_paid' => 'Parcialmente Paga',
                                    'overdue' => 'Vencida',
                                    'cancelled' => 'Cancelada'
                                ];
                            @endphp
                            <span class="status-badge {{ $statusClasses[$invoice->status] ?? 'status-draft' }}">
                                {{ $statusTexts[$invoice->status] ?? 'Desconhecido' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Criado por</span>
                        <span class="detail-value">{{ $invoice->creator->name ?? 'Sistema' }}</span>
                    </div>
                </div>
                
                <div class="details-right">
                    <div class="detail-group">
                        <span class="detail-label">Data de Emissão</span>
                        <span class="detail-value">{{ $invoice->issue_date->format('d/m/Y') }}</span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Data de Vencimento</span>
                        <span class="detail-value {{ $invoice->is_overdue ? 'text-danger font-bold' : '' }}">
                            {{ $invoice->due_date->format('d/m/Y') }}
                            @if($invoice->is_overdue)
                                <br><small style="color: #e74c3c; font-weight: bold;">(VENCIDA)</small>
                            @endif
                        </span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Data de Criação</span>
                        <span class="detail-value">{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Resumo Financeiro -->
            <div class="summary-section">
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Quantidade</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="label">Valor Total</td>
                            <td>{{ $invoice->items->count() }} itens</td>
                            <td class="value total">{{ number_format($invoice->total_amount, 2, ',', '.') }} MZN</td>
                        </tr>
                        <tr>
                            <td class="label">Valor Pago</td>
                            <td>{{ $invoice->items->where('is_paid', true)->count() }} pagos</td>
                            <td class="value paid">{{ number_format($invoice->paid_amount, 2, ',', '.') }} MZN</td>
                        </tr>
                        <tr>
                            <td class="label">Valor Pendente</td>
                            <td>{{ $invoice->items->where('is_paid', false)->count() }} pendentes</td>
                            <td class="value pending">{{ number_format($invoice->remaining_amount, 2, ',', '.') }} MZN</td>
                        </tr>
                        <tr>
                            <td class="label">Progresso</td>
                            <td>
                                {{ $invoice->total_amount > 0 ? round(($invoice->paid_amount / $invoice->total_amount) * 100) : 0 }}%
                                <div class="progress-mini">
                                    <div class="progress-fill" style="width: {{ $invoice->total_amount > 0 ? ($invoice->paid_amount / $invoice->total_amount) * 100 : 0 }}%"></div>
                                </div>
                            </td>
                            <td class="value progress">{{ $invoice->total_amount > 0 ? round(($invoice->paid_amount / $invoice->total_amount) * 100) : 0 }}% Completo</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Detalhamento dos Itens -->
            <h2 class="section-title">Detalhamento dos Itens</h2>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Equipamento / Taxa</th>
                        <th style="width: 20%;">Status</th>
                        <th style="width: 15%;" class="text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoice->items as $item)
                        <tr>
                            <td>
                                <div class="equipment-name">{{ $item->equipment->name ?? 'Equipamento não especificado' }}</div>
                                <div class="fee-name">{{ $item->fee->name ?? 'Taxa não especificada' }}</div>
                            </td>
                            <td>
                                @if($item->is_paid)
                                    <span class="status-badge status-paid">Pago</span>
                                @else
                                    <span class="status-badge status-issued">Pendente</span>
                                @endif
                            </td>
                            <td class="text-right font-bold">{{ number_format($item->amount, 2, ',', '.') }} MZN</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Nenhum item encontrado para este destino</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="footer">
                <p>Documento gerado automaticamente em {{ now()->format('d/m/Y H:i') }}</p>
                <p>Esta fatura contém apenas os itens relacionados ao cliente {{ $destination->name }}</p>
            </div>
        </div>
    </div>
</body>
</html>