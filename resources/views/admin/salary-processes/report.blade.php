<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Folha de Pagamento - {{ $salaryProcess->title }}</title>
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
        }
        
        .payroll-container {
            width: 100%;
            background: white;
        }
        
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
        }
        
        .company-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .company-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo-img {
            width: 45px;
            height: 45px;
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            object-fit: cover;
        }
        
        .company-info {
            text-align: right;
        }
        
        .company-info h1 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .company-info p {
            font-size: 9px;
            opacity: 0.9;
            margin: 1px 0;
            line-height: 1.2;
        }
        
        .payroll-title {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .payroll-badge {
            text-align: center;
        }
        
        .payroll-badge h2 {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 3px;
            color: #2c3e50;
        }
        
        .payroll-period {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
        }
        
        .content {
            padding: 15px;
        }
        
        .payroll-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            padding: 10px 12px;
            background-color: #f8f9fa;
            border-radius: 3px;
            border-left: 2px solid #6c757d;
        }
        
        .detail-group {
            margin-bottom: 6px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 1px;
            display: block;
        }
        
        .detail-value {
            font-size: 10px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            color: white;
            font-weight: 600;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        
        .status-pending { background-color: #6c757d; }
        .status-processed { background-color: #6c757d; }
        .status-approved { background-color: #6c757d; }
        .status-paid { background-color: #6c757d; }
        
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
            color: #495057;
            font-weight: 600;
            padding: 4px 8px;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .summary-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #f8f9fa;
            font-size: 8px;
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
        
        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 6px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .technicians-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #ecf0f1;
        }
        
        .technicians-table th {
            background-color: #f8f9fa;
            color: #495057;
            padding: 4px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        
        .technicians-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #ecf0f1;
            vertical-align: top;
            font-size: 8px;
        }
        
        .technicians-table tfoot th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 700;
            font-size: 8px;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .text-success { color: #27ae60; }
        .text-warning { color: #f39c12; }
        .text-danger { color: #e74c3c; }
        
        .total-section {
            background-color: white;
            padding: 12px;
            border-radius: 3px;
            margin-top: 15px;
            border: 1px solid #ecf0f1;
            border-left: 2px solid #495057;
        }
        
        .total-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .total-table td {
            padding: 5px 0;
            border-bottom: 1px solid #ecf0f1;
            font-size: 9px;
        }
        
        .total-table .total-label {
            font-weight: 600;
            color: #495057;
        }
        
        .total-table .final-total {
            background-color: #495057;
            color: white;
            font-weight: 700;
            font-size: 10px;
            padding: 6px;
            border-radius: 2px;
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

        .department-section {
            margin-bottom: 25px;
        }

        .department-header {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 0;
        }

        .department-summary {
            background-color: #ecf0f1;
            padding: 8px 15px;
            font-size: 11px;
            color: #7f8c8d;
            border-bottom: 1px solid #bdc3c7;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .payroll-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="payroll-container">
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
        
        <div class="payroll-title">
            <div class="payroll-badge">
                <h2>Folha de Pagamento</h2>
                <div class="payroll-period">
                    @php
                        $months = [
                            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                        ];
                    @endphp
                    {{ $months[$salaryProcess->month] }} {{ $salaryProcess->year }}
                </div>
            </div>
        </div>
        
        <div class="content">
            <!-- Detalhes do Processo -->
            <div class="payroll-details">
                <div class="details-left">
                    <div class="detail-group">
                        <span class="detail-label">Título do Processo</span>
                        <span class="detail-value">{{ $salaryProcess->title }}</span>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Status</span>
                        <div class="detail-value">
                            @php
                                $statusClasses = [
                                    'pending' => 'status-pending',
                                    'processed' => 'status-processed',
                                    'approved' => 'status-approved',
                                    'paid' => 'status-paid'
                                ];
                                
                                $statusTexts = [
                                    'pending' => 'Pendente',
                                    'processed' => 'Processado',
                                    'approved' => 'Aprovado',
                                    'paid' => 'Pago'
                                ];
                            @endphp
                            <span class="status-badge {{ $statusClasses[$salaryProcess->status] ?? 'status-pending' }}">
                                {{ $statusTexts[$salaryProcess->status] ?? 'Desconhecido' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <span class="detail-label">Processado por</span>
                        <span class="detail-value">{{ $salaryProcess->processedByUser->name ?? 'Sistema' }}</span>
                    </div>
                </div>
                
                <div class="details-right">
                    <div class="detail-group">
                        <span class="detail-label">Data de Processamento</span>
                        <span class="detail-value">{{ $salaryProcess->processed_at ? $salaryProcess->processed_at->format('d/m/Y H:i') : '-' }}</span>
                    </div>
                    
                    @if($salaryProcess->approved_at)
                    <div class="detail-group">
                        <span class="detail-label">Data de Aprovação</span>
                        <span class="detail-value">{{ $salaryProcess->approved_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    
                    @if($salaryProcess->approvedByUser)
                    <div class="detail-group">
                        <span class="detail-label">Aprovado por</span>
                        <span class="detail-value">{{ $salaryProcess->approvedByUser->name }}</span>
                    </div>
                    @endif
                    
                    @if($salaryProcess->description)
                    <div class="detail-group">
                        <span class="detail-label">Descrição</span>
                        <span class="detail-value">{{ $salaryProcess->description }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Resumo Geral -->
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
                            <td class="label">Total de Técnicos</td>
                            <td class="text-center">{{ $salaryProcess->total_technicians }}</td>
                            <td class="value">-</td>
                        </tr>
                        <tr>
                            <td class="label">Salários Base</td>
                            <td class="text-center">{{ $salaryProcess->total_technicians }} salários</td>
                            <td class="value">{{ number_format($salaryProcess->items->sum('base_salary'), 2, ',', '.') }} MZN</td>
                        </tr>
                        <tr>
                            <td class="label">Horas Extras</td>
                            <td class="text-center">{{ number_format($salaryProcess->items->sum('overtime_hours'), 1, ',', '.') }}h</td>
                            <td class="value">{{ number_format($salaryProcess->items->sum('overtime_amount'), 2, ',', '.') }} MZN</td>
                        </tr>
                        <tr>
                            <td class="label">Bônus</td>
                            <td class="text-center">{{ $salaryProcess->items->where('bonus', '>', 0)->count() }} técnicos</td>
                            <td class="value">{{ number_format($salaryProcess->items->sum('bonus'), 2, ',', '.') }} MZN</td>
                        </tr>
                        <tr>
                            <td class="label">Descontos</td>
                            <td class="text-center">{{ $salaryProcess->items->where('deductions', '>', 0)->count() }} técnicos</td>
                            <td class="value text-danger">-{{ number_format($salaryProcess->items->sum('deductions'), 2, ',', '.') }} MZN</td>
                        </tr>
                        <tr style="background-color: #27ae60; color: white; font-weight: bold;">
                            <td class="label">Total Líquido</td>
                            <td class="text-center">{{ $salaryProcess->total_technicians }} técnicos</td>
                            <td class="value">{{ number_format($salaryProcess->total_amount, 2, ',', '.') }} MZN</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Detalhamento por Departamento e Técnico -->
            @php
                $itemsByDepartment = $salaryProcess->items->groupBy(function($item) {
                    return $item->technician->department->name ?? 'Sem Departamento';
                });
            @endphp
            
            @foreach($itemsByDepartment as $departmentName => $departmentItems)
            <div class="department-section">
                <div class="department-header">
                    {{ $departmentName }}
                </div>
                <div class="department-summary">
                    {{ $departmentItems->count() }} técnico{{ $departmentItems->count() != 1 ? 's' : '' }} - 
                    Total: {{ number_format($departmentItems->sum('net_salary'), 2, ',', '.') }} MZN
                </div>
                
                <table class="technicians-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 20%;">Técnico</th>
                            <th style="width: 12%;">Salário Base</th>
                            <th style="width: 8%;">H. Extras</th>
                            <th style="width: 10%;">Vlr H. Extras</th>
                            <th style="width: 10%;">Bônus</th>
                            <th style="width: 10%;">Descontos</th>
                            <th style="width: 12%;">Líquido</th>
                            <th style="width: 13%;">Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentItems as $index => $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $item->technician->name }}</strong><br>
                                <small style="color: #7f8c8d;">{{ $item->technician->code ?? 'N/A' }}</small>
                            </td>
                            <td class="text-right">{{ number_format($item->base_salary, 2, ',', '.') }}</td>
                            <td class="text-center">{{ $item->overtime_hours }}h</td>
                            <td class="text-right">{{ number_format($item->overtime_amount, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($item->bonus, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($item->deductions, 2, ',', '.') }}</td>
                            <td class="text-right font-bold text-success">{{ number_format($item->net_salary, 2, ',', '.') }}</td>
                            <td style="font-size: 9px;">{{ $item->observations ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">SUBTOTAL {{ strtoupper($departmentName) }}</th>
                            <th class="text-right">{{ number_format($departmentItems->sum('base_salary'), 2, ',', '.') }}</th>
                            <th class="text-center">{{ number_format($departmentItems->sum('overtime_hours'), 1, ',', '.') }}h</th>
                            <th class="text-right">{{ number_format($departmentItems->sum('overtime_amount'), 2, ',', '.') }}</th>
                            <th class="text-right">{{ number_format($departmentItems->sum('bonus'), 2, ',', '.') }}</th>
                            <th class="text-right">{{ number_format($departmentItems->sum('deductions'), 2, ',', '.') }}</th>
                            <th class="text-right">{{ number_format($departmentItems->sum('net_salary'), 2, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endforeach
            
            <!-- Total Geral -->
            <div class="total-section">
                <h3 style="margin-bottom: 15px; color: #2c3e50;">Resumo Final</h3>
                <table class="total-table">
                    <tr>
                        <td class="total-label">Total de Técnicos:</td>
                        <td class="text-right font-bold">{{ $salaryProcess->total_technicians }}</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total Bruto (Base + Extras + Bônus):</td>
                        <td class="text-right font-bold">{{ number_format($salaryProcess->items->sum('base_salary') + $salaryProcess->items->sum('overtime_amount') + $salaryProcess->items->sum('bonus'), 2, ',', '.') }} MZN</td>
                    </tr>
                    <tr>
                        <td class="total-label">Total de Descontos:</td>
                        <td class="text-right text-danger font-bold">-{{ number_format($salaryProcess->items->sum('deductions'), 2, ',', '.') }} MZN</td>
                    </tr>
                    <tr class="final-total">
                        <td class="total-label">TOTAL LÍQUIDO A PAGAR:</td>
                        <td class="text-right">{{ number_format($salaryProcess->total_amount, 2, ',', '.') }} MZN</td>
                    </tr>
                </table>
            </div>
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