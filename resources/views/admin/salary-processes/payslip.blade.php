<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Pagamento - {{ $item->technician->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #2c3e50;
            background-color: white;
            padding: 0;
            margin: 0;
        }
        
        .payslip-container {
            width: 100%;
            background: white;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background-color: #f8f9fa;
            color: #495057;
            padding: 10px;
        }
        
        .company-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .company-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logo-img {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            object-fit: cover;
        }
        
        .company-info {
            text-align: right;
        }
        
        .company-info h1 {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .company-info p {
            font-size: 8px;
            opacity: 0.9;
            margin: 1px 0;
            line-height: 1.2;
        }
        
        .payslip-title {
            background-color: #ecf0f1;
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        
        .payslip-title h2 {
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 3px;
            color: #2c3e50;
        }
        
        .payslip-period {
            font-size: 9px;
            font-weight: 700;
            color: #34495e;
        }
        
        .content {
            padding: 12px;
        }
        
        .employee-section {
            background-color: #f8f9fa;
            padding: 8px 10px;
            border-radius: 3px;
            border-left: 2px solid #495057;
            margin-bottom: 10px;
        }
        
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .info-group {
            margin-bottom: 4px;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            margin-bottom: 1px;
            display: block;
        }
        
        .info-value {
            font-size: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .earnings-section, .deductions-section {
            margin-bottom: 10px;
        }
        
        .section-header {
            background-color: #495057;
            color: white;
            padding: 4px 8px;
            font-weight: 600;
            font-size: 8px;
            margin-bottom: 0;
            border-radius: 2px 2px 0 0;
        }
        
        .deductions-section .section-header {
            background-color: #495057;
        }
        
        .section-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border: 1px solid #ecf0f1;
            border-top: none;
            border-radius: 0 0 2px 2px;
        }
        
        .section-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 8px;
        }
        
        .section-table .item-label {
            font-weight: 500;
            color: #2c3e50;
        }
        
        .section-table .item-value {
            font-weight: 600;
            color: #2c3e50;
            text-align: right;
        }
        
        .section-table .subtotal {
            background-color: #f8f9fa;
            font-weight: 700;
            border-top: 2px solid #ecf0f1;
        }
        
        .net-salary-section {
            background-color: #495057;
            color: white;
            padding: 8px 10px;
            border-radius: 3px;
            text-align: center;
            margin: 10px 0;
        }
        
        .net-salary-label {
            font-size: 8px;
            font-weight: 500;
            margin-bottom: 2px;
            opacity: 0.9;
        }
        
        .net-salary-value {
            font-size: 12px;
            font-weight: 700;
        }
        
        .absence-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 8px;
            margin-bottom: 10px;
        }
        
        .absence-header {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .absence-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }
        
        .absence-table th {
            background-color: #f8f9fa;
            padding: 3px 4px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
        }
        
        .absence-table td {
            padding: 3px 4px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .observations-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 8px;
            margin-bottom: 10px;
        }
        
        .observations-header {
            font-weight: 600;
            color: #495057;
            margin-bottom: 4px;
        }
        
        .observations-text {
            color: #495057;
            font-size: 7px;
            line-height: 1.3;
        }
        
        .footer {
            background-color: #f8f9fa;
            color: #495057;
            padding: 8px 10px;
            text-align: center;
            margin-top: 15px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 7px;
        }
        
        .footer-left p {
            margin: 0;
            opacity: 0.8;
        }
        
        .generated-at {
            opacity: 0.7;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .text-success { color: #27ae60; }
        .text-warning { color: #f39c12; }
        .text-danger { color: #e74c3c; }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .payslip-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="payslip-container">
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
        
        <div class="payslip-title">
            <h2>Comprovante de Pagamento</h2>
            <div class="payslip-period">
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
        
        <div class="content">
            <!-- Informações do Funcionário -->
            <div class="employee-section">
                <div class="employee-info">
                    <div>
                        <div class="info-group">
                            <span class="info-label">Nome Completo</span>
                            <span class="info-value">{{ $item->technician->name }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Código do Funcionário</span>
                            <span class="info-value">{{ $item->technician->code ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Departamento</span>
                            <span class="info-value">{{ $item->technician->department->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <div class="info-group">
                            <span class="info-label">Área</span>
                            <span class="info-value">{{ $item->technician->area->name ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Data de Processamento</span>
                            <span class="info-value">{{ $salaryProcess->processed_at ? $salaryProcess->processed_at->format('d/m/Y') : 'N/A' }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                @if($salaryProcess->status === 'paid')
                                    ✅ Pago
                                @elseif($salaryProcess->status === 'approved')
                                    ✅ Aprovado
                                @elseif($salaryProcess->status === 'processed')
                                    ⏳ Processado
                                @else
                                    ⏳ Pendente
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Seção de Ganhos -->
            <div class="earnings-section">
                <div class="section-header">💰 GANHOS</div>
                <table class="section-table">
                    <tr>
                        <td class="item-label">Salário Base</td>
                        <td class="item-value">{{ number_format($item->base_salary, 2, ',', '.') }} MZN</td>
                    </tr>
                    @if($item->overtime_hours > 0)
                    <tr>
                        <td class="item-label">Horas Extras ({{ $item->overtime_hours }}h)</td>
                        <td class="item-value">{{ number_format($item->overtime_amount, 2, ',', '.') }} MZN</td>
                    </tr>
                    @endif
                    @if($item->bonus > 0)
                    <tr>
                        <td class="item-label">Bônus</td>
                        <td class="item-value">{{ number_format($item->bonus, 2, ',', '.') }} MZN</td>
                    </tr>
                    @endif
                    <tr class="subtotal">
                        <td class="item-label">TOTAL DE GANHOS</td>
                        <td class="item-value">{{ number_format($item->base_salary + $item->overtime_amount + $item->bonus, 2, ',', '.') }} MZN</td>
                    </tr>
                </table>
            </div>
            
            <!-- Seção de Descontos -->
            @if($item->deductions > 0)
            <div class="deductions-section">
                <div class="section-header">📉 DESCONTOS</div>
                <table class="section-table">
                    @php
                        // Calcular deduções por faltas
                        $absences = \App\Models\TechnicianAbsence::approved()
                                    ->forTechnician($item->technician_id)
                                    ->forMonth($salaryProcess->month, $salaryProcess->year)
                                    ->get();
                        
                        $hourlyRate = $item->base_salary / 160; // 160h = 8h/dia × 20 dias úteis
                        $absenceDeductions = $absences->sum('hours_lost') * $hourlyRate;
                        $otherDeductions = $item->deductions - $absenceDeductions;
                    @endphp
                    
                    @if($absenceDeductions > 0)
                    <tr>
                        <td class="item-label">Descontos por Faltas ({{ $absences->sum('hours_lost') }}h)</td>
                        <td class="item-value">{{ number_format($absenceDeductions, 2, ',', '.') }} MZN</td>
                    </tr>
                    @endif
                    
                    @if($otherDeductions > 0)
                    <tr>
                        <td class="item-label">Outros Descontos</td>
                        <td class="item-value">{{ number_format($otherDeductions, 2, ',', '.') }} MZN</td>
                    </tr>
                    @endif
                    
                    <tr class="subtotal">
                        <td class="item-label">TOTAL DE DESCONTOS</td>
                        <td class="item-value">{{ number_format($item->deductions, 2, ',', '.') }} MZN</td>
                    </tr>
                </table>
            </div>
            
            <!-- Detalhes das Faltas (se houver) -->
            @if($absences->count() > 0)
            <div class="absence-section">
                <div class="absence-header">
                    ⚠️ Detalhes das Faltas
                </div>
                <table class="absence-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Horas</th>
                            <th>Motivo</th>
                            <th>Desconto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absences as $absence)
                        <tr>
                            <td>{{ $absence->date->format('d/m/Y') }}</td>
                            <td>
                                @if($absence->type === 'absence')
                                    Falta
                                @elseif($absence->type === 'late_arrival')
                                    Atraso
                                @else
                                    Saída Antecipada
                                @endif
                            </td>
                            <td>{{ $absence->hours_lost }}h</td>
                            <td style="max-width: 150px; word-wrap: break-word;">{{ $absence->reason ?? '-' }}</td>
                            <td>{{ number_format($absence->hours_lost * $hourlyRate, 2, ',', '.') }} MZN</td>
                        </tr>
                        @endforeach
                        <tr style="background-color: #fff8dc; font-weight: bold;">
                            <td colspan="4">TOTAL</td>
                            <td>{{ number_format($absenceDeductions, 2, ',', '.') }} MZN</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            @endif
            
            <!-- Salário Líquido -->
            <div class="net-salary-section">
                <div class="net-salary-label">SALÁRIO LÍQUIDO</div>
                <div class="net-salary-value">{{ number_format($item->net_salary, 2, ',', '.') }} MZN</div>
            </div>
            
            <!-- Observações -->
            @if($item->observations)
            <div class="observations-section">
                <div class="observations-header">📝 Observações</div>
                <div class="observations-text">{{ $item->observations }}</div>
            </div>
            @endif
            
            <!-- Cálculo Detalhado -->
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 6px; border: 1px solid #e9ecef;">
                <div style="font-weight: 600; margin-bottom: 10px; color: #495057;">📊 Detalhamento do Cálculo</div>
                <div style="font-size: 11px; color: #6c757d; line-height: 1.6;">
                    <div>• <strong>Taxa Horária:</strong> {{ number_format($hourlyRate ?? 0, 2, ',', '.') }} MZN/hora (Salário Base ÷ 160h)</div>
                    @if($item->overtime_hours > 0)
                    <div>• <strong>Taxa Hora Extra:</strong> {{ number_format($hourlyRate * 1.5, 2, ',', '.') }} MZN/hora (1.5x taxa normal)</div>
                    @endif
                    @if($absences->count() > 0)
                    <div>• <strong>Horas de Faltas:</strong> {{ $absences->sum('hours_lost') }}h × {{ number_format($hourlyRate, 2, ',', '.') }} MZN = {{ number_format($absenceDeductions, 2, ',', '.') }} MZN</div>
                    @endif
                    <div>• <strong>Fórmula:</strong> (Salário Base + Horas Extras + Bônus) - Descontos = Salário Líquido</div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <p>MDO Areiabranca - Sistema de Gestão</p>
                    <p>Este documento é confidencial e destinado exclusivamente ao funcionário</p>
                </div>
                <div class="footer-right">
                    <div class="generated-at">
                        Gerado em: {{ now()->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>