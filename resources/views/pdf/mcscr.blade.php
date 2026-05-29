<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
  

    <style type="text/css">
        @page {
            margin: 0px;
        }
        .break-page{
          page-break-after: always;
        }

        html {
            margin-top: 70px ;
        
        }
        body {
            
            
        }
        * {
            font-family: Verdana, Arial, sans-serif;
        }
        a {
            color: #fff;
            text-decoration: none;
        }
        table {
            font-size: x-small;
            border-collapse:collapse;
            border-radius:6px;
        }
        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }
        .invoice {
            margin: 30px;
        }
        .invoice table {
            margin: 15px;
        }
        .invoice h3 {
            margin-left: 15px;
        }
        .information {
            background-color: #0eadf7;
            color: #000;
            margin: 30px;
        }

        .text{
            font-size:12px;
        }

        
        .information .logo {
            margin: 5px;
        }
        .information table {
            padding: 10px;

        }
        .body {
            margin: 30px;
        }
        table , tr , td{
        border:1px solid black;
        border-collapse: collapse;
}

.information2 {
            background-color: #0eadf7;
            color: #000;
            
        }

.header,
.footer {
    width: 100%;
    text-align: right;
    position: fixed;
    
}
.header {
    top: 0px;
}
.footer {
    bottom: 0px;
}
.pagenum:before {
    content: counter(page);
}

.remove_line{
border-right:2px solid white;    
}

.badge {
  color: white;
  padding: 3px 6px;
  text-align: center;
  border-radius: 5px;
}


    </style>

</head>
<body>

  
    <div class="footer">
       <p style="font-size:10px;">Page <span class="pagenum"></span></p> 
       <div class="information2" style=" bottom: 0; width:100%">
        <p style="color: black; font-size:10px; text-align:center">&copy; {{ date('Y') }} INOVATIS MZ LTD - Direitos Reservados.</p>    
     </div> 
    </div>

{{-- <div class="information" style="background-color: #fff;">
    <img src="https://freshfish.es/wp-content/uploads/2016/10/FRESHFISH-logo-apple.jpg" alt="Logo" width="120" class="logo"/>
    <h3>Company</h3>
    <h4>Company</h4>
    <p><strong>Coompany</strong>: </p>
    <p><strong>Coompany</strong>: </p>
    <p><strong>Coompany</strong>: </p>.,.M 
    
   
</div>


<br/> --}}



