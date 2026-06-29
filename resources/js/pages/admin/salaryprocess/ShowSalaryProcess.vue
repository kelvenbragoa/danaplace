<template>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                                <h3 class="card-title mb-0">Detalhes do Processo Salarial</h3>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group">
                                    <router-link class="btn btn-secondary" to="/admin/salary-processes">
                                        <vue-feather type="arrow-left" size="16"></vue-feather>
                                        Voltar
                                    </router-link>
                                    <router-link v-if="salaryProcess.status === 'pending' || salaryProcess.status === 'processed'" 
                                        :to="`/admin/salary-processes/${$route.params.id}/edit`" 
                                        class="btn btn-primary">
                                        <vue-feather type="edit-2" size="16"></vue-feather>
                                        Editar
                                    </router-link>
                                    <button v-if="salaryProcess.status === 'processed'" 
                                        @click="approveProcess" 
                                        class="btn btn-success">
                                        <vue-feather type="check" size="16"></vue-feather>
                                        Aprovar
                                    </button>
                                    <button v-if="salaryProcess.status === 'approved'" 
                                        @click="markAsPaid" 
                                        class="btn btn-info">
                                        <vue-feather type="dollar-sign" size="16"></vue-feather>
                                        Marcar como Pago
                                    </button>
                                    <!-- <button @click="generateReport" class="btn btn-outline-primary">
                                        <vue-feather type="download" size="16"></vue-feather>
                                        Baixar PDF
                                    </button> -->
                                    <button @click="printPayroll" class="btn btn-outline-success" :disabled="loadingPrint">
                                        <div v-if="loadingPrint" class="spinner-border spinner-border-sm me-2"></div>
                                        <vue-feather v-else type="printer" size="16"></vue-feather>
                                        {{ loadingPrint ? 'Imprimindo...' : 'Imprimir Folha' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" v-if="salaryProcess.id">
                        <!-- Informações Gerais -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Informações Gerais</h5>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row">
                                            <dt class="col-sm-4">Título:</dt>
                                            <dd class="col-sm-8">{{ salaryProcess.title }}</dd>
                                            
                                            <dt class="col-sm-4">Período:</dt>
                                            <dd class="col-sm-8">{{ formatMonthYear(salaryProcess.month, salaryProcess.year) }}</dd>
                                            
                                            <dt class="col-sm-4">Status:</dt>
                                            <dd class="col-sm-8">
                                                <span v-if="salaryProcess.status === 'pending'" class="badge bg-warning">
                                                    Pendente
                                                </span>
                                                <span v-if="salaryProcess.status === 'processed'" class="badge bg-info">
                                                    Processado
                                                </span>
                                                <span v-if="salaryProcess.status === 'approved'" class="badge bg-success">
                                                    Aprovado
                                                </span>
                                                <span v-if="salaryProcess.status === 'paid'" class="badge bg-primary">
                                                    Pago
                                                </span>
                                            </dd>
                                            
                                            <dt class="col-sm-4">Descrição:</dt>
                                            <dd class="col-sm-8">{{ salaryProcess.description || '-' }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Resumo Financeiro</h5>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row">
                                            <dt class="col-sm-6">Total de Técnicos:</dt>
                                            <dd class="col-sm-6">{{ salaryProcess.total_technicians }}</dd>
                                            
                                            <dt class="col-sm-6">Valor Total:</dt>
                                            <dd class="col-sm-6">
                                                <strong class="text-primary">{{ formatCurrency(salaryProcess.total_amount) }}</strong>
                                            </dd>
                                            
                                            <dt class="col-sm-6">Processado por:</dt>
                                            <dd class="col-sm-6">{{ salaryProcess.processed_by_user?.name || '-' }}</dd>
                                            
                                            <dt class="col-sm-6">Data Processamento:</dt>
                                            <dd class="col-sm-6">{{ formatDateTime(salaryProcess.processed_at) }}</dd>
                                            
                                            <dt class="col-sm-6" v-if="salaryProcess.approved_by_user">Aprovado por:</dt>
                                            <dd class="col-sm-6" v-if="salaryProcess.approved_by_user">{{ salaryProcess.approved_by_user.name }}</dd>
                                            
                                            <dt class="col-sm-6" v-if="salaryProcess.approved_at">Data Aprovação:</dt>
                                            <dd class="col-sm-6" v-if="salaryProcess.approved_at">{{ formatDateTime(salaryProcess.approved_at) }}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detalhes dos Técnicos -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Detalhes por Técnico</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Técnico</th>
                                                <th>Departamento</th>
                                                <th>Área</th>
                                                <th>Salário Base</th>
                                                <th>H. Extras</th>
                                                <th>Valor H. Extras</th>
                                                <th>Bônus</th>
                                                <th>Descontos</th>
                                                <th>Salário Líquido</th>
                                                <th>Observações</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in salaryProcess.items" :key="item.id">
                                                <td>{{ index + 1 }}</td>
                                                <td>
                                                    <strong>{{ item.technician.name }}</strong><br>
                                                    <small class="text-muted">{{ item.technician.code }}</small>
                                                </td>
                                                <td>{{ item.technician.department?.name || '-' }}</td>
                                                <td>{{ item.technician.area?.name || '-' }}</td>
                                                <td>{{ formatCurrency(item.base_salary) }}</td>
                                                <td>{{ item.overtime_hours }}h</td>
                                                <td>{{ formatCurrency(item.overtime_amount) }}</td>
                                                <td>{{ formatCurrency(item.bonus) }}</td>
                                                <td>{{ formatCurrency(item.deductions) }}</td>
                                                <td>
                                                    <strong class="text-success">{{ formatCurrency(item.net_salary) }}</strong>
                                                </td>
                                                <td>{{ item.observations || '-' }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <!-- <button 
                                                            @click="generatePayslip(item.id, item.technician.name)"
                                                            class="btn btn-outline-primary"
                                                            :title="`Baixar PDF de ${item.technician.name}`"
                                                        >
                                                            <vue-feather type="download" size="14"></vue-feather>
                                                        </button> -->
                                                        <button 
                                                            @click="printPayslip(item)"
                                                            class="btn btn-outline-success"
                                                            :title="`Imprimir payslip de ${item.technician.name}`"
                                                            :disabled="loadingPrint"
                                                        >
                                                            <vue-feather type="printer" size="14"></vue-feather>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-dark">
                                            <tr>
                                                <th colspan="4">TOTAL GERAL</th>
                                                <th>{{ formatCurrency(totals.base_salary) }}</th>
                                                <th>{{ totals.overtime_hours }}h</th>
                                                <th>{{ formatCurrency(totals.overtime_amount) }}</th>
                                                <th>{{ formatCurrency(totals.bonus) }}</th>
                                                <th>{{ formatCurrency(totals.deductions) }}</th>
                                                <th>{{ formatCurrency(totals.net_salary) }}</th>
                                                <th></th>
                                                <th>
                                                    <button @click="printPayroll" class="btn btn-outline-success" :disabled="loadingPrint">
                                        <div v-if="loadingPrint" class="spinner-border spinner-border-sm me-2"></div>
                                        <vue-feather v-else type="printer" size="16"></vue-feather>
                                        {{ loadingPrint ? 'Imprimindo...' : 'Imprimir Folha' }}
                                    </button>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="card-body">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Carregando...</span>
                            </div>
                            <p class="mt-2">Carregando dados...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template de Impressão da Folha Salarial (oculto) -->
        <div style="display: none;">
            <div id="print-payroll">
                <!-- Header da Folha Salarial -->
                <div class="row text-center">
                    <div class="col text-center" style="text-align: center">
                        <h2>Dana Place Payroll</h2>
                        <h4>{{ salaryProcess.title }}</h4>
                        <h5>{{ formatMonthYear(salaryProcess.month, salaryProcess.year) }}</h5>
                    </div>
                </div>
                
                <!-- Logo e Informações da Empresa -->
                <div class="row">
                    <div class="col text-left" style="text-align: left">
                        <img
                            src="/files/img/sys/companylogo.png"
                            class="img-fluid"
                            alt="image"
                            width="150px"
                            height="150px"
                            style="text-align: left"
                        />
                    </div>
                    <div class="col">
                        <br />
                    </div>
                    <div class="col text-right" style="text-align: right">
                        <!-- Status da Folha -->
                        <div class="text-center">
                            <span v-if="salaryProcess.status === 'pending'" class="badge bg-warning" style="font-size: 12px;">
                                PENDENTE
                            </span>
                            <span v-else-if="salaryProcess.status === 'processed'" class="badge bg-info" style="font-size: 12px;">
                                PROCESSADO
                            </span>
                            <span v-else-if="salaryProcess.status === 'approved'" class="badge bg-success" style="font-size: 12px;">
                                APROVADO
                            </span>
                            <span v-else-if="salaryProcess.status === 'paid'" class="badge bg-primary" style="font-size: 12px;">
                                PAGO
                            </span>
                            <p style="font-size: 10px; margin-top: 5px;">
                                Status do Processo
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Informações da Empresa -->
                <div class="row">
                    <div class="col">
                        <p style="font-size:10px">
                            Dana Place
                            <br />
                            Cimento a Ponta de Ouro
                            <br />
                            Matutuine, Moçambique
                            <br />
                            Tel: +258 87 914 1774
                            <br />
                            Email: info@ieareiabranca.com
                            <br />
                            www.areiabranca.com
                        </p>
                    </div>
                    <div class="col">
                        <br />
                    </div>
                </div>
                
                <!-- Cabeçalho da Folha -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="font-size: 10px;">
                                PERÍODO:
                                {{ formatMonthYear(salaryProcess.month, salaryProcess.year) }}
                            </th>
                            <th style="font-size: 10px;">
                                PROCESSADO EM:
                                {{ formatDateTime(salaryProcess.processed_at) }}
                            </th>
                            <th style="font-size: 10px;">
                                TOTAL TÉCNICOS:
                                {{ salaryProcess.total_technicians }}
                            </th>
                            <th style="font-size: 10px;">
                                VALOR TOTAL:
                                {{ formatCurrency(salaryProcess.total_amount) }}
                            </th>
                        </tr>
                    </thead>
                </table>

                <!-- Resumo Financeiro -->
                <div class="row">
                    <div class="col-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="2" class="bg-primary text-white" style="font-size: 10px;">
                                        RESUMO FINANCEIRO
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Total Salários Base</td>
                                    <td style="font-size: 10px;">{{ formatCurrency(totals.base_salary) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Total Horas Extras</td>
                                    <td style="font-size: 10px;">{{ totals.overtime_hours }}h</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Valor Horas Extras</td>
                                    <td style="font-size: 10px;">{{ formatCurrency(totals.overtime_amount) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Total Bônus</td>
                                    <td style="font-size: 10px;">{{ formatCurrency(totals.bonus) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Total Descontos</td>
                                    <td style="font-size: 10px;">{{ formatCurrency(totals.deductions) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="2" class="bg-success text-white" style="font-size: 10px;">
                                        INFORMAÇÕES DO PROCESSO
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Processado por</td>
                                    <td style="font-size: 10px;">{{ salaryProcess.processed_by_user?.name || '-' }}</td>
                                </tr>
                                <tr v-if="salaryProcess.approved_by_user">
                                    <td style="font-size: 10px; font-weight: bold;">Aprovado por</td>
                                    <td style="font-size: 10px;">{{ salaryProcess.approved_by_user.name }}</td>
                                </tr>
                                <tr v-if="salaryProcess.approved_at">
                                    <td style="font-size: 10px; font-weight: bold;">Data Aprovação</td>
                                    <td style="font-size: 10px;">{{ formatDateTime(salaryProcess.approved_at) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Status</td>
                                    <td style="font-size: 10px;">
                                        <span v-if="salaryProcess.status === 'pending'">PENDENTE</span>
                                        <span v-else-if="salaryProcess.status === 'processed'">PROCESSADO</span>
                                        <span v-else-if="salaryProcess.status === 'approved'">APROVADO</span>
                                        <span v-else-if="salaryProcess.status === 'paid'">PAGO</span>
                                    </td>
                                </tr>
                                <tr v-if="salaryProcess.description">
                                    <td style="font-size: 10px; font-weight: bold;">Descrição</td>
                                    <td style="font-size: 10px;">{{ salaryProcess.description }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detalhes por Técnico -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="bg-info text-white" style="font-size: 10px;">#</th>
                            <th class="bg-info text-white" style="font-size: 10px;">Técnico</th>
                            <th class="bg-info text-white" style="font-size: 10px;">Departamento</th>
                            <th class="bg-info text-white" style="font-size: 10px;">Área</th>
                            <th class="bg-info text-white" style="font-size: 10px;">Salário Base</th>
                            <th class="bg-info text-white" style="font-size: 10px;">H. Extras</th>
                            <th class="bg-info text-white" style="font-size: 10px;">Valor H. Extras</th>
                            <th class="bg-info text-white" style="font-size: 10px;">Bônus</th>
                            <th class="bg-info text-white" style="font-size: 10px;">Descontos</th>
                            <th class="bg-info text-white" style="font-size: 10px;">Salário Líquido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in salaryProcess.items" :key="item.id">
                            <td style="font-size: 9px;">{{ index + 1 }}</td>
                            <td style="font-size: 9px;">
                                <strong>{{ item.technician.name }}</strong><br>
                                <small>{{ item.technician.code }}</small>
                            </td>
                            <td style="font-size: 9px;">{{ item.technician.department?.name || '-' }}</td>
                            <td style="font-size: 9px;">{{ item.technician.area?.name || '-' }}</td>
                            <td style="font-size: 9px;">{{ formatCurrency(item.base_salary) }}</td>
                            <td style="font-size: 9px;">{{ item.overtime_hours }}h</td>
                            <td style="font-size: 9px;">{{ formatCurrency(item.overtime_amount) }}</td>
                            <td style="font-size: 9px;">{{ formatCurrency(item.bonus) }}</td>
                            <td style="font-size: 9px;">{{ formatCurrency(item.deductions) }}</td>
                            <td style="font-size: 9px; font-weight: bold;">{{ formatCurrency(item.net_salary) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-success text-white">
                            <td colspan="4" style="font-size: 10px; font-weight: bold;">TOTAL GERAL</td>
                            <td style="font-size: 10px; font-weight: bold;">{{ formatCurrency(totals.base_salary) }}</td>
                            <td style="font-size: 10px; font-weight: bold;">{{ totals.overtime_hours }}h</td>
                            <td style="font-size: 10px; font-weight: bold;">{{ formatCurrency(totals.overtime_amount) }}</td>
                            <td style="font-size: 10px; font-weight: bold;">{{ formatCurrency(totals.bonus) }}</td>
                            <td style="font-size: 10px; font-weight: bold;">{{ formatCurrency(totals.deductions) }}</td>
                            <td style="font-size: 12px; font-weight: bold;">{{ formatCurrency(totals.net_salary) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Rodapé -->
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <p style="font-size: 8px; color: #666;">
                            Esta folha salarial foi gerada automaticamente pelo sistema de gestão do Condomínio Dana Place.<br>
                            Documento confidencial - uso exclusivo do departamento de recursos humanos.<br>
                            <strong>Data de geração:</strong> {{ formatDateTime(new Date()) }} | 
                            <strong>Período:</strong> {{ formatMonthYear(salaryProcess.month, salaryProcess.year) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template de Impressão do Payslip Individual (oculto) -->
        <div style="display: none;">
            <div id="print-payslip">
                <!-- Header do Payslip -->
                <div class="row text-center">
                    <div class="col text-center" style="text-align: center">
                        <h2>Dana Place</h2>
                        <h3>Recibo de Salário / Payslip</h3>
                        <h4>{{ formatMonthYear(salaryProcess.month, salaryProcess.year) }}</h4>
                    </div>
                </div>
                
                <!-- Logo e Informações da Empresa -->
                <div class="row">
                    <div class="col text-left" style="text-align: left">
                        <img
                            src="/files/img/sys/companylogo.png"
                            class="img-fluid"
                            alt="image"
                            width="120px"
                            height="120px"
                            style="text-align: left"
                        />
                    </div>
                    <div class="col">
                        <br />
                    </div>
                    <div class="col text-right" style="text-align: right">
                        <!-- Informações do Período -->
                        <div class="text-center">
                            <p style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">
                                {{ formatMonthYear(salaryProcess.month, salaryProcess.year) }}
                            </p>
                            <p style="font-size: 10px; margin-top: 5px;">
                                Recibo de Vencimento
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Informações da Empresa -->
                <div class="row">
                    <div class="col">
                        <p style="font-size:10px">
                            Dana Place
                            <br />
                            Cimento a Ponta de Ouro
                            <br />
                            Matutuine, Moçambique
                            <br />
                            Tel: +258 87 914 1774
                            <br />
                            Email: info@ieareiabranca.com
                        </p>
                    </div>
                    <div class="col">
                        <br />
                    </div>
                </div>

                <!-- Dados do Funcionário -->
                <div class="row">
                    <div class="col-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="2" class="bg-primary text-white" style="font-size: 10px;">
                                        DADOS DO FUNCIONÁRIO
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Nome</td>
                                    <td style="font-size: 10px;">{{ payslipData.technicianName || '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Código</td>
                                    <td style="font-size: 10px;">{{ payslipData.technicianCode || '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Departamento</td>
                                    <td style="font-size: 10px;">{{ payslipData.department || '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Área</td>
                                    <td style="font-size: 10px;">{{ payslipData.area || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="2" class="bg-success text-white" style="font-size: 10px;">
                                        PERÍODO DE PAGAMENTO
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Mês/Ano</td>
                                    <td style="font-size: 10px;">{{ formatMonthYear(salaryProcess.month, salaryProcess.year) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Data Processamento</td>
                                    <td style="font-size: 10px;">{{ formatDateTime(salaryProcess.processed_at) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Status</td>
                                    <td style="font-size: 10px;">
                                        <span v-if="salaryProcess.status === 'pending'">PENDENTE</span>
                                        <span v-else-if="salaryProcess.status === 'processed'">PROCESSADO</span>
                                        <span v-else-if="salaryProcess.status === 'approved'">APROVADO</span>
                                        <span v-else-if="salaryProcess.status === 'paid'">PAGO</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10px; font-weight: bold;">Processo</td>
                                    <td style="font-size: 10px;">{{ salaryProcess.title }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detalhamento Salarial -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="bg-info text-white" style="font-size: 10px;">VENCIMENTOS</th>
                            <th class="bg-info text-white" style="font-size: 10px;">VALOR</th>
                            <th class="bg-info text-white" style="font-size: 10px;">DESCONTOS</th>
                            <th class="bg-info text-white" style="font-size: 10px;">VALOR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-size: 10px;">Salário Base</td>
                            <td style="font-size: 10px;">{{ payslipData.baseSalary || '-' }}</td>
                            <td style="font-size: 10px;">Descontos Diversos</td>
                            <td style="font-size: 10px;">{{ payslipData.deductions || '-' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px;">Horas Extras ({{ payslipData.overtimeHours || 0 }}h)</td>
                            <td style="font-size: 10px;">{{ payslipData.overtimeAmount || '-' }}</td>
                            <td style="font-size: 10px;">-</td>
                            <td style="font-size: 10px;">-</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px;">Bônus e Gratificações</td>
                            <td style="font-size: 10px;">{{ payslipData.bonus || '-' }}</td>
                            <td style="font-size: 10px;">-</td>
                            <td style="font-size: 10px;">-</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px;">-</td>
                            <td style="font-size: 10px;">-</td>
                            <td style="font-size: 10px;">-</td>
                            <td style="font-size: 10px;">-</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <td style="font-size: 10px; font-weight: bold;">TOTAL VENCIMENTOS</td>
                            <td style="font-size: 10px; font-weight: bold;">{{ payslipData.totalEarnings || '-' }}</td>
                            <td style="font-size: 10px; font-weight: bold;">TOTAL DESCONTOS</td>
                            <td style="font-size: 10px; font-weight: bold;">{{ payslipData.totalDeductions || '-' }}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Resumo Final -->
                <table class="table table-bordered">
                    <tbody>
                        <tr class="bg-success text-white">
                            <td style="font-size: 12px; font-weight: bold;">SALÁRIO LÍQUIDO A RECEBER</td>
                            <td style="font-size: 14px; font-weight: bold;">{{ payslipData.netSalary || '-' }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Observações -->
                <table v-if="payslipData.hasObservations" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="bg-warning text-dark" style="font-size: 10px;">
                                OBSERVAÇÕES
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-size: 10px;">
                                {{ payslipData.observations }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Assinaturas -->
                <div class="row mt-4">
                    <div class="col-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="bg-secondary text-white text-center" style="font-size: 10px;">
                                        FUNCIONÁRIO
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-size: 10px; height: 60px; vertical-align: bottom; text-align: center;">
                                        <div style="border-top: 1px solid #000; width: 200px; margin: 40px auto 0;">
                                            Assinatura
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="bg-secondary text-white text-center" style="font-size: 10px;">
                                        RECURSOS HUMANOS
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-size: 10px; height: 60px; vertical-align: bottom; text-align: center;">
                                        <div style="border-top: 1px solid #000; width: 200px; margin: 40px auto 0;">
                                            Assinatura e Carimbo
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rodapé -->
                <div class="row mt-4">
                    <div class="col-12 text-center">
                        <p style="font-size: 8px; color: #666;">
                            Este recibo de salário foi gerado automaticamente pelo sistema de gestão do Condomínio Dana Place.<br>
                            Documento confidencial de uso pessoal do funcionário.<br>
                            <strong>Data de geração:</strong> {{ formatDateTime(new Date()) }} | 
                            <strong>Período:</strong> {{ formatMonthYear(salaryProcess.month, salaryProcess.year) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import moment from 'moment'
import {useToastr} from '../../../toastr';
import VueFeather from 'vue-feather'
import { usePaperizer } from "paperizer"


const toastr = useToastr();

const route = useRoute()
const router = useRouter()
const salaryProcess = ref({})
const loading = ref(false)
const loadingPrint = ref(false)
const currentPayslipItem = ref(null)

// Paperizer configurations
let { paperize: paperizePayroll } = usePaperizer("print-payroll", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `Folha Salarial`,
});

const createPayslipPaperizer = (technicianName) => {
    const { paperize } = usePaperizer(`print-payslip-${Date.now()}`, {
        styles: [
            "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
        ],
        windowTitle: `Payslip - ${technicianName}`,
    });
    return paperize;
};

const totals = computed(() => {
    if (!salaryProcess.value.items) return {}
    
    return salaryProcess.value.items.reduce((acc, item) => {
        acc.base_salary += parseFloat(item.base_salary)
        acc.overtime_hours += parseFloat(item.overtime_hours)
        acc.overtime_amount += parseFloat(item.overtime_amount)
        acc.bonus += parseFloat(item.bonus)
        acc.deductions += parseFloat(item.deductions)
        acc.net_salary += parseFloat(item.net_salary)
        return acc
    }, {
        base_salary: 0,
        overtime_hours: 0,
        overtime_amount: 0,
        bonus: 0,
        deductions: 0,
        net_salary: 0
    })
})

const payslipData = computed(() => {
    if (!currentPayslipItem.value) return {}
    
    const item = currentPayslipItem.value
    const totalEarnings = parseFloat(item.base_salary) + parseFloat(item.overtime_amount) + parseFloat(item.bonus)
    
    return {
        technicianName: item.technician.name,
        technicianCode: item.technician.code,
        department: item.technician.department?.name || '-',
        area: item.technician.area?.name || '-',
        baseSalary: formatCurrency(item.base_salary),
        overtimeHours: item.overtime_hours,
        overtimeAmount: formatCurrency(item.overtime_amount),
        bonus: formatCurrency(item.bonus),
        deductions: formatCurrency(item.deductions),
        totalEarnings: formatCurrency(totalEarnings),
        totalDeductions: formatCurrency(item.deductions),
        netSalary: formatCurrency(item.net_salary),
        observations: item.observations || '',
        hasObservations: !!(item.observations && item.observations.trim())
    }
})

const getData = async () => {
    loading.value = true
    try {
        const response = await axios.get(`/salary-processes/${route.params.id}`)
        salaryProcess.value = response.data.salary_process
    } catch (error) {
        console.error('Erro ao buscar dados:', error)
        toastr.error('Erro ao carregar processo salarial')
        router.push('/admin/salary-processes')
    } finally {
        loading.value = false
    }
}

const approveProcess = async () => {
    if (confirm('Tem certeza que deseja aprovar este processo salarial?')) {
        try {
            await axios.post(`/salary-processes/${route.params.id}/approve`)
            toastr.success('Processo salarial aprovado com sucesso!')
            getData()
        } catch (error) {
            toastr.error('Erro ao aprovar processo salarial')
        }
    }
}

const markAsPaid = async () => {
    if (confirm('Tem certeza que deseja marcar este processo como pago?')) {
        try {
            await axios.post(`/salary-processes/${route.params.id}/mark-as-paid`)
            toastr.success('Processo salarial marcado como pago!')
            getData()
        } catch (error) {
            toastr.error('Erro ao marcar processo como pago')
        }
    }
}

const generateReport = async () => {
    try {
        // Fazer download direto do PDF
        window.open(`/salary-processes/${route.params.id}/report`, '_blank');
        toastr.success('Relatório de folha salarial gerado com sucesso!');
    } catch (error) {
        toastr.error('Erro ao gerar relatório');
    }
}

const generatePayslip = (itemId, technicianName) => {
    try {
        // Fazer download direto do PDF do payslip
        window.open(`/salary-processes/${route.params.id}/payslip/${itemId}`, '_blank');
        toastr.success(`Payslip de ${technicianName} gerado com sucesso!`);
    } catch (error) {
        toastr.error('Erro ao gerar payslip');
    }
}

const generateAllPayslips = () => {
    if (!salaryProcess.value.items || salaryProcess.value.items.length === 0) {
        toastr.warning('Nenhum técnico encontrado para gerar payslips');
        return;
    }

    // Abrir uma nova aba para cada payslip com um pequeno delay
    salaryProcess.value.items.forEach((item, index) => {
        setTimeout(() => {
            window.open(`/salary-processes/${route.params.id}/payslip/${item.id}`, '_blank');
        }, index * 500); // 500ms de delay entre cada download
    });

    toastr.success(`Gerando ${salaryProcess.value.items.length} payslips...`);
}

const printPayroll = () => {
    loadingPrint.value = true;
    paperizePayroll();
    loadingPrint.value = false;
}

const printPayslip = (item) => {
    loadingPrint.value = true;
    
    // Set the current payslip item for reactive data
    currentPayslipItem.value = item;
    
    // Create paperizer instance for payslip
    const { paperize } = usePaperizer("print-payslip", {
        styles: [
            "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
        ],
        windowTitle: `Payslip - ${item.technician.name}`,
    });
    
    // Wait a bit for Vue to update the template, then print
    setTimeout(() => {
        paperize();
        loadingPrint.value = false;
    }, 100);
}

const formatMonthYear = (month, year) => {
    const monthNames = [
        'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ]
    return `${monthNames[month - 1]} ${year}`
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-MZ', {
        style: 'currency',
        currency: 'MZN'
    }).format(value)
}

const formatDateTime = (dateTime) => {
    return dateTime ? moment(dateTime).format('DD/MM/YYYY HH:mm') : '-'
}

onMounted(() => {
    getData()
})
</script>

<style scoped>
.table th, .table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.8rem;
}

dl dt {
    font-weight: 600;
}

/* Print specific styles */
@media print {
    .table {
        font-size: 10px !important;
    }
    
    .bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }
    
    .bg-success {
        background-color: #198754 !important;
        color: white !important;
    }
    
    .bg-info {
        background-color: #0dcaf0 !important;
        color: white !important;
    }
    
    .bg-warning {
        background-color: #ffc107 !important;
        color: black !important;
    }
    
    .bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }
}
</style>