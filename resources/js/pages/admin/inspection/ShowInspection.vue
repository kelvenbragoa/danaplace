<script setup>
import axios from "axios";
import {
    ref,
    onMounted,
    reactive,
    defineEmits,
    defineComponent,
    watch,
} from "vue";
import moment from "moment";
import { useToastr } from "../../../toastr";
import { debounce } from "lodash";
import { Form, Field } from "vee-validate";
import { useRouter } from "vue-router";
import * as yup from "yup";
import VueFeather from "vue-feather";
import { Bootstrap4Pagination } from "laravel-vue-pagination";
import VueHtmlToPaper from "vue-html-to-paper";
import { usePaperizer } from "paperizer";

let retrievedData = ref([]);
let loadingSubmit = ref([true]);
let loadingDiv = ref([true]);
const loadingprint = ref(false);
const router = useRouter();
let self = this;
let requeststock = ref([]);
let requesttool = ref([]);
let requesttechnician = ref([]);
const toastr = useToastr();
let equipmentref = ref(0);
let jobcardnr = ref(0);
const { paperize } = usePaperizer("print-me", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `REF: ${equipmentref.value} - JOBCARD Nº: #${router.currentRoute.value.params.id}`,
});

const downloadMcscr = () => {
    // axios.get(`/download-mcscr/+${router.currentRoute.value.params.id}`)
    //    .then((response)=>{
    //     console.log(response.data)
    //    }).catch(()=>{

    //    })
    loadingprint.value = true;
    // window.print();
    // router.push('/admin/mcscr').catch(()=>{})
    // this.$htmlToPaper('printMe');
    paperize();
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
};

const conditionStatus = (status) => {
    switch (status) {
        case 1:
            return "New";
        case 2:
            return "Very Good Condition";
        case 3:
            return "Good";
        case 4:
            return "Works with Disabilities";
        case 5:
            return "Works With Great Disabilities";
        default:
            return "Other";
    }
};
const conditionOperation = (status) => {
    switch (status) {
        case 1:
            return "Monitor and Follow Maintenance Plans";
        case 2:
            return "Monitor and Follow Maintenance Plans";
        case 3:
            return "Needs Minor Repairs (Tightenin)";
        case 4:
            return "Requires Localized Repairs";
        case 5:
            return "Needs Complete Repair";
        default:
            return "Other";
    }
};

