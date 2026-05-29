<script setup>

import {onMounted, ref, reactive,watch} from 'vue';
import axios from 'axios';
import {useToastr} from '../../../toastr';
import moment from 'moment'
import {debounce} from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import timeGridPlugin from '@fullcalendar/timegrid'
import { useRouter } from "vue-router";


const toastr = useToastr();
const searchQuery = ref(null);
const loadingDiv = ref(true);
const modalCalendar = ref(false);

const total = ref(0)
const pendente = ref(0)
const aprovado = ref(0)
const materiaisentregue = ref(0)
const pagamentofeito = ref(0)
const foradeprazo = ref(0)
const processoconcluido = ref(0)
const materiaisemtransito = ref(0)
const aguardaaprovacao = ref(0)
const destinations = ref([])
const destinationfilter = ref(null)

const router = useRouter();


const openCalendarFunction = () => {
    $('#calendarModal').modal('show');


};

const closeCalendarModal = () => {
    $('#calendarModal').modal('hide');
};

const calendarOptions =  reactive({
        plugins: [ dayGridPlugin, interactionPlugin ,timeGridPlugin],
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events:"",
        initialView: 'dayGridMonth',
        height: '80vh',
        eventClick: function(info) {
            $('#calendarModal').modal('hide');
            router.push({ name: 'admin.quotation.show', params: { id: info.event.id } });
            info.el.style.borderColor = 'red';
        }
    
        
      })

const getEventList = async () => {
  axios.get('/calendarquotation')
       .then((response)=>{
        calendarOptions.events = response.data;
        // loadingDiv.value=false;
       })
}




const loadingButtonDelete = ref(false);
const quotationStatus = {'pendente':1,'aprovado':2,'materiaisentregue':3,'pagamentofeito':4,'foradeprazo':5,'processoconcluido':6,'materiaisemtransito':7,'aguardaaprovacao':8}
function formatCurrency(value) {
  if (isNaN(value) || value === null || value === undefined) {
    return '0,00';
  }
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value));
}


const retriviedData = ref({'data': []})

let dataIdBeingDeleted = ref(null);


 const getData = async (page = 1,status,destinationfilter) => {
  axios.get(`/quotation?page=${page}`,
      {
        params:{
          query: searchQuery.value,
          status:status,
          destination: destinationfilter
        }
      })
       .then((response)=>{
        retriviedData.value = response.data.quotation;

        pendente.value = response.data.pendente;
        aprovado.value = response.data.aprovado;
        materiaisentregue.value = response.data.materiaisentregue;

        pagamentofeito.value = response.data.pagamentofeito;
        foradeprazo.value = response.data.foradeprazo;
        processoconcluido.value = response.data.processoconcluido;

        materiaisemtransito.value = response.data.materiaisemtransito;
        aguardaaprovacao.value = response.data.aguardaaprovacao;

        total.value = response.data.total;
        destinations.value = response.data.destinations;

        loadingDiv.value=false;

        
       })

       
}

const confirmDeletion = (data) => {

dataIdBeingDeleted = data.id;

$('#deleteModal').modal('show');
// axios.post('/categories',values).then((response)=>{

//   categories.value.unshift(response.data);
//   $('#createCategory').modal('hide');
//   resetForm();
// })
};

const deleteData = () =>{

loadingButtonDelete.value= true;

axios.delete(`/quotation/${dataIdBeingDeleted}`)
.then(()=>{
 retriviedData.value.data = retriviedData.value.data.filter(data=>data.id !== dataIdBeingDeleted); 
 $('#deleteModal').modal('hide');

 toastr.success('Registro apagado com sucesso');

}).catch(()=>{
 toastr.error('Erro ao apagar');
 loadingButtonDelete.value= false;
 $('#deleteModal').modal('hide');
}).finally(()=>{
 loadingButtonDelete.value= false;
});
}

watch(searchQuery,debounce(()=>{
    getData();
},300));

