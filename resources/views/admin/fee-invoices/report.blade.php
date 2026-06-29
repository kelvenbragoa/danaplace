<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura de Taxas - {{ $invoice->invoice_number }}</title>
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
        .status-partially-paid { background-color: #f39c12; color: #fff; }
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
            border-radius: 3px;
            overflow: hidden;
            margin-top: 8px;
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
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #ecf0f1;
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
        
        .equipment-group td {
            padding: 15px;
        }
        
        .fee-item.paid {
            background-color: #d5f4e6;
        }
        
        .fee-item.unpaid {
            background-color: #fef9e7;
        }
        
        .paid-indicator {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .paid-indicator.paid {
            background-color: #27ae60;
            color: white;
        }
        
        .paid-indicator.unpaid {
            background-color: #f39c12;
            color: white;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .text-success { color: #27ae60; }
        .text-warning { color: #f39c12; }
        .text-danger { color: #e74c3c; }
        
        .total-section {
            background-color: white;
            padding: 25px;
            border-radius: 6px;
            margin-top: 30px;
            border: 1px solid #ecf0f1;
            border-left: 4px solid #27ae60;
        }
        
        .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .total-table td {
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
            font-size: 14px;
        }
        
        .total-table .total-label {
            font-weight: 600;
            color: #7f8c8d;
        }
        
        .total-table .final-total {
            background-color: #27ae60;
            color: white;
            font-weight: 700;
            font-size: 16px;
            padding: 12px;
            border-radius: 4px;
        }
        
        .notes-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-top: 30px;
            border-left: 4px solid #3498db;
        }
        
        .notes-section h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px 40px;
            text-align: center;
            margin-top: 40px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-left h4 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .footer-left p {
            font-size: 11px;
            opacity: 0.8;
            margin: 0;
        }
        
        .footer-right {
            text-align: right;
        }
        
        .generated-at {
            font-size: 10px;
            opacity: 0.7;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }
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
                    
                    @if($invoice->approved_at)
                    <div class="detail-group">
                        <span class="detail-label">Data de Aprovação</span>
                        <span class="detail-value">{{ $invoice->approved_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
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
                        <th style="width: 20%;">Cliente</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 15%;" class="text-right">Valor</th>
                        <th style="width: 15%;">Pagamento</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedItems = $invoice->items->groupBy('equipment_id');
                    @endphp
                    
                    @foreach($groupedItems as $equipmentId => $items)
                        @php
                            $equipment = $items->first()->equipment;
                            $equipmentTotal = $items->sum('amount');
                            $paidItems = $items->where('is_paid', true)->count();
                            $totalItems = $items->count();
                        @endphp
                        
                        <!-- Equipment Header -->
                        <tr class="equipment-group">
                            <td colspan="3">
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <div>
                                        <strong>{{ $equipment->name }}</strong>
                                        <br><small>{{ $equipment->type_equipment->name ?? 'Tipo não definido' }}</small>
                                    </div>
                                    <small>{{ $paidItems }}/{{ $totalItems }} pagos</small>
                                </div>
                            </td>
                            <td class="text-right font-bold">{{ number_format($equipmentTotal, 2, ',', '.') }} MZN</td>
                            <td class="text-center">{{ $totalItems }} taxa{{ $totalItems != 1 ? 's' : '' }}</td>
                        </tr>
                        
                        <!-- Fee Items -->
                        @foreach($items as $item)
                        <tr class="fee-item {{ $item->is_paid ? 'paid' : 'unpaid' }}">
                            <td style="padding-left: 25px;">{{ $item->fee->name }}</td>
                            <td>{{ $equipment->destination->name ?? 'N/A' }}</td>
                            <td>
                                <span class="paid-indicator {{ $item->is_paid ? 'paid' : 'unpaid' }}">
                                    {{ $item->is_paid ? 'Pago' : 'Pendente' }}
                                </span>
                            </td>
                            <td class="text-right font-bold">{{ number_format($item->amount, 2, ',', '.') }} MZN</td>
                            <td class="text-center">
                                @if($item->paid_at)
                                    {{ $item->paid_at->format('d/m/Y') }}
                                    @if($item->markedByUser)
                                        <br><small>{{ $item->markedByUser->name }}</small>
                                    @endif
                                @else
                                    <span style="color: #7f8c8d;">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            
            <!-- Total Section -->
            <div class="total-section">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">Resumo Financeiro</h3>
                <table class="total-table">
                    <tr>
                        <td class="total-label">Valor Total da Fatura:</td>
                        <td class="text-right font-bold">{{ number_format($invoice->total_amount, 2, ',', '.') }} MZN</td>
                    </tr>
                    <tr>
                        <td class="total-label">Valor já Pago:</td>
                        <td class="text-right text-success font-bold">{{ number_format($invoice->paid_amount, 2, ',', '.') }} MZN</td>
                    </tr>
                    <tr class="final-total">
                        <td class="total-label">Valor Pendente:</td>
                        <td class="text-right">{{ number_format($invoice->remaining_amount, 2, ',', '.') }} MZN</td>
                    </tr>
                </table>
            </div>
            
            @if($invoice->notes)
            <div class="notes-section">
                <h4>Observações:</h4>
                <p>{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>
        
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <h4>MDO Areiabranca</h4>
                    <p>Sistema de Gestão de Ativos e Manutenção</p>
                    <p>Mozambique</p>
                </div>
                <div class="footer-right">
                    <div class="generated-at">
                        Relatório gerado em: {{ now()->format('d/m/Y H:i:s') }}
                    </div>
                    <div style="margin-top: 5px; font-size: 10px; opacity: 0.7;">
                        Página 1 de 1
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>