const getData = () => {
    axios
        .get(`/inspections/+${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data.inspection;

            equipmentref.value = response.data.inspection.equipment.ref;
            jobcardnr.value = response.data.inspection.id;
            // requeststock.value = response.data.requeststock
            // requesttool.value = response.data.requesttool
            // requesttechnician.value = response.data.requesttechnician
        })
        .catch(() => {
            loadingDiv.value = false;
        });
};

onMounted(() => {
    getData();
});
</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Inspeção</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body m-sm-3 m-md-5 border">
                        <div class="mb-4">
                            <router-link
                                to="/admin/inspections"
                                class="btn btn-pill btn-primary mt-3"
                                ><vue-feather type="arrow-left"></vue-feather
                                >Voltar</router-link
                            >
                        </div>
                        <div id="print-me">

                            <div class="row ">
                                <div
                                    class="col text-left"
                                    style="text-align: left"
                                >
                                    <img
                                        src="/files/img/sys/companylogo.png"
                                        class="img-fluid"
                                        alt="image"
                                        width="150"
                                        height="150"
                                        style="text-align: left"
                                    />
                                </div>
                                <div class="col">
                                    <br />
                                </div>
                                <div
                                    class="col text-right"
                                    style="text-align: right"
                                >
                                    <!-- <img
                                        src="/files/img/sys/volvopenta1.png"
                                        class="img-fluid mt-2"
                                        alt="image"
                                        width="250px"
                                        height="250px"
                                        style="text-align: right"
                                    /> -->
                                </div>
                            </div>

                            
                            <div class="row">
                                <div class="col">
                                    <!-- <div class="text-muted">Área de Manutenção</div> -->
                                    <!-- <strong> {{ retrievedData.area.name ?? 'N/A' }} </strong> -->
                                    <p style="font-size:10px">
                                        Condominio Areia Branca Lda
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
                                <!-- <div class="col text-right" style="text-align: right;">
                                                <div class="text-muted" style="text-align: right;">Clientes</div>
                                                <strong style="text-align: right;"> {{ retrievedData.destination.name ?? 'N/A' }} </strong>
                                                <p style="text-align: right;"> {{ retrievedData.destination.company_name ?? 'N/A' }} 
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
                                            </div> -->
                            </div>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="font-size:10px">
                                            DATE:
                                            {{
                                                moment(
                                                    retrievedData.created_at
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </th>
                                        <th style="font-size:10px">
                                            FLEET:
                                            {{
                                                retrievedData.equipment
                                                    .type_equipment.name ??
                                                "N/A"
                                            }}
                                        </th>
                                        <th style="font-size:10px">
                                            REF:
                                            {{
                                                retrievedData.equipment.ref ??
                                                "N/A"
                                            }}
                                        </th>
                                        <th style="font-size:10px">
                                            INSPECTION Nº: #{{ retrievedData.id }}
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">OPENED AT:</th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            CLOSED AT:
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            INSPECTION Nº:
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            STATUS:
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.opened_at == null ? 'N/A':
                                                moment(
                                                    retrievedData.opened_at
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                               retrievedData.closed_at == null ? 'N/A':
                                                moment(
                                                    retrievedData.closed_at
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </td>
                                        
                                        <td style="font-size:10px">
                                            #{{
                                                retrievedData.id
                                            }}
                                           
                                        </td>
                                        
                                        <td style="font-size:10px">
                                            <span
                                        v-if="
                                            retrievedData.inspection_status_id ==
                                            1
                                        "
                                        class="badge bg-warning"
                                    >
                                        {{
                                            retrievedData.inspection_status.name
                                        }}
                                    </span>
                                    <span
                                        v-if="
                                            retrievedData.inspection_status_id ==
                                            2
                                        "
                                        class="badge bg-success"
                                    >
                                        {{
                                            retrievedData.inspection_status.name
                                        }}
                                    </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th
                                                    colspan="2"
                                                    class="bg-secondary"
                                                    style="font-size:10px"
                                                >
                                                    CUSTOMER INFORMATION
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size:10px">COMPANY NAME</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .company_name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:10px">NAME OF RESPONSIBLE</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData
                                                            .destination.name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:10px">ADDRESS</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .company_address ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:10px">CITY</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .province.name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:10px">MOBILE</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .company_mobile ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th
                                                    colspan="2"
                                                    class="bg-secondary"
                                                    style="font-size:10px"
                                                >
                                                    ENGINE INFORMATION
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size:10px">Nº OF CHASSI</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData.equipment
                                                            .chassis ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:10px">MODEL</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData.equipment
                                                            .model ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:10px">Nº SERIE</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData.equipment
                                                            .serial ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:10px">YEAR</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData.equipment
                                                            .buy_year ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:10px">HOUR METER</td>
                                                <td style="font-size:10px">
                                                    {{
                                                        retrievedData.total_hours ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>



                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">UNIT CONDITION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.rating_unit_condition
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">IN OPERATION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">
                                            {{
                                                conditionOperation(
                                                    retrievedData.rating_in_operation
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>


                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">COMMENTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">
                                            {{ retrievedData.comments }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" colspan="2" style="font-size:10px">
                                            SIGNATURE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">TECHNICIAN NAME:</td>
                                        <td style="font-size:10px">SIGNATURE:</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:10px">CLIENT NAME:</td>
                                        <td style="font-size:10px">SIGNATURE:</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div style="page-break-before:always">&nbsp;</div>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" colspan="5" style="font-size:10px">
                                            COMPONENT CONDITIONS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">
                                            ITENS
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">VERIFICATION</th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            CONDITION
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">Description of the most relevant
                                            defects</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">1</td>
                                        <td style="font-size:10px">Engine</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.engine_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.engine_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">2</td>
                                        <td style="font-size:10px">Electrical System</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.eletrical_system_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.eletrical_system_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">3</td>
                                        <td style="font-size:10px">Transmission</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.transmission_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.transmission_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">4</td>
                                        <td style="font-size:10px">Control System</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.control_system_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.control_system_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">5</td>
                                        <td style="font-size:10px">Structure / Chassis</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.structure_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.structure_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">6</td>
                                        <td style="font-size:10px">Hydraulic System</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.hydraulic_system_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.hydraulic_system_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">7</td>
                                        <td style="font-size:10px">Pneumatic System</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.pneumatic_system_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.pneumatic_system_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">8</td>
                                        <td style="font-size:10px">Suspension</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.suspension_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.suspension_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">9</td>
                                        <td style="font-size:10px">Tyres</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.tyres_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.tyres_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">10</td>
                                        <td style="font-size:10px">Blade / Bucket / Winch / Spreader</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.blades_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.blades_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">11</td>
                                        <td style="font-size:10px">Cabin</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.cabin_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.cabin_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">12</td>
                                        <td style="font-size:10px">Others</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.others_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.others_description
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>


                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">ITEMS</th>
                                        <th class="bg-secondary" style="font-size:10px">RECOMMENDATION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">1</td>
                                        <td style="font-size:10px">
                                            {{ retrievedData.recommendation_1 }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:10px">2</td>
                                        <td style="font-size:10px">
                                            {{ retrievedData.recommendation_2 }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:10px">3</td>
                                        <td style="font-size:10px">
                                            {{ retrievedData.recommendation_3 }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:10px">4</td>
                                        <td style="font-size:10px">
                                            {{ retrievedData.recommendation_4 }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>






                       

                          
                            

<!-- 
                            <div class="row ">
                                <div class="col">
                                    <div class="text-muted">
                                        Signed by Industrial Engines
                                    </div>
                                    <strong> Name: </strong>
                                    <span
                                        style="
                                            border-bottom: 1px solid black;
                                            padding-right: 20px;
                                        "
                                        >{{
                                            retrievedData.opened_by_user
                                                .firstName +
                                            " " +
                                            retrievedData.opened_by_user
                                                .lastName
                                        }}</span
                                    >
                                    <br />
                                    <br />
                                    <strong> Signature: </strong
                                    ><span
                                        style="
                                            border-bottom: 1px solid black;
                                            padding-right: 20px;
                                        "
                                        >{{
                                            retrievedData.opened_by_user
                                                .firstName +
                                            " " +
                                            retrievedData.opened_by_user
                                                .lastName
                                        }}</span
                                    >
                                </div>
                                <div class="col">
                                    <br />
                                </div>
                                <div class="col text-right">
                                    <div class="text-muted">
                                        Signed by Client
                                    </div>
                                    <strong>
                                        Name: ______________________</strong
                                    >
                                    <br />
                                    <br />
                                    <strong>
                                        Signature:
                                        ______________________</strong
                                    >
                                </div>
                            </div> -->
                        </div>

                       
                    </div>

                    <div class="text-center">
                        <button
                            @click="downloadMcscr"
                            class="btn btn-primary"
                            :disabled="loadingprint"
                        >
                            <div
                                v-if="loadingprint"
                                class="spinner-border spinner-border-sm"
                                role="status"
                            ></div>
                            <span v-else>Print</span>
                        </button>
                        <div
                            v-if="loadingprint"
                            class="d-flex justify-content-center"
                        >
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
                <br />
                <div class="d-flex justify-content-center">
                    Carregando Dados...
                </div>
            </div>
        </div>
    </div>
</template>