onMounted(()=>{
    getEventList();
    getData();
    
})
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Cotações</h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Tabela das Cotações do sistema. {{ retriviedData.total }} registros encontrados.</h5>
                                        <h6 class="card-subtitle text-muted">Para procurar, digite na caixa de procura</h6>

                                        <router-link to="/admin/quotation/create" class="btn btn-pill btn-primary m-3"><vue-feather type="plus"></vue-feather>Adicionar</router-link> 
                                        <button @click="openCalendarFunction" class="btn btn-pill btn-primary m-3"><vue-feather type="calendar"></vue-feather>Calendário</button> 

                                        <br>

                                        <form class="d-none d-sm-inline-block m-3">
                                            <div class="input-group input-group-navbar">
                                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar nome..." aria-label="Search">
                                            </div>
                                        </form>
                                        <form class="d-none d-sm-inline-block m-3">
                                            <div class="input-group input-group-navbar">
                                                <select name="date_range" @change="getData(1,null,destinationfilter)" v-model="destinationfilter" class="form-control">
                                                        <option selected disabled>Select Destination</option>  
                                                        <option :value="destination.id" v-for="destination in destinations" :key="destination.id">{{ destination.name }}</option>                          
                                                </select>                       
                                            </div>
                                        </form>
                                        <div class="btn-group ml-5 flex-wrap" role="group" aria-label="Basic example">
                                            <button @click="getData(1)" class="btn btn-secondary">
                                                <span class="mr-1">Todos</span>
                                                <span  class="badge bg-ligth">{{ total }} </span>
                                            </button>
                                            <button @click="getData(1,quotationStatus.pendente)" class="btn btn-ligth">
                                                <span class="mr-1">Pendente</span>
                                                <span  class="badge bg-success">{{pendente}}</span>
                                            </button>
                                            <button @click="getData(1,quotationStatus.aprovado)" class="btn btn-light">
                                                <span class="mr-1">Aprovado</span>
                                                <span  class="badge bg-warning">{{aprovado}}</span>
                                            </button>
                                            <button @click="getData(1,quotationStatus.materiaisentregue)" class="btn btn-light">
                                                <span class="mr-1">Materiais Entregue</span>
                                                <span  class="badge bg-danger">{{materiaisentregue}}</span>
                                            </button>
                                            <button @click="getData(1,quotationStatus.pagamentofeito)" class="btn btn-light">
                                                <span class="mr-1">Pagamento Feito</span>
                                                <span  class="badge bg-primary">{{pagamentofeito}}</span>
                                            </button>
                                            <button @click="getData(1,quotationStatus.foradeprazo)" class="btn btn-light">
                                                <span class="mr-1">Fora de Prazo</span>
                                                <span  class="badge bg-info">{{foradeprazo}}</span>
                                            </button>
                                            <button @click="getData(1,quotationStatus.processoconcluido)" class="btn btn-light">
                                                <span class="mr-1">Processo Concluido</span>
                                                <span  class="badge bg-info">{{processoconcluido}}</span>
                                            </button>
                                            <button @click="getData(1,quotationStatus.materiaisemtransito)" class="btn btn-light">
                                                <span class="mr-1">Materiais Em Transito</span>
                                                <span  class="badge bg-info">{{materiaisemtransito}}</span>
                                            </button>
                                            <button @click="getData(1,quotationStatus.aguardaaprovacao)" class="btn btn-light">
                                                <span class="mr-1">Aguarda Aprovação</span>
                                                <span  class="badge bg-info">{{aguardaaprovacao}}</span>
                                            </button>
                                        </div>
                                        <br>
                                        
								    </div>
                                    
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Clientes</th>
                                                        <th>Total</th>
                                                        <th>Criado em</th>
                                                        <th>Expira em</th>
                                                        <th>Estado</th>
                                                        <th>Obs</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody v-if="retriviedData.data.length > 0">
                                                    <tr  v-for="(actualData,index) in retriviedData.data" :key="actualData.id">
                                                        <td>#{{ index + 1 }}</td>
                                                        <td>{{actualData.destination_id != 0 ? actualData.destination.name : actualData.company_name}}</td>
                                                        <td>{{ formatCurrency(actualData.total_amount)}} {{actualData.coin.name}}</td>
                                                        <td>{{ moment(actualData.created_at).format('DD-MM-YYYY') }}</td>
                                                        
                                                        <td>
                                                            {{
                                                                actualData?.expires_date
                                                                ? moment(actualData.expires_date).isBefore(moment())
                                                                    ? `Expirado há ${Math.abs(moment().diff(moment(actualData.expires_date), 'days'))} dias`
                                                                    : `Expira em ${moment(actualData.expires_date).diff(moment(), 'days')} dias`
                                                                : 'Data Inválida'
                                                            }}
                                                        </td>

                                                        <!-- <td>{{ moment(actualData.expires_date).format('DD-MM-YYYY') }}</td> -->
                                                        <td>
                                                            <span v-if="actualData.status_quotation_id == 1" class="badge bg-warning">
                                                                {{ actualData.status.name}}
                                                            </span>
                                                            <span v-if="actualData.status_quotation_id == 2" class="badge bg-primary">
                                                                {{ actualData.status.name}}
                                                            </span>
                                                            <span v-if="actualData.status_quotation_id == 3" class="badge bg-info">
                                                                {{ actualData.status.name}}
                                                            </span>
                                                            <span v-if="actualData.status_quotation_id == 4" class="badge bg-success">
                                                                {{ actualData.status.name}}
                                                            </span>
                                                            <span v-if="actualData.status_quotation_id == 5" class="badge bg-danger">
                                                                {{ actualData.status.name}}
                                                            </span>
                                                            <span v-if="actualData.status_quotation_id == 6" class="badge bg-primary">
                                                                {{ actualData.status.name}}
                                                            </span>
                                                            <span v-if="actualData.status_quotation_id == 7" class="badge bg-primary">
                                                                {{ actualData.status.name}}
                                                            </span>
                                                            <span v-if="actualData.status_quotation_id == 8" class="badge bg-warning">
                                                                {{ actualData.status.name}}
                                                            </span>
                                                          
                                                        </td>
                                                        <td>{{ actualData.obs}}</td>

                                                        <td>
                                                            <router-link :to="'/admin/quotation/'+actualData.id+'/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                                            <router-link :to="'/admin/quotation/'+actualData.id"><vue-feather type="eye"></vue-feather></router-link> 
                                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tbody v-else>
                                                    <tr>
                                                    <td colspan="7" align="center">Nenhum resultado encontrado</td>
                                                    </tr>
                                                </tbody>
                                            </table>
								        </div>
                                    </div>
                                   
								
                                </div>
                                <Bootstrap4Pagination :data="retriviedData" @pagination-change-page="getData"/>
                            </div>
                        </div>

    <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Deseja mesmo eliminar este item.</h5>
          
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
            Ao apagar este item, irá apagar todos os registros relacionados a ele.
        </div>
        <div class="modal-footer">
          
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button @click.prevent="deleteData" type="button" class="btn btn-danger" :disabled="loadingButtonDelete">
                    <div v-if="loadingButtonDelete" class="spinner-border spinner-border-sm" role="status"></div>
                    <span v-else>Apagar registro</span>
                </button>
         
          
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="calendarModal" tabindex="-1" role="dialog" aria-labelledby="calendarModalTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="calendarModalTitle">Calendário</h5>
            <button type="button" @click="closeCalendarModal" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <FullCalendar :options="calendarOptions" />
          </div>
          <div class="modal-footer">
            <button type="button" @click="closeCalendarModal" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>

       
                        
    </div>

    <div v-else>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="sr-only"></span>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div> 
        </div>
    </div>




</template>

<style scoped>

/* Define a altura máxima para o modal e habilita a rolagem se necessário */
.modal-dialog {
  max-height: 90vh;  /* Define a altura máxima do modal, 90% da altura da tela */
  display: flex;
  justify-content: center;  /* Garante que o modal fique centralizado */
  align-items: center;      /* Centraliza o modal verticalmente */
}

.modal-body {
  max-height: 80vh;  /* Define a altura máxima para o corpo do modal */
  overflow-y: auto;  /* Adiciona rolagem se o conteúdo for maior que a altura máxima */
}
</style>