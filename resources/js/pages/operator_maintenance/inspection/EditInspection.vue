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
  axios.get(`/inspections/+${router.currentRoute.value.params.id}/edit`)
       .then((response)=>{

        loadingDiv.value=false;
        retrievedData.value = response.data.inspection;

       }).catch(()=>{

        loadingDiv.value=false;

       })
}

const editFunction = (values, actions) => {

  loadingButtonSubmit.value = true;
  axios.patch(`/inspections/${retrievedData.value.id}`,values).then((response)=>{

    // admins.value.unshift(response.data);
    // $('#createCategory').modal('hide');
    actions.resetForm();
    router.push({ path: '/operator/maintenance/inspections' });
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

                                        <router-link to="/operator/maintenance/inspections" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</router-link> 
                                       
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
                                                            <label class="form-label" for="engine_condition">Engine Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.engine_condition}" v-model="retrievedData.engine_condition" name="engine_condition" id="engine_condition" aria-describedby="engine_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="engine_description">Engine Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.engine_description}" v-model="retrievedData.engine_description" name="engine_description" id="engine_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="eletrical_system_condition">Eletrical System Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.eletrical_system_condition}" v-model="retrievedData.eletrical_system_condition" name="eletrical_system_condition" id="eletrical_system_condition" aria-describedby="eletrical_system_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="eletrical_system_description">Eletrical System Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.eletrical_system_description}" v-model="retrievedData.eletrical_system_description" name="eletrical_system_description" id="eletrical_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="transmission_condition">Transmission Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.transmission_condition}" v-model="retrievedData.transmission_condition" name="transmission_condition" id="transmission_condition" aria-describedby="transmission_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="transmission_description">Transmission Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.transmission_description}" v-model="retrievedData.transmission_description" name="transmission_description" id="transmission_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="control_system_condition">Control System Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.control_system_condition}" v-model="retrievedData.control_system_condition" name="control_system_condition" id="control_system_condition" aria-describedby="control_system_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="control_system_description">Control System Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.control_system_description}" v-model="retrievedData.control_system_description" name="control_system_description" id="control_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="structure_condition">Structure Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.structure_condition}" v-model="retrievedData.structure_condition"  name="structure_condition" id="structure_condition" aria-describedby="structure_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="structure_description">Structure Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.structure_description}" v-model="retrievedData.structure_description" name="structure_description" id="structure_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="hydraulic_system_condition">Hydraulic System Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.hydraulic_system_condition}" v-model="retrievedData.hydraulic_system_condition" name="hydraulic_system_condition" id="hydraulic_system_condition" aria-describedby="hydraulic_system_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="hydraulic_system_description">Hydraulic System Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.hydraulic_system_description}" v-model="retrievedData.hydraulic_system_description" name="hydraulic_system_description" id="hydraulic_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="pneumatic_system_condition">Pneumatic System Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.pneumatic_system_condition}" v-model="retrievedData.pneumatic_system_condition"  name="pneumatic_system_condition" id="pneumatic_system_condition" aria-describedby="pneumatic_system_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="pneumatic_system_description">Pneumatic System Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.pneumatic_system_description}" v-model="retrievedData.pneumatic_system_description" name="pneumatic_system_description" id="pneumatic_system_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="suspension_condition">Suspension Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.suspension_condition}" required v-model="retrievedData.suspension_condition" name="suspension_condition" id="suspension_condition" aria-describedby="suspension_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="suspension_description">Suspension Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.suspension_description}" v-model="retrievedData.suspension_description" name="suspension_description" id="suspension_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="tyres_condition">Tyres Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.tyres_condition}" v-model="retrievedData.tyres_condition" name="tyres_condition" id="tyres_condition" aria-describedby="tyres_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="tyres_description">Tyres Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.tyres_description}" v-model="retrievedData.tyres_description" name="tyres_description" id="tyres_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="blades_condition">Blades Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.blades_condition}" v-model="retrievedData.blades_condition" name="blades_condition" id="blades_condition" aria-describedby="blades_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="blades_description">Blades Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.blades_description}" v-model="retrievedData.blades_description" name="blades_description" id="blades_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="cabin_condition">Cabin Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.cabin_condition}" v-model="retrievedData.cabin_condition" name="cabin_condition" id="cabin_condition" aria-describedby="cabin_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="cabin_description">Cabin Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.cabin_description}" v-model="retrievedData.cabin_description" name="cabin_description" id="cabin_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-4">
                                                            <label class="form-label" for="others_condition">Others Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.others_condition}" v-model="retrievedData.others_condition" name="others_condition" id="others_condition" aria-describedby="others_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
                                                        <div class="mb-3 col-md-8">
                                                            <label class="form-label" for="others_description">Others Description:</label>
                                                            <Field type="text" class="form-control" :class="{'is-invalid':errors.others_description}" v-model="retrievedData.others_description" name="others_description" id="others_description" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="rating_unit_condition">Unit Condition:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.rating_unit_condition}" v-model="retrievedData.rating_unit_condition"  name="rating_unit_condition" id="rating_unit_condition" aria-describedby="rating_unit_condition">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">New</option>
                                                                <option value="2" >Very Good Condition</option>
                                                                <option value="3" >Good</option>
                                                                <option value="4" >Works with Disabilities</option>
                                                                <option value="5" >Works With Great Disabilities</option>

                                                            </Field>
                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="rating_in_operation">In Operation:</label>
                                                            <Field as="select" class="form-control" :class="{'is-invalid':errors.rating_in_operation}" v-model="retrievedData.rating_in_operation" name="rating_in_operation" id="rating_in_operation" aria-describedby="rating_in_operation">
                                                                <option value=""  disabled>Selecionar</option>
                                                                <option value="1">Monitor and Follow Maintenance Plans</option>
                                                                <option value="2" >Monitor and Follow Maintenance Plans</option>
                                                                <option value="3" >Needs Minor Repairs (Tightenin)</option>
                                                                <option value="4" >Requires Localized Repairs</option>
                                                                <option value="5" >Needs Complete Repair</option>

                                                            </Field>
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
                                                            <label class="form-label" for="recommendation_1">Recommendation 1:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.recommendation_1}" v-model="retrievedData.recommendation_1" name="recommendation_1" id="recommendation_1" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="recommendation_2">Recommendation 2:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.recommendation_2}" v-model="retrievedData.recommendation_2" name="recommendation_2" id="recommendation_2" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="recommendation_3">Recommendation 3:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.recommendation_3}" v-model="retrievedData.recommendation_3" name="recommendation_3" id="recommendation_3" placeholder=""/>

                                                        </div>
												    </div>

                                                    <div class="row">
                                                        <div class="mb-3 col-md-12">
                                                            <label class="form-label" for="recommendation_4">Recommendation 4:</label>
                                                            <Field type="textarea" class="form-control" :class="{'is-invalid':errors.recommendation_4}" v-model="retrievedData.recommendation_4" name="recommendation_4" id="recommendation_4" placeholder=""/>

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