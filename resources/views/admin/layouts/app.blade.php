<!DOCTYPE html>
<html lang="pt">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="M+D - InoGest">
	<meta name="author" content="M+D - InoGest">
	<meta name="keywords" content="M+D - InoGest">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
	@vite(['resources/css/app.css', 'resources/js/app.js'])
	<link rel="shortcut icon" href="{{asset('files/img/sys/companylogo.png')}}" />
	<link href="{{asset('template/css/app.css')}}" rel="stylesheet">
	<link rel="stylesheet" href="{{asset('toastr.min.css')}}" />
	<title>M+D - InoGest</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
	<div id="app" class="wrapper">
		<nav id="sidebar" class="sidebar">
			<div class="sidebar-content js-simplebar">
				<router-link class="sidebar-brand" to="/admin/dashboard">
          			<span class="align-middle">M+D - InoGest</span>
        		</router-link>

				<ul class="sidebar-nav">
					<li class="sidebar-header">
						Páginas
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/home">
							<i class="align-middle" data-feather="home"></i> <span class="align-middle">{{__('message.home')}}</span>
						</router-link>
						<router-link class="sidebar-link" to="/admin/dashboard">
							<i class="align-middle" data-feather="sliders"></i> <span class="align-middle">{{__('message.dashboard')}}</span>
						</router-link>
					</li>

					<li class="sidebar-header">
						Manutenção
					</li>

					<li class="sidebar-item">
						<a data-target="#maintenance" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="tool"></i> <span class="align-middle">Manutenção</span>
						</a>
						<ul id="maintenance" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/malfunctions">Tipos de Avarias</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/tasks">Tipo de Actividades</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/taskplans">Plano de Actividades</router-link></li>
						</ul>
					</li>
					<li class="sidebar-item">
						<a data-target="#equipments" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Equipamentos e Ativos</span>
						</a>
						<ul id="equipments" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/equipments">Equipamentos/Activos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/type_equipments">Tipos de Equipamentos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/hourdistances">Horas/Distância Operação</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/fuel">Combústivel</router-link></li>
							
						</ul>
					</li>

				
					<li class="sidebar-item">
						<a data-target="#jobcard" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="book"></i> <span class="align-middle">Job Cards</span>
						</a>
						<ul id="jobcard" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr">Corretivas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/taskmcscr">Preventivas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/jobtasks">Recomendações</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/quotation">Cotação</router-link></li>

						</ul>
					</li>
					<li class="sidebar-item">
						<a data-target="#tools" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="pen-tool"></i> <span class="align-middle">Ferramentaria</span>
						</a>
						<ul id="tools" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/toolshops">Ferramentas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/toolrequests">Requisições</router-link></li>				
						</ul>
					</li>


					<li class="sidebar-header">
						Atas
					</li>

					<li class="sidebar-item">
						<a data-target="#atas" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="layers"></i> <span class="align-middle">Atas</span>
						</a>
						<ul id="atas" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/meetingtype">Tipos de Reunião</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/meeting">Reuniões</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/meetingtask">Tarefas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/meetingparticipant">Funcionários</router-link></li>
						</ul>
					</li>

					<li class="sidebar-header">
						Recursos Humanos
					</li>

					<li class="sidebar-item">
						<a data-target="#hresoruce" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="users"></i> <span class="align-middle">Recursos Humanos</span>
						</a>
						<ul id="hresoruce" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/departments">Departamentos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/technicians">Técnicos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/contract-types">Tipos de Contrato</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/technicianrequests">Requisições</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/absences">Ausências</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/work-schedule">Escala de Trabalho</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/salary-processes">Processar Salários</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/entry-guides">Guias de Entrada</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/vacation-plans">Plano de Férias</router-link></li>
						</ul>
					</li>
					<li class="sidebar-item">
						<a data-target="#documents" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="file-text"></i> <span class="align-middle">Gestão Documental</span>
						</a>
						<ul id="documents" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/typedocuments">Tipos de Documentos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/documents">Documentos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/trips">Viagens</router-link></li>

									

						</ul>
					</li>

					<li class="sidebar-header">
						Finanças
					</li>

					<li class="sidebar-item">
						<a data-target="#finance" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="dollar-sign"></i> <span class="align-middle">Financeiro</span>
						</a>
						<ul id="finance" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/expenses">Despesas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/expense-categories">Categorias</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/fees">Taxas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/fee-invoices">Faturas de Taxas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/waterconsumption">Consumo Água</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/energyconsumption">Consumo Energia</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/energyinvoice">Fatura Energia</router-link></li>
						</ul>
					</li>

					<li class="sidebar-header">
						Stock
					</li>

					<li class="sidebar-item">
						<a data-target="#stock" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="database"></i> <span class="align-middle">Stock</span>
						</a>
						<ul id="stock" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/stockcenters">Centro de Stock</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/brands">Marca</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/categories">Categoria</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/stocksuppliers">Fornecedor Stock</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/products">Produto</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/inventories">Inventário</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/entrynotes">Nota de Entrada</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/exitnotes">Nota de Saída</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/stocktransfers">Transferência</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/stockrequests">Requisições</router-link></li>
						</ul>
					</li>

					<li class="sidebar-item">
						<a data-target="#register" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="archive"></i> <span class="align-middle">Registro</span>
						</a>
						<ul id="register" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/users">Usuários</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/areas">Áreas</router-link></li>
							
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/destinations">Clientes</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/centercost">Centros de custo</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/suppliers">Fornecedores</router-link></li>
							
									<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/reasons">Motivos</router-link></li>
									<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/causes">Causas</router-link></li>
									<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/solutions">Soluções</router-link></li>
									<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/consequences">Consequências</router-link></li>
									<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/recommendations">Recomendações</router-link></li>
						
							
						</ul>
					</li>

					{{-- <li class="sidebar-header">
						Equipamentos e Ativos
					</li> --}}
					
					

					{{-- <li class="sidebar-header">
						Operação
					</li> --}}
					
					<!-- ============================================ -->
					<!-- NOVO MÓDULO: PRODUÇÃO AVÍCOLA (OVOS)          -->
					<!-- ============================================ -->
					<li class="sidebar-header">
						Produção Avícola
					</li>
					<li class="sidebar-item">
						<a data-target="#poultry" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="globe"></i> <span class="align-middle">Gestão de Ovos</span>
						</a>
						<ul id="poultry" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/granjas">Farmas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/galpoes">Galpões</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/lotes">Lotes</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/linhagens">Linhagens</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/producao-diaria">Produção Diária</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mortalidade">Mortalidade</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/despesas-ovos">Despesas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/calendario-vacinal">Calendário Vacinal</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/classificacao-ovos">Classificação de Ovos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/ovos">Ovos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/embalagem">Embalagem</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/estoque-ovos">Estoque de Ovos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/clientes-ovos">Clientes</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/pedidos">Pedidos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/pedidos/calendario">Calendário Pedidos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/separacao-ovos">Separação de Ovos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/expedicao-ovos">Expedição</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/expedicao-ovos/calendario">Calendário Expedição</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/rastreabilidade">Rastreabilidade</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/dashboard-ovos">Dashboard Ovos</router-link></li>
						</ul>
					</li>

					<li class="sidebar-item">
						<a data-target="#egg-masters" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="bar-chart-2"></i> <span class="align-middle">Indicadores Avícolas</span>
						</a>
						<ul id="egg-masters" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/kpi-postura">KPIs de Postura</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/kpi-mortalidade">Taxa de Mortalidade</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/kpi-conversao">Conversão Alimentar</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/curva-postura">Curva de Postura</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/ranking-galpoes">Ranking de Galpões</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/custo-duzia">Custo por Dúzia</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/despesas-ovos/dashboard">Dashboard Despesas</router-link></li>
						</ul>
					</li>

					<li class="sidebar-item">
						<a data-target="#egg-reports" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="file-text"></i> <span class="align-middle">Relatórios Ovos</span>
						</a>
						<ul id="egg-reports" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/relatorio-producao-diaria">Relatório Produção Diária</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/relatorio-refugos">Relatório de Refugos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/relatorio-estoque-ovos">Relatório Estoque Ovos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/relatorio-sanitario">Relatório Sanitário</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/relatorio-rastreabilidade">Relatório Rastreabilidade</router-link></li>
						</ul>
					</li>

					<li class="sidebar-item">
						<a data-target="#egg-config" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="settings"></i> <span class="align-middle">Configurações Avícolas</span>
						</a>
						<ul id="egg-config" class="sidebar-dropdown list-unstyled collapse" data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/linhagens">Linhagens</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/categorias-ovos">Categorias de Ovos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/motivos-refugo">Motivos de Refugo</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/vacinas">Vacinas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/alertas-ovos">Alertas</router-link></li>
						</ul>
					</li>
					<!-- ============================================ -->
					<!-- FIM DO MÓDULO DE OVOS                          -->
					<!-- ============================================ -->
					

					
					<li class="sidebar-header">
						Operações
					</li>
					<li class="sidebar-item">
						<a data-target="#logistic" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="globe"></i> <span class="align-middle">Logística</span>
						</a>
						<ul id="logistic" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/logisticcustomer">Clientes</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/driver">Motoristas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/logisticdestination">Destinos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/logistictrip">Viagens</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/logisticquotation">Cotação Viagens</router-link></li>
						</ul>
					</li>
					<li class="sidebar-item">
						<a data-target="#operation" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="columns"></i> <span class="align-middle">Operações</span>
						</a>
						<ul id="operation" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/shifts">Planeamento</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/shiftequipmentrequest">Requisições</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/schedulework">Escala de Trabalho</router-link></li>							
						</ul>
					</li>
					<li class="sidebar-item">
						<a data-target="#jobcardcampaign" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="book"></i> <span class="align-middle">Inspeção</span>
						</a>
						<ul id="jobcardcampaign" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/inspections">Inspeção</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="">Inspeção Pneus</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/generalinspections">Inspeção Geral</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="">Inspeção Lubrificantes</router-link></li>	
						</ul>
					</li>

					<li class="sidebar-item">
						<a data-target="#tiremanagement" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="aperture"></i> <span class="align-middle">Gestão de Pneus</span>
						</a>
						<ul id="tiremanagement" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/tireallocations">Alocação de Pneus</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/tirelayouts">Modelo de Pneus</router-link></li>
						</ul>
					</li>


					<li class="sidebar-header">
						Segurança
					</li>
					<li class="sidebar-item">
						<a data-target="#inosecure" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="globe"></i> <span class="align-middle">InoSecure</span>
						</a>
						<ul id="inosecure" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/inosecure-transactions">Transações</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/inosecure-customers">Utentes</router-link></li>

						</ul>
					</li>







					{{-- <li class="sidebar-header">
						Ferramentaria
					</li> --}}
					



					

					<li class="sidebar-header">
						Configurações da conta
					</li>

					{{-- <li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/mcscr" >
							<i class="align-middle" data-feather="settings"></i> <span class="align-middle">Perfil Organização</span>
						</router-link>
					</li> --}}
					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/profile" >
							<i class="align-middle" data-feather="settings"></i> <span class="align-middle">Perfil Usuário</span>
						</router-link>
					</li>

				
			</div>
		</nav>

		<div class="main">
			<nav class="navbar navbar-expand navbar-light navbar-bg">
				<a class="sidebar-toggle d-flex">
					<i class="hamburger align-self-center"></i>
				</a>

				
				<ul class="navbar-nav d-none d-lg-flex">
					<li class="nav-item px-2 dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="megaDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
							aria-expanded="false">
							Mega Menu
						</a>
						<div class="dropdown-menu dropdown-menu-start dropdown-mega" aria-labelledby="megaDropdown">
							<div class="d-md-flex align-items-start justify-content-start">
								<div class="dropdown-mega-list">
									<div class="dropdown-header">Cadastros</div>
									<router-link to="/admin/users/create" class="dropdown-item" >Usuários</router-link>
									<router-link to="/admin/areas/create" class="dropdown-item" >Áreas</router-link>
									<router-link to="/admin/destinations/create" class="dropdown-item" >Clientes</router-link>
									<router-link to="/admin/centercost/create" class="dropdown-item" >Centros de Custo</router-link>
									<router-link to="/admin/type_equipments/create" class="dropdown-item" >Tipos de Equipamento</router-link>
									<router-link to="/admin/equipments/create" class="dropdown-item" >Equipamentos</router-link>
									<router-link to="/admin/malfunctions/create" class="dropdown-item" >Avarias</router-link>
									<router-link to="/admin/suppliers/create" class="dropdown-item" >Fornecedores</router-link>
									<router-link to="/admin/mcscr/create" class="dropdown-item" >MCSCR</router-link>
								</div>
								<div class="dropdown-mega-list">
									<div class="dropdown-header">Páginas</div>
									<router-link to="/admin/dashboard" class="dropdown-item" >Painel de Análise</router-link>
									<router-link to="/admin/dashboard" class="dropdown-item" >Perfil</router-link>
									<router-link to="/admin/dashboard" class="dropdown-item" >Organização</router-link>
								</div>
							</div>
						</div>
					</li>

					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" id="dropdownMenuButton" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
							aria-expanded="false">
							Recursos
						</a>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
							<router-link class="dropdown-item" to="/admin/dashboard"><i class="align-middle me-1" data-feather="home"></i>
								Dashboard</router-link>
							<router-link class="dropdown-item" to="/admin/dashboard"><i class="align-middle me-1" data-feather="book-open"></i>
								Documentação</router-link>
							<router-link class="dropdown-item" to="/admin/dashboard"><i class="align-middle me-1"
									data-feather="edit"></i> Ajuda</router-link>
						</div>

					</li>
					
					
				</ul>


				<div class="navbar-collapse collapse">
					<ul class="navbar-nav navbar-align">
						<li class="nav-item dropdown">
							<div class="dropdown-item">
								<select id="language-selector" class="form-control" onchange="changeLanguage(this.value)">
									<option value="en">English</option>
									<option value="pt">Português</option>
								</select>
							</div>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-toggle="dropdown">
								<div class="position-relative">
									<i class="align-middle" data-feather="bell"></i>
									<span class="indicator">{{Auth::user()->unreadNotifications->count()}}</span>
								</div>
							</a>
							<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right py-0" aria-labelledby="alertsDropdown">
								<div class="dropdown-menu-header">
									{{Auth::user()->unreadNotifications->count()}} Novas notificações
								</div>

								<div class="list-group">

									@foreach (Auth::user()->notifications->take(3) as $item)
										<router-link to="/admin/notifications" class="list-group-item">
											<div class="row g-0 align-items-center">
												<div class="col-2">
													<i class="text-warning" data-feather="bell"></i>
												</div>
												<div class="col-10">
													<div class="text-dark">{{ Str::words($item->data['data'], 10) }}</div>
                                        			<div class="text-muted small mt-1">{{$item->created_at}}</div>
												</div>
											</div>
										</router-link >
                            		@endforeach	
								</div>

								<div class="dropdown-menu-footer">
									<router-link to="/admin/notifications" class="text-muted" href="#">Mostrar todas notificações</router-link>
								
								</div>
							</div>
						</li>

						<li class="nav-item dropdown">
							<a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-toggle="dropdown">
								<i class="align-middle" data-feather="settings"></i>
							</a>

							<a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-toggle="dropdown">
								<img src="{{asset('files/img/sys/logoinogesticon.png')}}" class="avatar img-fluid rounded mr-2" alt="{{Auth()->user()->firstName}} {{Auth()->user()->lastName}}" /> <span class="text-dark">{{Auth()->user()->firstName}} {{Auth()->user()->lastName}}</span>
							</a>
							<div class="dropdown-menu dropdown-menu-right">
							
								<a class="dropdown-item" href="#"><i class="align-middle mr-1" data-feather="user"></i> Perfil</a>
								<a class="dropdown-item" href="#"><i class="align-middle mr-1" data-feather="help-circle"></i>Ajuda</a>
								<div class="dropdown-divider"></div>
								<form action="{{route('logout')}}" id="form" method="POST">
									@csrf
								  <button type="submit" class="btn btn-outline-primary mx-3 mt-2 d-block">Sair</button>
								  </form>
							</div>
						</li>
					</ul>
				</div>
			</nav>

			<main class="content">
				<div class="container-fluid p-0">

					<router-view></router-view>

				</div>
			</main>

			<footer class="footer">
				<div class="container-fluid">
					<div class="row text-muted">
						<div class="col-6 text-left">
							<p class="mb-0">
								<a href="#" class="text-muted"><strong>M+D - InoGest</strong></a> &copy; {{ date('Y')}}
							</p>
						</div>
						<div class="col-6 text-right">
							<ul class="list-inline">
								{{-- <li class="list-inline-item">
									<a class="text-muted" href="#">Support</a>
								</li> --}}
								<li class="list-inline-item">
									<router-link to="/admin/dashboard" class="text-muted" href="#">Ajuda</router-link>
								</li>
								{{-- <li class="list-inline-item">
									<a class="text-muted" href="#">Privacy</a>
								</li> --}}
								<li class="list-inline-item">
									<router-link to="/admin/dashboard" class="text-muted" href="#">Termos e Privacidade</router-link>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>

	<script src="{{asset('template/js/app.js')}}"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
	<script>
		window.user = {!! Auth::user() !!}
	</script>
	<script>
		// document.addEventListener("DOMContentLoaded", function () {
		// 	// Define o idioma salvo no localStorage ao carregar a página
		// 	const savedLanguage = localStorage.getItem("language") || "en";
		// 	document.getElementById("language-selector").value = savedLanguage;
		// });
	
		// function changeLanguage(lang) {
		// 	localStorage.setItem("language", lang); // Salvar a escolha no localStorage
		// 	window.location.reload(); // Recarregar a página para aplicar as mudanças
		// }

		document.addEventListener("DOMContentLoaded", function () {
    const savedLanguage = localStorage.getItem("language") || "en";

    // Define o idioma no Vue I18n (se existir no app Vue)
    if (window.app && window.app.$i18n) {
        window.app.$i18n.locale = savedLanguage;
    }

    document.getElementById("language-selector").value = savedLanguage;
});

function changeLanguage(lang) {
    localStorage.setItem("language", lang); // Salvar idioma no localStorage

    // Alterar idioma no Vue sem recarregar
    if (window.app && window.app.$i18n) {
        window.app.$i18n.locale = lang;
    }

    // Fazer request para o backend para alterar o idioma do Laravel
    fetch('/set-locale', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ locale: lang })
    }).then(() => {
        console.log("Idioma alterado no Laravel:", lang);
		window.location.reload(); // Recarregar a página para aplicar as mudanças

    });
}
	</script>
</body>

</html>