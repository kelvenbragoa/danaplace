<script setup>

import axios from 'axios';
import { ref, onMounted, reactive, defineEmits, defineComponent,watch } from "vue";
import moment from 'moment'
import {useToastr} from '../../../toastr';
import {debounce} from 'lodash';
import {Form, Field} from 'vee-validate';
import { useRouter} from "vue-router";
import * as yup from 'yup';
import VueFeather from 'vue-feather';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueHtmlToPaper from "vue-html-to-paper";
import { usePaperizer } from 'paperizer'


let retrievedData =ref([]);
let loadingSubmit =ref([true]);
let loadingDiv =ref([true]);
const loadingprint = ref(false);
const router = useRouter();
let self = this;
let requeststock = ref([])
let requesttool = ref([]);
let requesttechnician = ref([])
const toastr = useToastr();
const { paperize } = usePaperizer('print-me',{
    styles: [
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
  ]
})


const downloadMcscr = () =>{
    // axios.get(`/download-mcscr/+${router.currentRoute.value.params.id}`)
    //    .then((response)=>{
    //     console.log(response.data)
    //    }).catch(()=>{
        
    //    })
    loadingprint.value = true;
        // window.print();
        // router.push('/admin/mcscr').catch(()=>{})
        // this.$htmlToPaper('printMe');
        paperize()
    loadingprint.value = false;

    // axios({
    //     url:`/download-mcscr/+${router.currentRoute.value.params.id}`,
    //     responseType:'blob'
    // }).then((response)=>{
    //     const url = window.URL.createObjectURL(new Blob([response.data]));
    //     const link = document.createElement('a');
    //     link.href = url;
    //     link.setAttribute('download', 'mcscr-nr-'+retrievedData.value.id+'.pdf');
    //     document.body.appendChild(link);
    //     link.click();
    //     loadingprint.value = false;
    //     toastr.success('Documento baixado com sucesso');
    // }).catch((error)=>{
    
    //     loadingprint.value = false;
    //     toastr.error('Ocorreu um erro ao tentar baixar o documento. '+error.response.data.message);
       
    // }).finally(()=>{
    //     loadingprint.value = false;
    // })
}

const conditionStatus = (status)=>{
    switch(status) {
        case 1:
            return 'New';
        case 2:
            return 'Very Good Condition';
        case 3:
            return 'Good';
        case 4:
            return 'Works with Disabilities';
        case 5:
            return 'Works With Great Disabilities';
        default:
            return 'Other';
        
    }
}  
const conditionOperation = (status)=>{
    switch(status) {
        case 1:
            return 'Monitor and Follow Maintenance Plans';
        case 2:
            return 'Monitor and Follow Maintenance Plans';
        case 3:
            return 'Needs Minor Repairs (Tightenin)';
        case 4:
            return 'Requires Localized Repairs';
        case 5:
            return 'Needs Complete Repair';
        default:
            return 'Other';
        
    }
}





const getData = () => {
  axios.get(`/inspections/+${router.currentRoute.value.params.id}`)
       .then((response)=>{
        loadingDiv.value=false;
        retrievedData.value = response.data.inspection;
        // requeststock.value = response.data.requeststock
        // requesttool.value = response.data.requesttool
        // requesttechnician.value = response.data.requesttechnician
       }).catch(()=>{
        loadingDiv.value=false;
       })
}




onMounted(()=>{
  
  getData();
})
</script>