<div class="invoice"> 
    {{-- <h3 style="text-align: center"> c fvbyh </h3>

    <div class="body" style="margin-top: 15px">
       zxxxxx
    </div> --}}
     <table width="95%">
       
        <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7"> MCSCR</td>
         </tr>
         <tr>
            <td colspan="3" align="left" class="remove_line">
                <p>
                    <img src="http://ie.inogest.co.mz/files/img/sys/companylogo.png" alt="Logo" width="120" class="logo"/><br>
                    <strong>Área de Manutenção</strong> <br>
                    {{$mcscr->area->company_name }} <br>
                    {{$mcscr->area->company_address ?? 'N/A'}} <br>
                    {{$mcscr->area->company_nuit ?? 'N/A'}} <br>
                    {{$mcscr->area->province->name ?? 'N/A'}} <br>
                    {{$mcscr->area->company_mobile ?? 'N/A'}} <br>
                    {{$mcscr->area->company_email ?? 'N/A'}} 
                
                </p> 
            </td>

            <td colspan="3" align="right">
                <p>
                    <br><br><br><br>
                    <strong>Clientes</strong> <br>
                    {{$mcscr->destination->company_name }} <br>
                    {{$mcscr->destination->company_address ?? 'N/A'}} <br>
                    {{$mcscr->destination->company_nuit ?? 'N/A'}} <br>
                    {{$mcscr->destination->province->name ?? 'N/A'}} <br>
                    {{$mcscr->destination->company_mobile ?? 'N/A'}} <br>
                    {{$mcscr->destination->company_email ?? 'N/A'}} 
                
                </p> 
            </td>
         </tr>
         <tr>
            <td colspan="6" align="left">MCSCR nº: {{$mcscr->id}}</td>
         </tr>

         <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7"> Detalhes do Equipamento</td>
         </tr>
         <tr>
            <td align="center" style="background-color: #ededed">Equipamento/Ativo:</td>
            <td align="center" style="background-color: #ededed">Tipo/Frota:</td>
            <td align="center" style="background-color: #ededed">Ref:</td>
            <td align="center" style="background-color: #ededed">Marca:</td>
            <td align="center" style="background-color: #ededed">Modelo:</td>
            <td align="center" style="background-color: #ededed">Odômetro/Horímetro:</td>
         </tr>
         <tr>
            <td>{{$mcscr->equipment->name}}</td>
            <td>{{$mcscr->equipment->type_equipment->name}}</td>
            <td>{{$mcscr->equipment->ref}}</td>
            <td>{{$mcscr->equipment->make}}</td>
            <td>{{$mcscr->equipment->model}}</td>
            <td>{{$mcscr->distance}}</td>
         </tr>

         <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7">Detalhes MCSCR</td>
         </tr>
         <tr>
            <td colspan="6" align="left">
                <p>
                    <span><strong>Estado: </strong>
                        <span class="badge" 
                            @if($mcscr->mcscr_status_id==1) 
                                style="background-color: #00da0b"
                            @elseif($mcscr->mcscr_status_id==2) 
                                style="background-color: #ebef07"
                            @elseif($mcscr->mcscr_status_id==3) 
                                style="background-color: #ef0707"
                            @elseif($mcscr->mcscr_status_id==4) 
                                style="background-color: #1a6b8b"
                            @elseif($mcscr->mcscr_status_id==5) 
                                style="background-color: #07adef"
                            @endif>
                            {{ $mcscr->mcscr_status->name}}
                        </span>
                    </span>  <br>
                    <span> <strong>Tempo Paralizado: </strong>
                        @if ($mcscr->closed_at == null)
                            
                        <?php
                        $created_at = strtotime($mcscr->opened_at);
                        $closed_at = strtotime(Date::now());

                        $time = $closed_at - $created_at;


                        ?>
                        <span style="color:red">{{round($time/3600, 1);  }}Horas({{round($time/60, 1);  }}Minutos)</span>
                        @else
                        <?php
                        $created_at = strtotime($mcscr->opened_at);
                        $closed_at = strtotime($mcscr->closed_at);

                        $time = $closed_at - $created_at;


                        ?>
                        <span style="color:red">{{round($time/3600, 1);  }}Horas({{round($time/60, 1);  }}Minutos)</span>
                        @endif
                    </span> <br>
                    <span> <strong>Previsão de Saída:</strong> {{date('d-m-Y H:i',strtotime($mcscr->output_forecast)) }}</span>
                </p>
            </td>
         </tr>
         <tr>
            <td align="center" colspan="2" style="background-color: #ededed">Motivo</td>
            <td align="center" style="background-color: #ededed">Causa</td>
            <td align="center" style="background-color: #ededed">Solução</td>
            <td align="center" style="background-color: #ededed">Consequência</td>
            <td align="center" style="background-color: #ededed">Recomendação</td>
         </tr>
         <tr>
            <td colspan="2" align="left">
                <p>
                    {{$mcscr->reason_id == null ? $mcscr->reason : $mcscr->reason_name->name}}
                    {{-- <strong>Desc:</strong>{{$mcscr->reason ?? 'N/A'}} <br>
                    <strong>Reg:</strong>{{$mcscr->reason_name->name ?? 'N/A'}} <br>
                    <strong>Cód:</strong> {{$mcscr->reason_name->code ?? 'N/A'}}<br> --}}
                </p>
            </td>

            <td align="left">
                <p>
                    {{$mcscr->cause_id == null ? $mcscr->cause : $mcscr->cause_name->name}}
                    {{-- <strong>Desc:</strong>{{$mcscr->reason ?? 'N/A'}} <br>
                    <strong>Reg:</strong>{{$mcscr->reason_name->name ?? 'N/A'}} <br>
                    <strong>Cód:</strong> {{$mcscr->reason_name->code ?? 'N/A'}}<br> --}}
                </p>
            </td>

            <td align="left">
                <p>
                    {{$mcscr->solution_id == null ? $mcscr->solution : $mcscr->solution_name->name}}
                    {{-- <strong>Desc:</strong>{{$mcscr->reason ?? 'N/A'}} <br>
                    <strong>Reg:</strong>{{$mcscr->reason_name->name ?? 'N/A'}} <br>
                    <strong>Cód:</strong> {{$mcscr->reason_name->code ?? 'N/A'}}<br> --}}
                </p>
            </td>

            <td align="left">
                <p>
                    {{$mcscr->consequence_id == null ? $mcscr->consequence : $mcscr->consequence_name->name}}
                    {{-- <strong>Desc:</strong>{{$mcscr->reason ?? 'N/A'}} <br>
                    <strong>Reg:</strong>{{$mcscr->reason_name->name ?? 'N/A'}} <br>
                    <strong>Cód:</strong> {{$mcscr->reason_name->code ?? 'N/A'}}<br> --}}
                </p>
            </td>

            <td align="left">
                <p>
                    {{$mcscr->recommendation_id == null ? $mcscr->recommendation : $mcscr->recommendation_name->name}}
                    {{-- <strong>Desc:</strong>{{$mcscr->reason ?? 'N/A'}} <br>
                    <strong>Reg:</strong>{{$mcscr->reason_name->name ?? 'N/A'}} <br>
                    <strong>Cód:</strong> {{$mcscr->reason_name->code ?? 'N/A'}}<br> --}}
                </p>
            </td>

            
         </tr>

         <tr>
            <td align="center"></td>
            <td align="center" style="background-color: #ededed">Aberto em:</td>
            <td align="center" style="background-color: #ededed">Diagnóstico em:</td>
            <td align="center" style="background-color: #ededed">Execução em:</td>
            <td align="center" style="background-color: #ededed">Aprovação em:</td>
            <td align="center" style="background-color: #ededed">Fechado em:</td>
         </tr>
         <tr>
            <td align="left" style="background-color: #ededed">Data</td>
            <td align="left"> {{date('d-m-Y H:i',strtotime($mcscr->opened_at)) }} </td>
            <td align="left"> {{$mcscr->diagnosis_start_at == null ? 'N/A' : date('d-m-Y H:i',strtotime($mcscr->diagnosis_start_at)) }}</td>
            <td align="left"> {{$mcscr->execution_start_at == null ? 'N/A' : date('d-m-Y H:i',strtotime($mcscr->execution_start_at)) }}</td>
            <td align="left"> {{$mcscr->awaiting_approval_start_at == null ? 'N/A' : date('d-m-Y H:i',strtotime($mcscr->awaiting_approval_start_at)) }}</td>
            <td align="left"> {{$mcscr->closed_at == null ? 'N/A' : date('d-m-Y H:i',strtotime($mcscr->closed_at)) }}</td>
         </tr>
         <tr>
            <td align="left" style="background-color: #ededed">Duração</td>
            <td>
                N/A
            </td>
            <td>
                @if ($mcscr->diagnosis_start_at == null)           
              
                <span>N/A</span>
                @else
                <?php
                $created_at = strtotime($mcscr->opened_at);
                $diagnosis_start_at = strtotime($mcscr->diagnosis_start_at);
                $time = $diagnosis_start_at - $created_at;
                ?>
                <span>{{round($time/3600, 1);  }}H({{round($time/60, 1);  }} Min)</span> 
                @endif
            </td>
            <td>
                @if ($mcscr->execution_start_at == null)           
                
                    <span>N/A</span>
                    @else
                    <?php
                    $created_at = strtotime($mcscr->diagnosis_start_at);
                    $execution_start_at = strtotime($mcscr->execution_start_at);
                    $time = $execution_start_at - $created_at;
                    ?>
                    <span>{{round($time/3600, 1);  }}H({{round($time/60, 1);  }} Min)</span> 
                    @endif  
            </td>
            <td>
                @if ($mcscr->awaiting_approval_start_at == null)           
                <span>N/A</span>
                @else
                <?php
                $created_at = strtotime($mcscr->execution_start_at);
                $awaiting_approval_start_at = strtotime($mcscr->awaiting_approval_start_at);
                $time = $awaiting_approval_start_at - $created_at;
                ?>
                <span>{{round($time/3600, 1);  }}H({{round($time/60, 1);  }} Min)</span> 
                @endif  
            </td>
            <td>
                @if ($mcscr->closed_at == null)           
                <span>N/A</span>
                @else
                <?php
                $created_at = strtotime($mcscr->awaiting_approval_start_at);
                $closed_at = strtotime($mcscr->closed_at);
                $time = $closed_at - $created_at;
                ?>
                <span>{{round($time/3600, 1);  }}H({{round($time/60, 1);  }} Min)</span> 
                @endif  
            </td>
         </tr>

         <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7">Detalhes da Avaria</td>
         </tr>
         <tr>
            <td align="center" colspan="2" style="background-color: #ededed">Tipo de Avaria:</td>
            <td align="center" style="background-color: #ededed">Componente:</td>
            <td align="center" style="background-color: #ededed">SubComponente:</td>
            <td align="center" style="background-color: #ededed">Retrabalho:</td>
            <td align="center" style="background-color: #ededed">Motivo de Espera:</td>
         </tr>
         <tr>
            <td align="left" colspan="2">{{$mcscr->type_malfunction->name ?? 'N/A'}}</td>
            <td align="left">{{$mcscr->component->name ?? 'N/A'}}</td>
            <td align="left">{{$mcscr->subcomponent->name ?? 'N/A'}}</td>
            <td align="left">{{$mcscr->is_rework == 1 ? 'Sim' : 'Não'}}</td>
            <td align="left">{{$mcscr->waiting_status->name}}</td>
         </tr>
         

         <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7">Custos Envolvidos</td>
         </tr>
         <tr>
            <td align="center" colspan="3" style="background-color: #ededed">Mão de Obra:</td>
            <td align="center" colspan="3" style="background-color: #ededed">Material:</td>
         </tr>
         <tr>
            <td align="left" colspan="3">{{$mcscr->material_labor ?? '0'}} MT</td>
            <td align="left" colspan="3">{{$mcscr->material_cost ?? '0'}} MT</td>

         </tr>

         <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7">Requisição de Materiais : {{$requeststock->count() == 0 ? 'Sem requisição' : $requeststock->count().' requisição'}} </td>
         </tr>
         {{-- # --}}
         @foreach ($requeststock as $item)
         <tr>
            <td colspan="6" align="left">
                <p>
                    <strong>Registro</strong>:#{{$loop->index+1}} <br>
                    <strong>ID da Requisição</strong>:#{{$item->id}} <br>
                    <strong>Data da Criação</strong>:{{date('d-m-Y', strtotime($item->created_at))}} <br>
                    <strong>Criado por</strong>:{{$item->createdbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Aprovado/Reprovado por</strong>:{{$item->approvedbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Entregue por</strong>:{{$item->deliveredbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Estado</strong>:
                            <span class="badge" 
                                @if($item->request_stock_status_id==1) 
                                    style="background-color: #ebef07"
                                @elseif($item->request_stock_status_id==2) 
                                    style="background-color: #12f70e"
                                @elseif($item->request_stock_status_id==3) 
                                    style="background-color: #f70e2d"
                                @elseif($item->request_stock_status_id==4) 
                                    style="background-color: #0ea6f7"
                                @endif
                                >
                                {{ $item->status->name}}
                        </span>
                    <br>
                
                </p> 
            </td>
         </tr>
         <tr>
            <td align="center" style="background-color: #ededed">#:</td>
            <td align="center" style="background-color: #ededed">ID da Requisição:</td>
            <td align="center" colspan="2" style="background-color: #ededed">Material:</td>
            <td align="center" style="background-color: #ededed">Quantidade Requisitado:</td>
            <td align="center" style="background-color: #ededed">Quantidade Entregue:</td>
         </tr>
         @foreach ($item->requestitens as $item2)
            <tr>
                <td>#{{$loop->index+1}}</td>
                <td>#{{$item2->id}}</td>
                <td colspan="2">{{$item2->product->name}}</td>
                <td>{{$item2->quantity}}</td>
                <td>{{$item2->delivered_quantity}}</td>
            </tr>
         @endforeach
         
         @endforeach

         <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7">Requisição Técnicos : {{$requesttechnician->count() == 0 ? 'Sem requisição' : $requeststock->count().' requisição'}}</td>
         </tr>

         @foreach ($requesttechnician as $item)
         <tr>
            <td colspan="6" align="left">
                <p>
                    <strong>Registro</strong>:#{{$loop->index+1}} <br>
                    <strong>ID da Requisição</strong>:#{{$item->id}} <br>
                    <strong>Data da Criação</strong>:{{date('d-m-Y', strtotime($item->created_at))}} <br>
                    <strong>Criado por</strong>:{{$item->createdbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Aprovado/Reprovado por</strong>:{{$item->approvedbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Entregue por</strong>:{{$item->deliveredbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Estado</strong>:
                            <span class="badge" 
                                @if($item->request_technician_status_id==1) 
                                    style="background-color: #ebef07"
                                @elseif($item->request_technician_status_id==2) 
                                    style="background-color: #12f70e"
                                @elseif($item->request_technician_status_id==3) 
                                    style="background-color: #f70e2d"
                                @elseif($item->request_technician_status_id==4) 
                                    style="background-color: #0ea6f7"
                                @endif
                                >
                                {{ $item->status->name}}
                        </span>
                    <br>
                
                </p> 
            </td>
         </tr>
         <tr>
            <td align="center" style="background-color: #ededed">#:</td>
            <td align="center" style="background-color: #ededed">ID da Requisição:</td>
            <td align="center" colspan="2" style="background-color: #ededed">Departamento:</td>
            <td align="center" colspan="2" style="background-color: #ededed">Nome Técnicos Requisitados:</td>
         </tr>
         @foreach ($item->requestitens as $item2)
            <tr>
                <td>#{{$loop->index+1}}</td>
                <td>#{{$item2->id}}</td>
                <td colspan="2">{{$item2->department->name}}</td>
                <td colspan="2">{{$item2->technician->name ?? 'N/A'}}</td>
            </tr>
         @endforeach
         
         @endforeach

         <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7">Requisição Ferramentaria : {{$requesttool->count() == 0 ? 'Sem requisição' : $requeststock->count().' requisição'}}</td>
         </tr>

         @foreach ($requesttool as $item)
         <tr>
            <td colspan="6" align="left">
                <p>
                    <strong>Registro</strong>:#{{$loop->index+1}} <br>
                    <strong>ID da Requisição</strong>:#{{$item->id}} <br>
                    <strong>Data da Criação</strong>:{{date('d-m-Y', strtotime($item->created_at))}} <br>
                    <strong>Criado por</strong>:{{$item->createdbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Aprovado/Reprovado por</strong>:{{$item->approvedbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Entregue por</strong>:{{$item->deliveredbyuser->firstName ?? 'N/A'}} <br>
                    <strong>Estado</strong>:
                            <span class="badge" 
                                @if($item->request_tool_status_id==1) 
                                    style="background-color: #ebef07"
                                @elseif($item->request_tool_status_id==2) 
                                    style="background-color: #12f70e"
                                @elseif($item->request_tool_status_id==3) 
                                    style="background-color: #f70e2d"
                                @elseif($item->request_tool_status_id==4) 
                                    style="background-color: #0ea6f7"
                                @endif
                                >
                                {{ $item->status->name}}
                        </span>
                    <br>
                
                </p> 
            </td>
         </tr>
         <tr>
            <td align="center" style="background-color: #ededed">#:</td>
            <td align="center" style="background-color: #ededed">ID da Requisição:</td>
            <td align="center" colspan="2" style="background-color: #ededed">Ferramentaria:</td>
            <td align="center" colspan="2" style="background-color: #ededed">Código:</td>
         </tr>
         @foreach ($item->requestitens as $item2)
            <tr>
                <td>#{{$loop->index+1}}</td>
                <td>#{{$item2->id}}</td>
                <td colspan="2">{{$item2->tool->name ?? 'N/A'}}</td>
                <td colspan="2">{{$item2->tool->code ?? 'N/A'}}</td>
            </tr>
         @endforeach
         
         @endforeach

         <tr>
            <td colspan="6" align="center" style="background-color: #0eadf7">Envolvidos</td>
         </tr>
         <tr>
            <td align="center" colspan="3" style="background-color: #ededed">Aberto por::</td>
            <td align="center" colspan="3" style="background-color: #ededed">Fechado por:</td>
         </tr>
         <tr>
            <td align="left" colspan="3">
                {{$mcscr->opened_by_user->firstName == null ? 'N/A' : $mcscr->opened_by_user->firstName.' '.$mcscr->opened_by_user->lastName.' /'.$mcscr->opened_by_user->email }} 
                <br><br>
                <hr>
            </td>
            <td align="left" colspan="3">
                {{$mcscr->closed_by_user->firstName == null ? 'N/A' : $mcscr->closed_by_user->firstName.' '.$mcscr->closed_by_user->lastName.' /'.$mcscr->closed_by_user->email}} 
                <br><br>
                <hr>
            </td>

         </tr>

         {{-- <tr>
            <td  colspan="7" align="center" style="background-color: #f0f0f0">{{$meeting->subject}}</td>
           
         </tr>
         
         <tr>
            <td colspan="7" align="center" style="background-color: #0eadf7"> {{Str::upper(__('text.meeting_participants'))}}</td>
         </tr>

         <tr>
            <td  style="background-color: #f0f0f0">{{__('text.name')}}</td>
            <td  style="background-color: #f0f0f0">{{__('text.email')}}</td>
            <td  style="background-color: #f0f0f0">{{__('text.department')}}</td>
            <td  style="background-color: #f0f0f0">{{__('text.consensus')}}</td>
            <td  colspan="3" style="background-color: #f0f0f0">{{__('text.obs')}}</td>
         </tr>
         @foreach ($meeting->participants as $item)
         <tr>
            <td >{{$item->name}}</td>
            <td >{{$item->email}}</td>
            <td >{{$item->department->name ?? __('text.guest')}}</td>
            <td>@if ($item->status == 0) <span>{{__('text.agree')}}</span> @else <span>{{__('text.disagree')}}</span> @endif</td>
            <td  colspan="3">{{$item->obs}}</td>
         </tr>
         @endforeach

         <tr>
            <td colspan="7" align="center" style="background-color: #0eadf7">{{Str::upper(__('text.meeting_activities'))}}</td>
         </tr>

         <tr>
            <td  style="background-color: #f0f0f0">{{__('text.when')}}</td>
            <td  style="background-color: #f0f0f0">{{__('text.name')}}</td>
            
            <td  colspan="4" style="background-color: #f0f0f0">{{__('text.tasks')}}</td>
            <td  style="background-color: #f0f0f0">{{__('text.status')}}</td>
         </tr>
         @foreach ($meeting->tasks as $item)
         <tr>
            <td  >{{date('d-m-Y',strtotime($item->when))}}</td>
            <td  >{{$item->participant->name}}</td>
            

            <td  colspan="4" >{{$item->what}}</td>
            <td @if ($item->status == 1) style="background-color:rgb(61, 175, 61)" @endif @if ($item->status == 0) style="background-color:rgb(213, 41, 41)" @endif @if ($item->status == 2) style="background-color:rgb(216, 232, 39)" @endif @if ($item->status == 3) style="background-color:rgb(10, 174, 239)" @endif   >
                @if ($item->status == 1) 
                    {{__('text.done')}} 
                @endif 
                @if($item->status == 0) 
                    {{__('text.not_done')}} 
                @endif 
                @if($item->status == 2) 
                    {{__('text.waiting_for_approval')}}
                @endif
                @if($item->status == 3) 
                {{__('text.done')}} ({{__('text.out_of_date')}})
            @endif
            </td>
           
         </tr>
         @endforeach --}}

    

    </table>
   
    






</div>



 
</body>
</html>
