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
	<link rel="shortcut icon" href="{{asset('files/img/sys/logoinogesticon.png')}}" />
	<link href="{{asset('template/css/app.css')}}" rel="stylesheet">
	<link rel="stylesheet" href="{{asset('toastr.min.css')}}" />
	<title>M+D - InoGest</title>
</head>

<body>
	<div id="app" class="wrapper">
		<nav id="sidebar" class="sidebar">
			<div class="sidebar-content js-simplebar">
				<router-link class="sidebar-brand" to="/admin/stock/dashboard">
          			<span class="align-middle">M+D - InoGest</span>
        		</router-link>

				<ul class="sidebar-nav">
					<li class="sidebar-header">
						Páginas
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/stock/dashboard">
							<i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Painel Geral</span>
						</router-link>
					</li>
					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/stock/dashboard">
							<i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Painel Operacional</span>
						</router-link>
					</li>

					<li class="sidebar-header">
						Configurações do sistema
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/users">
							<i class="align-middle" data-feather="user"></i> <span class="align-middle">Usuários</span>
						</router-link>
					</li>

					{{-- <li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/areas">
							<i class="align-middle" data-feather="square"></i> <span class="align-middle">Áreas</span>
						</router-link>
					</li>
					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/destinations">
							<i class="align-middle" data-feather="codepen"></i> <span class="align-middle">Clientes</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/centercost">
							<i class="align-middle" data-feather="dollar-sign"></i> <span class="align-middle">Centros de custo</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/type_equipments">
							<i class="align-middle" data-feather="list"></i> <span class="align-middle">Tipos de Equipamentos</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/malfunctions">
							<i class="align-middle" data-feather="alert-triangle"></i> <span class="align-middle">Tipos de Avarias</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/suppliers">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Fornecedores</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/tasks">
							<i class="align-middle" data-feather="tablet"></i> <span class="align-middle">Tipo de Atividades</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/taskplans">
							<i class="align-middle" data-feather="tablet"></i> <span class="align-middle">Plano de Atividades</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/equipments" >
							<i class="align-middle" data-feather="package"></i> <span class="align-middle">Equipamentos/Ativos</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/taskmcscr" >
							<i class="align-middle" data-feather="activity"></i> <span class="align-middle">Realizar Atividades</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/mcscr" >
							<i class="align-middle" data-feather="database"></i> <span class="align-middle">MCSCR</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<a data-target="#config" data-toggle="collapse" class="sidebar-link collapsed">
							<i class="align-middle" data-feather="layers"></i> <span class="align-middle">Registro MCSCR</span>
						</a>
						<ul id="config" class="sidebar-dropdown list-unstyled collapse " data-parent="#sidebar">
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/reasons">Motivos</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/causes">Causas</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/solutions">Soluções</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/consequences">Consequências</router-link></li>
							<li class="sidebar-item"><router-link class="sidebar-link" to="/admin/mcscr/recommendations">Recomendações</router-link></li>
						</ul>
					</li> --}}

					{{-- <li class="sidebar-header">
						Recursos Humanos
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/departments">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Departamentos</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/technicians">
							<i class="align-middle" data-feather="users"></i> <span class="align-middle">Técnicos</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/technicianrequests">
							<i class="align-middle" data-feather="users"></i> <span class="align-middle">Requisições</span>
						</router-link>
					</li> --}}


					{{-- <li class="sidebar-header">
						Ferramentaria
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/toolshops">
							<i class="align-middle" data-feather="tool"></i> <span class="align-middle">Ferramentas</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/toolrequests">
							<i class="align-middle" data-feather="list"></i> <span class="align-middle">Requisições</span>
						</router-link>
					</li> --}}


					<li class="sidebar-header">
						Stock
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/stockcenters">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Centro de Stock</span>
						</router-link>
					</li>


					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/brands">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Marca</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/categories">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Categoria</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/stocksuppliers">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Fornecedor Stock</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/products">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Produto</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/inventories">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Inventário</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/entrynotes">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Nota de Entrada</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/exitnotes">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Nota de Saída</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/stocktransfers">
							<i class="align-middle" data-feather="truck"></i> <span class="align-middle">Transferência</span>
						</router-link>
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/stockrequests">
							<i class="align-middle" data-feather="list"></i> <span class="align-middle">Requisições</span>
						</router-link>
					</li>

					

					

					

					<li class="sidebar-header">
						Configurações da conta
					</li>

					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/mcscr" >
							<i class="align-middle" data-feather="settings"></i> <span class="align-middle">Perfil Organização</span>
						</router-link>
					</li>
					<li class="sidebar-item">
						<router-link class="sidebar-link" to="/admin/mcscr" >
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
									<router-link to="/admin/stock/dashboard" class="dropdown-item" >Painel de Análise</router-link>
									<router-link to="/admin/stock/dashboard" class="dropdown-item" >Perfil</router-link>
									<router-link to="/admin/stock/dashboard" class="dropdown-item" >Organização</router-link>
								
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
							<router-link class="dropdown-item" to="/admin/stock/dashboard"><i class="align-middle me-1" data-feather="home"></i>
								Dashboard</router-link>
							<router-link class="dropdown-item" to="/admin/stock/dashboard"><i class="align-middle me-1" data-feather="book-open"></i>
								Documentação</router-link>
							<router-link class="dropdown-item" to="/admin/stock/dashboard"><i class="align-middle me-1"
									data-feather="edit"></i> Ajuda</router-link>
						</div>
					</li>
					
				</ul>


				<div class="navbar-collapse collapse">
					<ul class="navbar-nav navbar-align">
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
								{{-- <form action="{{route('logout')}}" method="POST">
									@csrf
									<a class="dropdown-item"  onclick="event.preventDefault(); this.closest('form').submit()" href="#">Sair</a>
								</form> --}}

								{{-- <form action="{{route('logout')}}" method="POST">
									@csrf
									
							   
								  <a class="sidebar-link" href="#" onclick="event.preventDefault(); this.closest('form').submit()" aria-expanded="false">
									<span>
									  <i class="ti ti-login"></i>
									</span>
									<span class="hide-menu">Sair</span>
								  </a>
								</form> --}}
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
									<router-link to="/admin/stock/dashboard" class="text-muted" href="#">Ajuda</router-link>
								</li>
								{{-- <li class="list-inline-item">
									<a class="text-muted" href="#">Privacy</a>
								</li> --}}
								<li class="list-inline-item">
									<router-link to="/admin/stock/dashboard" class="text-muted" href="#">Termos e Privacidade</router-link>
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
</body>

</html>