<template>
    <div v-if="!loadingDiv">

        <h1 class="h3 mb-3">Inspeção</h1>
        
        <div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body m-sm-3 m-md-5 border">
									
                                    <div class="mb-4">
                                        <router-link to="/admin/inspections" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link>
                                    </div> 
                                    <div id="print-me">
                                        <div class="row mb-4">
                                            <div class="col">
                                                <div class="col-2">
                                                    <img src="/files/img/sys/companylogo.png" class="img-fluid w-100" alt="M+D" width="450" height="450">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col">
                                                <div class="text-muted">Area</div>
                                                <strong> {{ retrievedData.area.name ?? 'N/A' }} </strong>
                                                <p> {{ retrievedData.area.company_name ?? 'N/A' }} 
                                                    <br> 
                                                    {{ retrievedData.area.company_address ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.area.company_nuit ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.area.province.name ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.area.company_mobile ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.area.company_email ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="col">
                                                <br>
                                            </div>
                                            <div class="col text-right">
                                                <div class="text-muted">Customer</div>
                                                <strong> {{ retrievedData.destination.name ?? 'N/A' }} </strong>
                                                <p> {{ retrievedData.destination.company_name ?? 'N/A' }} 
                                                    <br> 
                                                    {{ retrievedData.destination.company_address ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.destination.company_nuit ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.destination.province.name ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.destination.company_mobile ?? 'N/A' }}
                                                    <br>
                                                    {{ retrievedData.destination.company_email ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                        <!-- <div class="row mb-4">
                                            <p><strong>Inspection Nº: </strong>#{{retrievedData.id}}  </p>
                                        </div>
                                        <hr class="my-4" /> -->


                                            <div class="text-muted mb-4">Equipment Detail</div>

                                                <div class="row mb-2 border">
                                                    <div class="col border-right">
                                                        <div class="text-muted">Equipment</div>
                                                        <strong>{{ retrievedData.equipment.name ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="col border-right">
                                                        <div class="text-muted">Fleet</div>
                                                        <strong>{{ retrievedData.equipment.type_equipment.name ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="col border-right">
                                                        <div class="text-muted">Ref</div>
                                                        <strong>{{ retrievedData.equipment.ref ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="col border-right">
                                                        <div class="text-muted">Make</div>
                                                        <strong>{{ retrievedData.equipment.make ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="col border-right">
                                                        <div class="text-muted">Model</div>
                                                        <strong>{{ retrievedData.equipment.model ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="col border-right">
                                                        <div class="text-muted">Km/Hours</div>
                                                        <strong>{{ retrievedData.total_hours ?? 'N/A' }}</strong>
                                                    </div>
                                                </div>

                                        <hr class="" />
                                        <div class="text-muted">Details</div>

                                            <!-- <p>
                                                                <strong>Status: </strong>  
                                                            

                                                                <span v-if="retrievedData.inspection_status_id == 1" class="badge bg-warning">
                                                                    {{ retrievedData.inspection_status.name}}
                                                                </span>
                                                                <span v-if="retrievedData.inspection_status_id == 2" class="badge bg-success">
                                                                    {{ retrievedData.inspection_status.name}}
                                                                </span>
                                                               
                                                </p> -->
                                               
                                              

                                                <div class="row mb-1 border">
                                                    <div class="col border-right">
                                                        <div class="text-muted">Status</div>
                                                        <span v-if="retrievedData.inspection_status_id == 1" class="badge bg-warning">
                                                                    {{ retrievedData.inspection_status.name}}
                                                                </span>
                                                                <span v-if="retrievedData.inspection_status_id == 2" class="badge bg-success">
                                                                    {{ retrievedData.inspection_status.name}}
                                                                </span>
                                                    </div>
                                                    <div class="col border-right">
                                                        <div class="text-muted">Inspection nº</div>
                                                        <strong>#{{retrievedData.id}}</strong>
                                                    </div>
                                                    
                                                    <div class="col text-md-right border-right">
                                                        <div class="text-muted">Opened at:</div>
                                                        <strong>{{ moment(retrievedData.opened_at).format('DD-MM-YYYY H:mm') }}</strong>
                                                    </div>
                                                   
                                                    
                                                    <div class="col text-md-right border-right">
                                                        <div class="text-muted">Closed at:</div>
                                                        <strong>{{ retrievedData.closed_at == null ? 'N/A' : moment(retrievedData.closed_at).format('DD-MM-YYYY H:mm') }}
                                                           
                                                        </strong>
                                                    </div>
                                                </div>

                                        <hr class="my-4" />
                                        <div class="text-muted mb-4">Component Condition</div>
                                        <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Items</th>
                                                            <th>Verification</th>
                                                            <th>Condition</th>
                                                            <th>Description of the most relevant defects</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>1</td>
                                                            <td>Engine</td>
                                                            <td>{{ conditionStatus(retrievedData.engine_condition) }}</td>
                                                            <td>{{ retrievedData.engine_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>2</td>
                                                            <td>Electrical System</td>
                                                            <td>{{ conditionStatus(retrievedData.eletrical_system_condition) }}</td>
                                                            <td>{{ retrievedData.eletrical_system_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>3</td>
                                                            <td>Transmission</td>
                                                            <td>{{ conditionStatus(retrievedData.transmission_condition) }}</td>
                                                            <td>{{ retrievedData.transmission_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>4</td>
                                                            <td>Control System</td>
                                                            <td>{{ conditionStatus(retrievedData.control_system_condition) }}</td>
                                                            <td>{{ retrievedData.control_system_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>5</td>
                                                            <td>Structure / Chassis</td>
                                                            <td>{{ conditionStatus(retrievedData.structure_condition) }}</td>
                                                            <td>{{ retrievedData.structure_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>6</td>
                                                            <td>Hydraulic System</td>
                                                            <td>{{ conditionStatus(retrievedData.hydraulic_system_condition) }}</td>
                                                            <td>{{ retrievedData.hydraulic_system_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>7</td>
                                                            <td>Pneumatic System</td>
                                                            <td>{{ conditionStatus(retrievedData.pneumatic_system_condition) }}</td>
                                                            <td>{{ retrievedData.pneumatic_system_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>8</td>
                                                            <td>Suspension</td>
                                                            <td>{{ conditionStatus(retrievedData.suspension_condition) }}</td>
                                                            <td>{{ retrievedData.suspension_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>9</td>
                                                            <td>Tyres</td>
                                                            <td>{{ conditionStatus(retrievedData.tyres_condition) }}</td>
                                                            <td>{{ retrievedData.tyres_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>10</td>
                                                            <td>Blade / Bucket / Winch / Spreader</td>
                                                            <td>{{ conditionStatus(retrievedData.blades_condition) }}</td>
                                                            <td>{{ retrievedData.blades_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>11</td>
                                                            <td>Cabin</td>
                                                            <td>{{ conditionStatus(retrievedData.cabin_condition) }}</td>
                                                            <td>{{ retrievedData.cabin_description }}</td>
                                                        </tr>

                                                        <tr>
                                                            <td>12</td>
                                                            <td>Others</td>
                                                            <td>{{ conditionStatus(retrievedData.others_condition) }}</td>
                                                            <td>{{ retrievedData.others_description }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>


                                           

                                        <hr class="my-4" />
                                        <!-- <div class="text-muted mb-4">Rating Criteria</div> -->
                                        <div class="mb-3 col-md-12">
                                                        <label class="form-label">Unit Condition:</label>
                                                        <span  class="form-control">{{ conditionStatus(retrievedData.rating_unit_condition) }} </span>
                                        </div>

                                        <div class="mb-3 col-md-12">
                                                        <label class="form-label">In Operation:</label>
                                                        <span  class="form-control">{{ conditionOperation(retrievedData.rating_in_operation) }} </span>
                                        </div>


                                       
                                        <hr class="my-4" />

                                        <!-- <div class="text-muted mb-4">Comments</div> -->
                                        
                                        <div class="mb-3 col-md-12">
                                                        <label class="form-label">Comments:</label>
                                                        <span  class="form-control">{{ retrievedData.comments }} </span>
                                        </div>


                                        <hr class="my-4" />

                                        <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Items</th>
                                                            <th>Recommendation</th>
                                                            
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>1</td>
                                                             <td>{{ retrievedData.recommendation_1 }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>2</td>
                                                             <td>{{ retrievedData.recommendation_2 }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>3</td>
                                                             <td>{{ retrievedData.recommendation_3 }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>4</td>
                                                             <td>{{ retrievedData.recommendation_4 }}</td>
                                                        </tr>

                                                    
                                                    </tbody>
                                                </table>



                                               
                                                <hr class="my-4" />

                                            
                                        <div class="row mb-4">
                                            <div class="col">
                                                <div class="text-muted">Signed by</div>
                                                <strong> Name: </strong> <span style="border-bottom: 1px solid black; padding-right: 20px">{{ retrievedData.opened_by_user.firstName+' '+retrievedData.opened_by_user.lastName }}</span> <br> <br>
                                                <strong> Signature: </strong><span style="border-bottom: 1px solid black; padding-right: 20px">{{ retrievedData.opened_by_user.firstName+' '+retrievedData.opened_by_user.lastName }}</span>
                                               
                                            </div>
                                            <div class="col">
                                                <br>
                                            </div>
                                            <div class="col text-right">
                                                <div class="text-muted">Signed by Client</div>
                                                <strong> Name: ______________________</strong> <br> <br>
                                                <strong> Signature: ______________________</strong>
                                            </div>
                                        </div>
                                                
                                                
                                    </div>

                                        <hr class="my-4" />


                                         



                                        <div class="text-center">
                                            <p class="text-sm">
                                                <strong>Observação:</strong> {{ retrievedData.first_observation ?? 'N/A' }}.
                                            </p>
									    </div>
                                </div>

                                <div class="text-center">
                                    <button @click="downloadMcscr" class="btn btn-primary" :disabled="loadingprint">
                                                <div v-if="loadingprint" class="spinner-border spinner-border-sm" role="status"></div>
                                                <span v-else>Print</span>
                                            </button>
                                            <div v-if="loadingprint" class="d-flex justify-content-center">
                                                Aguarde, Estamos gerando o seu documento...
                                            </div>
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