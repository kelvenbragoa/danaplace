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

let retrievedData =ref([]);
let loadingButtonSubmit =ref([false]);
let loadingDiv =ref([true]);
let loading =ref([false]);
const toastr = useToastr();
const router = useRouter();
let self = this;
let currentvalue = ref([]);
const schema = yup.object({
//   name: yup.string().required(),
//   code: yup.string().required(),
});







const getData = () => {
  axios.get(`/generalinspections/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.generalinspection;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/generalinspections/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/admin/generalinspections' });
    toastr.success('Inspeção editada com sucesso');

  }).catch((error)=>{

    loadingButtonSubmit.value = false;
    toastr.error('Erro ao adicionar');
    if(error.response.data.errors){
      actions.setErrors(error.response.data.errors);
    }
  }).finally(()=>{
    loadingButtonSubmit.value = false;
  })
};




onMounted(()=>{
  
  getData();

})
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Inspeção </h1>
        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Formulário criação das Inspeções do sistema.</h5>
                                        <h5 class="card-title">Ao submeter o Inspeção, o equipamento estará indisponível até o Inspeção for terminado.</h5>

                                        <router-link to="/admin/generalinspections" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 
                                       
								    </div>
                                    
                                    <div class="card-body">
                                        
                                            <Form @submit="editFunction" :validation-schema="schema" v-slot="{ errors }"> 

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Equipamentos/Ativos:</label>
                                                        <span class="form-control">{{ retrievedData.equipment.name }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Clientes:</label>
                                                        <span class="form-control">{{ retrievedData.equipment.destination.name }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Area:</label>
                                                        <span class="form-control">{{ retrievedData.equipment.area.name }}</span>
													</div>
												</div>

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="equipment_id">Tipo de Equipamento:</label>
                                                        <span class="form-control">{{ retrievedData.equipment.type_equipment.name }}</span>
													</div>
												</div>

   

                                               

                                                <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="opened_at">Aberto às:</label>
														<Field type="datetime-local" class="form-control" :class="{'is-invalid':errors.opened_at}" v-model="retrievedData.opened_at" name="opened_at" id="opened_at" placeholder="Aberto às" required/>
                                                        <!-- <span class="form-control">{{ moment(currentDate).format('DD-MM-YYYY H:mm') }}</span> -->
                                                        <span class="invalid-feedback">{{ errors.opened_at }}</span>
													</div>
												</div>
                                                
                                              

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="total_hours">Hodômetro/Horímetro:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.total_hours}" v-model="retrievedData.total_hours"  name="total_hours" id="total_hours" placeholder=""/>
                                                            <span class="invalid-feedback">{{ errors.total_hours }}</span>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="is_operational">Operacional:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.is_operational}" v-model="retrievedData.is_operational" name="is_operational" id="is_operational" aria-describedby="is_operational">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1" >Sim</option>
                                                                <option value="0" >Não</option>
                                                            </Field>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="interior">Interior:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.interior}" v-model="retrievedData.interior" name="interior" id="interior" aria-describedby="interior">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="interior_description">Interior Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.interior_description}" v-model="retrievedData.interior_description" name="interior_description" id="interior_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="seats">Seats:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.seats}" v-model="retrievedData.seats" name="seats" id="seats" aria-describedby="seats">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="seats_description">Seats Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.seats_description}" v-model="retrievedData.seats_description" name="seats_description" id="seats_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="interior_trim_roof_lining_carpet">Interior Trim Roof Lining Carpet:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.interior_trim_roof_lining_carpet}" v-model="retrievedData.interior_trim_roof_lining_carpet" name="interior_trim_roof_lining_carpet" id="interior_trim_roof_lining_carpet" aria-describedby="interior_trim_roof_lining_carpet">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="interior_trim_roof_lining_carpet_description">iInterior Trim Roof Lining Carpet Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.interior_trim_roof_lining_carpet_description}" v-model="retrievedData.interior_trim_roof_lining_carpet_description" name="interior_trim_roof_lining_carpet_description" id="interior_trim_roof_lining_carpet_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="dashboard_cluster">Dashboard Cluster:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.dashboard_cluster}" v-model="retrievedData.dashboard_cluster" name="dashboard_cluster" id="dashboard_cluster" aria-describedby="dashboard_cluster">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="dashboard_cluster_description">Dashboard Cluster Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.dashboard_cluster_description}" v-model="retrievedData.dashboard_cluster_description" name="dashboard_cluster_description" id="dashboard_cluster_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="interior_control_unitis">Interior Control Unitis:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.interior_control_unitis}" v-model="retrievedData.interior_control_unitis" name="interior_control_unitis" id="interior_control_unitis" aria-describedby="interior_control_unitis">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="interior_control_unitis_description">Interior Control Unitis Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.interior_control_unitis_description}" v-model="retrievedData.interior_control_unitis_description" name="interior_control_unitis_description" id="interior_control_unitis_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="air_condition">Air Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.air_condition}" v-model="retrievedData.air_condition" name="air_condition" id="air_condition" aria-describedby="air_condition">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="air_condition_description">Air Condition Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.air_condition_description}" v-model="retrievedData.air_condition_description" name="air_condition_description" id="air_condition_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="eletric_windows">Eletric Windows:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.eletric_windows}" v-model="retrievedData.eletric_windows" name="eletric_windows" id="eletric_windows" aria-describedby="eletric_windows">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="eletric_windows_description">Eletric Windows Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.eletric_windows_description}" v-model="retrievedData.eletric_windows_description" name="eletric_windows_description" id="eletric_windows_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="eletric_sunroof">Eletric Sunroof:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.eletric_sunroof}" v-model="retrievedData.eletric_sunroof" name="eletric_sunroof" id="eletric_sunroof" aria-describedby="eletric_sunroof">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="eletric_sunroof_description">Eletric Sunroof Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.eletric_sunroof_description}" v-model="retrievedData.eletric_sunroof_description" name="eletric_sunroof_description" id="eletric_sunroof_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="seat_heaters">Seat Heaters:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.seat_heaters}" v-model="retrievedData.seat_heaters" name="seat_heaters" id="seat_heaters" aria-describedby="seat_heaters">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="seat_heaters_description">Seat Heaters Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.seat_heaters_description}" v-model="retrievedData.seat_heaters_description" name="seat_heaters_description" id="seat_heaters_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="rims">Rims:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.rims}" v-model="retrievedData.rims" name="rims" id="rims" aria-describedby="rims">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="rims_description">Rims Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.rims_description}" v-model="retrievedData.rims_description" name="rims_description" id="rims_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="mechanical_doors">Mechanical Doors:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.mechanical_doors}" v-model="retrievedData.mechanical_doors" name="mechanical_doors" id="mechanical_doors" aria-describedby="mechanical_doors">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="mechanical_doors_description">Mechanical Doors Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.mechanical_doors_description}" v-model="retrievedData.mechanical_doors_description" name="mechanical_doors_description" id="mechanical_doors_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="vehicle_body">Vehicle Body:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.vehicle_body}" v-model="retrievedData.vehicle_body" name="vehicle_body" id="vehicle_body" aria-describedby="vehicle_body">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="vehicle_body_description">Vehicle Body Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.vehicle_body_description}" v-model="retrievedData.vehicle_body_description" name="vehicle_body_description" id="vehicle_body_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="windows">Windows:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.windows}" v-model="retrievedData.windows" name="windows" id="windows" aria-describedby="windows">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="windows_description">Windows Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.windows_description}" v-model="retrievedData.windows_description" name="windows_description" id="windows_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="hang_on_parts">Hang on Parts:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.hang_on_parts}" v-model="retrievedData.hang_on_parts" name="hang_on_parts" id="hang_on_parts" aria-describedby="hang_on_parts">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="hang_on_parts_description">Hang on Parts Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.hang_on_parts_description}" v-model="retrievedData.hang_on_parts_description" name="hang_on_parts_description" id="hang_on_parts_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="spare_wheel">Spare Wheel:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.spare_wheel}" v-model="retrievedData.spare_wheel" name="spare_wheel" id="spare_wheel" aria-describedby="spare_wheel">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="spare_wheel_description">Spare Wheel Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.spare_wheel_description}" v-model="retrievedData.spare_wheel_description" name="spare_wheel_description" id="spare_wheel_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="tires">Tires:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.tires}" v-model="retrievedData.tires" name="tires" id="tires" aria-describedby="tires">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="tires_description">Tires Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.tires_description}" v-model="retrievedData.tires_description" name="tires_description" id="tires_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="engine_oil">Engine Oil:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.engine_oil}" v-model="retrievedData.engine_oil" name="engine_oil" id="engine_oil" aria-describedby="engine_oil">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="engine_oil_description">Engine Oil Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.engine_oil_description}" v-model="retrievedData.engine_oil_description" name="engine_oil_description" id="engine_oil_description" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="engine_cooling_system">Engine Cooling System:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.engine_cooling_system}" v-model="retrievedData.engine_cooling_system" name="engine_cooling_system" id="engine_cooling_system" aria-describedby="engine_cooling_system">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="engine_cooling_system_description">Engine Cooling System Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.engine_cooling_system_description}" v-model="retrievedData.engine_cooling_system_description" name="engine_cooling_system_description" id="engine_cooling_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="oil_loss_engine">Oil Loss Engine:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.oil_loss_engine}" v-model="retrievedData.oil_loss_engine" name="oil_loss_engine" id="oil_loss_engine" aria-describedby="oil_loss_engine">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="oil_loss_engine_description">Oil Loss Engine Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.oil_loss_engine_description}" v-model="retrievedData.oil_loss_engine_description" name="oil_loss_engine_description" id="oil_loss_engine_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="oil_loss_gear_box">Oil Loss Gear Box:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.oil_loss_gear_box}" v-model="retrievedData.oil_loss_gear_box" name="oil_loss_gear_box" id="oil_loss_gear_box" aria-describedby="oil_loss_gear_box">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="oil_loss_gear_box_description">Oil Loss Gear Box Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.oil_loss_gear_box_description}" v-model="retrievedData.oil_loss_gear_box_description" name="oil_loss_gear_box_description" id="oil_loss_gear_box_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="exhaust_system">Exhaust System:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.exhaust_system}" v-model="retrievedData.exhaust_system" name="exhaust_system" id="exhaust_system" aria-describedby="exhaust_system">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="exhaust_system_description">Exhaust System Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.exhaust_system_description}" v-model="retrievedData.exhaust_system_description" name="exhaust_system_description" id="exhaust_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="gearshift">Gear Shift:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.gearshift}" v-model="retrievedData.gearshift" name="gearshift" id="gearshift" aria-describedby="gearshift">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="gearshift_description">Gear Shift Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.gearshift_description}" v-model="retrievedData.gearshift_description" name="gearshift_description" id="gearshift_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="noise_levels_engine">Noise Levels Engine:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.noise_levels_engine}" v-model="retrievedData.noise_levels_engine" name="noise_levels_engine" id="noise_levels_engine" aria-describedby="noise_levels_engine">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="noise_levels_engine_description">Noise Levels Engine Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.noise_levels_engine_description}" v-model="retrievedData.noise_levels_engine_description" name="noise_levels_engine_description" id="noise_levels_engine_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="noise_levels_transmissions">Noise Levels Transmissions:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.noise_levels_transmissions}" v-model="retrievedData.noise_levels_transmissions" name="noise_levels_transmissions" id="noise_levels_transmissions" aria-describedby="noise_levels_transmissions">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="noise_levels_transmissions_description">Noise Levels Transmissions Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.noise_levels_transmissions_description}" v-model="retrievedData.noise_levels_transmissions_description" name="noise_levels_transmissions_description" id="noise_levels_transmissions_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="noise_levels_axles">Noise Levels Axles:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.noise_levels_axles}" v-model="retrievedData.noise_levels_axles" name="noise_levels_axles" id="noise_levels_axles" aria-describedby="noise_levels_axles">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="noise_levels_axles_description">Noise Levels Axles Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.noise_levels_axles_description}" v-model="retrievedData.noise_levels_axles_description" name="noise_levels_axles_description" id="noise_levels_axles_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="engine">Engine:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.engine}" v-model="retrievedData.engine" name="engine" id="engine" aria-describedby="engine">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="engine_description">Engine Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.engine_description}" v-model="retrievedData.engine_description" name="engine_description" id="engine_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="gearbox">GearBox:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.gearbox}" v-model="retrievedData.gearbox" name="gearbox" id="gearbox" aria-describedby="gearbox">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="gearbox_description">GearBox Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.gearbox_description}" v-model="retrievedData.gearbox_description" name="gearbox_description" id="gearbox_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="drivetrain">Drive Train:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.drivetrain}" v-model="retrievedData.drivetrain" name="drivetrain" id="drivetrain" aria-describedby="drivetrain">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="drivetrain_description">Drive Train Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.drivetrain_description}" v-model="retrievedData.drivetrain_description" name="drivetrain_description" id="drivetrain_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="brake_fluid">Brake Fluid:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.brake_fluid}" v-model="retrievedData.brake_fluid" name="brake_fluid" id="brake_fluid" aria-describedby="brake_fluid">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="brake_fluid_description">Brake Fluid Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.brake_fluid_description}" v-model="retrievedData.brake_fluid_description" name="brake_fluid_description" id="brake_fluid_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="brakes">Brakes:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.brakes}" v-model="retrievedData.brakes" name="brakes" id="brakes" aria-describedby="brakes">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="brakes_description">Brakes Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.brakes_description}" v-model="retrievedData.brakes_description" name="brakes_description" id="brakes_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="brake_system">Brake System:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.brake_system}" v-model="retrievedData.brake_system" name="brake_system" id="brake_system" aria-describedby="brake_system">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="brake_system_description">Brake System Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.brake_system_description}" v-model="retrievedData.brake_system_description" name="brake_system_description" id="brake_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="vehicle_undercarriage">Vehicle Undercarriage:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.vehicle_undercarriage}" v-model="retrievedData.vehicle_undercarriage" name="vehicle_undercarriage" id="vehicle_undercarriage" aria-describedby="vehicle_undercarriage">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="vehicle_undercarriage_description">Vehicle Undercarriage Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.vehicle_undercarriage_description}" v-model="retrievedData.vehicle_undercarriage_description" name="vehicle_undercarriage_description" id="vehicle_undercarriage_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="axles_suspension">Axles Suspension:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.axles_suspension}" v-model="retrievedData.axles_suspension" name="axles_suspension" id="axles_suspension" aria-describedby="axles_suspension">
                                                                <option value="" selected disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="axles_suspension_description">Axles Suspension Inspection Result:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.axles_suspension_description}" v-model="retrievedData.axles_suspension_description" name="axles_suspension_description" id="axles_suspension_description" placeholder=""/>

                                                        </div>
												    </div>









                                                    <div class="row">
                                                        <label class="form-label" for="front_left">Front Brake Test Result:</label>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_left">Front Left Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_left}" v-model="retrievedData.front_left" name="front_left" id="front_left" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_right">Front Right Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_right}" v-model="retrievedData.front_right" name="front_right" id="front_right" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_axle_weight">Front Axle Weight Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_axle_weight}" v-model="retrievedData.front_axle_weight" name="front_axle_weight" id="front_axle_weight" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_deceleration">Front Deceleration Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_deceleration}" v-model="retrievedData.front_deceleration" name="front_deceleration" id="front_deceleration" placeholder=""/>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <label class="form-label" for="rear_left">Rear Brake Test Result:</label>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_left">Rear Left Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_left}" v-model="retrievedData.rear_left" name="rear_left" id="rear_left" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_right">Rear Right Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_right}" v-model="retrievedData.rear_right" name="rear_right" id="rear_right" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_axle_weight">Rear Axle Weight Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_axle_weight}" v-model="retrievedData.rear_axle_weight" name="rear_axle_weight" id="rear_axle_weight" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_deceleration">Rear Deceleration Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_deceleration}" v-model="retrievedData.rear_deceleration" name="rear_deceleration" id="rear_deceleration" placeholder=""/>
                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <label class="form-label" for="emergency_left">Emergency Brake Test Result:</label>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="emergency_left">Emergency Left Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.emergency_left}" v-model="retrievedData.emergency_left" name="emergency_left" id="emergency_left" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="emergency_right">Emergency Right Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.emergency_right}" v-model="retrievedData.emergency_right" name="emergency_right" id="emergency_right" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="emergency_axle_weight">Emergency Axle Weight Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.emergency_axle_weight}" v-model="retrievedData.emergency_axle_weight" name="emergency_axle_weight" id="emergency_axle_weight" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="emergency_deceleration">Emergency Deceleration Result:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.emergency_deceleration}" v-model="retrievedData.emergency_deceleration" name="emergency_deceleration" id="emergency_deceleration" placeholder=""/>
                                                        </div>
												    </div>


                                                    <div class="row">
                                                        <label class="form-label" for="front_left_size">Tyre Specification & Measurement:</label>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_left_size">Front Left Size:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_left_size}" v-model="retrievedData.front_left_size" name="front_left_size" id="front_left_size" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_left_load">Front Left Load:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_left_load}" v-model="retrievedData.front_left_load" name="front_left_load" id="front_left_load" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_left_manufacture">Front Left Manufacture:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_left_manufacture}" v-model="retrievedData.front_left_manufacture" name="front_left_manufacture" id="front_left_manufacture" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_left_model">Front Left Model:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_left_model}" v-model="retrievedData.front_left_model" name="front_left_model" id="front_left_model" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_left_type">Front Left Type:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_left_type}" v-model="retrievedData.front_left_type" name="front_left_type" id="front_left_type" placeholder=""/>
                                                        </div>
                                                        <!-- <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_left_date">Front Left Date:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_left_date}" v-model="retrievedData.front_left_date" name="front_left_date" id="front_left_date" placeholder=""/>
                                                        </div> -->
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_left_thread_depth">Front Left Thread Depth:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_left_thread_depth}" v-model="retrievedData.front_left_thread_depth" name="front_left_thread_depth" id="front_left_thread_depth" placeholder=""/>
                                                        </div>
												    </div>



                                                    <div class="row">
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_right_size">Front right Size:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_right_size}" v-model="retrievedData.front_right_size" name="front_right_size" id="front_right_size" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_right_load">Front Right Load:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_right_load}" v-model="retrievedData.front_right_load" name="front_right_load" id="front_right_load" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_right_manufacture">Front right Manufacture:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_right_manufacture}" v-model="retrievedData.front_right_manufacture" name="front_right_manufacture" id="front_right_manufacture" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_right_model">Front right Model:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_right_model}" v-model="retrievedData.front_right_model" name="front_right_model" id="front_right_model" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_right_type">Front right Type:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_right_type}" v-model="retrievedData.front_right_type" name="front_right_type" id="front_right_type" placeholder=""/>
                                                        </div>
                                                        <!-- <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_right_date">Front right Date:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_right_date}" v-model="retrievedData.front_right_date" name="front_right_date" id="front_right_date" placeholder=""/>
                                                        </div> -->
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="front_right_thread_depth">Front right Thread Depth:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.front_right_thread_depth}" v-model="retrievedData.front_right_thread_depth" name="front_right_thread_depth" id="front_right_thread_depth" placeholder=""/>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_left_size">Rear Left Size:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_left_size}" v-model="retrievedData.rear_left_size" name="rear_left_size" id="rear_left_size" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_left_load">Rear Left Load:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_left_load}" v-model="retrievedData.rear_left_load" name="rear_left_load" id="rear_left_load" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_left_manufacture">Rear Left Manufacture:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_left_manufacture}" v-model="retrievedData.rear_left_manufacture" name="rear_left_manufacture" id="rear_left_manufacture" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_left_model">Rear Left Model:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_left_model}" v-model="retrievedData.rear_left_model" name="rear_left_model" id="rear_left_model" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_left_type">Rear Left Type:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_left_type}" v-model="retrievedData.rear_left_type" name="rear_left_type" id="rear_left_type" placeholder=""/>
                                                        </div>
                                                        <!-- <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_left_date">Rear Left Date:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_left_date}" v-model="retrievedData.rear_left_date" name="rear_left_date" id="rear_left_date" placeholder=""/>
                                                        </div> -->
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_left_thread_depth">Rear Left Thread Depth:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_left_thread_depth}" v-model="retrievedData.rear_left_thread_depth" name="rear_left_thread_depth" id="rear_left_thread_depth" placeholder=""/>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_right_size">Rear Right Size:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_right_size}" v-model="retrievedData.rear_right_size" name="rear_right_size" id="rear_right_size" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_right_load">Rear Right Load:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_right_load}" v-model="retrievedData.rear_right_load" name="rear_right_load" id="rear_right_load" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_right_manufacture">Rear Right Manufacture:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_right_manufacture}" v-model="retrievedData.rear_right_manufacture" name="rear_right_manufacture" id="rear_right_manufacture" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_right_model">Rear Right Model:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_right_model}" v-model="retrievedData.rear_right_model" name="rear_right_model" id="rear_right_model" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_right_type">Rear Right Type:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_right_type}" v-model="retrievedData.rear_right_type" name="rear_right_type" id="rear_right_type" placeholder=""/>
                                                        </div>
                                                        <!-- <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_right_date">Rear Right Date:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_right_date}" v-model="retrievedData.rear_right_date" name="rear_right_date" id="rear_right_date" placeholder=""/>
                                                        </div> -->
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="rear_right_thread_depth">Rear Right Thread Depth:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.rear_right_thread_depth}" v-model="retrievedData.rear_right_thread_depth" name="rear_right_thread_depth" id="rear_right_thread_depth" placeholder=""/>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="spare_size">Spare Size:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.spare_size}" v-model="retrievedData.spare_size" name="spare_size" id="spare_size" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="spare_load">Spare Load:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.spare_load}" v-model="retrievedData.spare_load" name="spare_load" id="spare_load" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="spare_manufacture">Spare Manufacture:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.spare_manufacture}" v-model="retrievedData.spare_manufacture" name="spare_manufacture" id="spare_manufacture" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="spare_model">Spare Model:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.spare_model}" v-model="retrievedData.spare_model" name="spare_model" id="spare_model" placeholder=""/>
                                                        </div>
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="spare_type">Spare Type:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.spare_type}" v-model="retrievedData.spare_type" name="spare_type" id="spare_type" placeholder=""/>
                                                        </div>
                                                        <!-- <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="spare_date">Spare Date:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.spare_date}" v-model="retrievedData.spare_date" name="spare_date" id="spare_date" placeholder=""/>
                                                        </div> -->
                                                        <div class="mb-3 col-md-2">
                                                            <label class="form-label" for="spare_thread_depth">Spare Thread Depth:</label>
                                                            <Field type="number" class="form-control" :class="{'is-invalid':errors.spare_thread_depth}" v-model="retrievedData.spare_thread_depth" name="spare_thread_depth" id="spare_thread_depth" placeholder=""/>
                                                        </div>
												    </div>



         



























                                                

                                                   

                                                   

                                        


                                         

                                




                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="diagnostic">Diagnostic:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.diagnostic}" v-model="retrievedData.diagnostic" name="diagnostic" id="diagnostic" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="inspection_condition">Inspection Condition:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.inspection_condition}" v-model="retrievedData.inspection_condition" name="inspection_condition" id="inspection_condition" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="comments">Comments:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.comments}" v-model="retrievedData.comments" name="comments" id="comments" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="concluding_remarks">Concluding Remarks:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.concluding_remarks}" v-model="retrievedData.concluding_remarks" name="concluding_remarks" id="concluding_remarks" placeholder=""/>

                                                        </div>
												    </div>


                                                    <div class="row">
													<div class="mb-3 col-md-12">
														<label class="form-label" for="inspection_status_id">Estado da Inspeção:</label>
                                                        <Field as="select" class="form-control" :class="{'is-invalid':errors.inspection_status_id}" required v-model="retrievedData.inspection_status_id"  name="inspection_status_id" id="inspection_status_id" aria-describedby="inspection_status_id">
                                                            <option value="" disabled>Selecionar</option>
                                                            <option value="1">Programar</option>
                                                            <option value="2">Executar</option>
                                                        </Field>
                                                        <span class="invalid-feedback">{{ errors.inspection_status_id }}</span>
													</div>
												</div>
                                                    
                                              


                                                    <button type="submit" class="btn btn-primary" :disabled="loadingButtonSubmit == true">
                                                            <div v-if="loadingButtonSubmit == true" class="spinner-border spinner-border-sm" role="status"></div>
                                                            <span v-else>Submeter</span>
                                                        </button>
											</Form>

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