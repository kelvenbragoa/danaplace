<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura por Cliente - {{ $destinationName }}</title>
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
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-info .invoice-title {
            font-size: 18px;
            font-weight: bold;
            color: #ecf0f1;
            margin-bottom: 8px;
        }
        
        .invoice-info .invoice-number {
            font-size: 20px;
            font-weight: bold;
            color: #f39c12;
            margin-bottom: 5px;
        }
        
        .invoice-info .invoice-date {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.8);
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
            font-weight: bold;
            color: #e74c3c;
        }
        
        .destination-highlight {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }
        
        .content {
            padding: 20px;
        }
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }
        
        .details-section {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #34495e;
        }
        
        .details-section h3 {
            color: #34495e;
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .details-section .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .details-section .detail-label {
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .details-section .detail-value {
            color: #34495e;
            font-weight: bold;
        }
        
        .items-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #34495e;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #34495e;
        }
        
        .equipment-group {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .equipment-header {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
            padding: 12px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .equipment-name {
            font-size: 14px;
            font-weight: bold;
        }
        
        .equipment-meta {
            font-size: 10px;
            opacity: 0.8;
            margin-top: 2px;
        }
        
        .equipment-total {
            font-size: 14px;
            font-weight: bold;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table th {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 10px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            border-bottom: 2px solid #bdc3c7;
        }
        
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 10px;
            vertical-align: top;
        }
        
        .items-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .amount {
            font-weight: bold;
            color: #27ae60;
        }
        
        .fee-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .fee-description {
            font-size: 9px;
            color: #7f8c8d;
            margin-top: 2px;
        }
        
        .summary {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #bdc3c7;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 8px 0;
            font-size: 12px;
            border-bottom: 1px solid #d5d8dc;
        }
        
        .summary-table .summary-label {
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .summary-table .summary-value {
            text-align: right;
            color: #2c3e50;
            font-weight: bold;
        }
        
        .final-total {
            background: #2c3e50;
            color: white;
            font-size: 14px;
            font-weight: bold;
        }
        
        .final-total td {
            padding: 12px 0;
            border: none;
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
        
        .page-break {
            page-break-before: always;
        }
        
        @page {
            margin: 0;
            size: A4 portrait;
        }
        
        .highlight-destination {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-header">
                <div class="company-logo">
                    <img src="{{ public_path('companylogo.png') }}" alt="Logo Areia Branca" class="logo-img">
                </div>
                
                <div class="company-info">
                    <h1>Areia Branca</h1>
                    <p>NUIT: 8300202</p>
                    <p>Maputo - Ponta de Ouro</p>
                    <p>Tel: 840127200</p>
                </div>
            </div>
        </div>
        
        <div class="invoice-title">
            <div class="invoice-badge">
                <h2>Fatura de Taxas por Destino</h2>
                <div class="invoice-number">{{ $destinationInvoice->invoice_number }}</div>
            </div>
        </div>
        
        <!-- Destination Highlight -->
        <div class="destination-highlight">
            📍 FATURA ESPECÍFICA PARA: {{ strtoupper($destinationName) }}
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Invoice Details -->
            <div class="invoice-details">
                <div class="details-section">
                    <h3>Informações da Fatura</h3>
                    <div class="detail-row">
                        <span class="detail-label">Número:</span>
                        <span class="detail-value">{{ $destinationInvoice->invoice_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Data de Emissão:</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($destinationInvoice->issue_date)->format('d/m/Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Vencimento:</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($destinationInvoice->due_date)->format('d/m/Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Período:</span>
                        <span class="detail-value">{{ $destinationInvoice->period_description }}</span>
                    </div>
                </div>
                
                <div class="details-section">
                    <h3>Cliente Específico</h3>
                    <div class="detail-row">
                        <span class="detail-label">Cliente:</span>
                        <span class="detail-value highlight-destination">{{ $destinationName }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total de Itens:</span>
                        <span class="detail-value">{{ $destinationInvoice->items->count() }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Equipamentos:</span>
                        <span class="detail-value">{{ $destinationInvoice->items->pluck('equipment')->unique('id')->count() }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Valor Total:</span>
                        <span class="detail-value amount">{{ number_format($destinationInvoice->total_amount, 2, ',', '.') }} MZN</span>
                    </div>
                </div>
                
                <div class="details-section">
                    <h3>Informações Adicionais</h3>
                    <div class="detail-row">
                        <span class="detail-label">Criado por:</span>
                        <span class="detail-value">{{ $destinationInvoice->creator?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Data de Criação:</span>
                        <span class="detail-value">{{ $destinationInvoice->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            @switch($destinationInvoice->status)
                                @case('draft') Rascunho @break
                                @case('issued') Emitida @break
                                @case('partially_paid') Parcialmente Paga @break
                                @case('paid') Paga @break
                                @case('overdue') Vencida @break
                                @case('cancelled') Cancelada @break
                                @default {{ $destinationInvoice->status }} @break
                            @endswitch
                        </span>
                    </div>
                </div>
            </div>
        
            <!-- Items Section -->
            <div class="items-section">
                <h2 class="section-title">Detalhamento por Equipamento - {{ $destinationName }}</h2>
                
                @php
                    $groupedItems = $destinationInvoice->items->groupBy('equipment.id');
                @endphp
                
                @foreach($groupedItems as $equipmentId => $items)
                    @php
                        $equipment = $items->first()->equipment;
                        $equipmentTotal = $items->sum('amount');
                    @endphp
                    
                    <div class="equipment-group">
                        <div class="equipment-header">
                            <div>
                                <div class="equipment-name">{{ $equipment->name ?? 'Equipamento ' . $equipment->id }}</div>
                                <div class="equipment-meta">
                                    Tipo: {{ $equipment->type_equipment?->name ?? 'N/A' }} | 
                                    Centro de Custo: {{ $equipment->centercost?->name ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="equipment-total">{{ number_format($equipmentTotal, 2, ',', '.') }} MZN</div>
                        </div>
                        
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 50%">Taxa</th>
                                    <th style="width: 15%">Tipo</th>
                                    <th style="width: 20%" class="text-right">Valor</th>
                                    <th style="width: 15%">Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td>
                                        <div class="fee-name">{{ $item->fee?->name ?? 'Taxa ' . $item->fee_id }}</div>
                                        @if($item->fee?->description)
                                            <div class="fee-description">{{ Str::limit($item->fee->description, 80) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $item->fee?->fee_type ?? 'N/A' }}</td>
                                    <td class="text-right amount">{{ number_format($item->amount, 2, ',', '.') }} MZN</td>
                                    <td>
                                        @if($item->notes)
                                            {{ Str::limit($item->notes, 50) }}
                                        @else
                                            <span style="color: #bdc3c7;">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        
            <!-- Summary -->
            <div class="summary">
                <h3 style="color: #34495e; margin-bottom: 15px; text-align: center;">Resumo do Cliente: {{ $destinationName }}</h3>
                <table class="summary-table">
                    <tr class="final-total">
                        <td class="summary-label">VALOR TOTAL PARA {{ strtoupper($destinationName) }}:</td>
                        <td class="summary-value">{{ number_format($destinationInvoice->total_amount, 2, ',', '.') }} MZN</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <h4>Areia Branca</h4>
                    <p>NUIT: 8300202 | Maputo - Ponta de Ouro | Tel: 840127200</p>
                </div>
                <div class="footer-right">
                    <div class="generated-at">
                        Documento gerado em {{ now()->format('d/m/Y H:i:s') }}
                    </div>
                    <div class="generated-at">
                        Fatura por Destino: {{ $destinationName }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>