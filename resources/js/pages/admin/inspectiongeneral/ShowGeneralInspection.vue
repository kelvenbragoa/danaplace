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
        .get(`/generalinspections/+${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data.generalinspection;

            equipmentref.value = response.data.generalinspection.equipment.ref;
            jobcardnr.value = response.data.generalinspection.id;
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
                                to="/admin/generalinspections"
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
                                        <th class="bg-secondary" style="font-size:10px">DIAGNOSTIC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">
                                            {{ retrievedData.diagnostic }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">INSPECTION CONDITIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">
                                            {{ retrievedData.inspection_condition }}
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
                                        <th class="bg-secondary" style="font-size:10px">CONCLUDING REMARKS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">
                                            {{ retrievedData.concluding_remarks }}
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
                                        <th class="bg-secondary text-center" colspan="5" style="font-size:10px">
                                            GENERAL CONDITIONS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary text-center" colspan="5" style="font-size:10px">
                                            INTERIOR
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">COMPONENTS</th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            CONDITION
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">INSPECTION RESULT</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">Interior </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.interior
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.interior_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Seats</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.seats
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.seats_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Interior Trim Roof Lining Carpet</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.interior_trim_roof_lining_carpet
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.interior_trim_roof_lining_carpet_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Dashboard Cluster</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.dashboard_cluster
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.dashboard_cluster_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Interior Control Unitis</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.interior_control_unitis
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.interior_control_unitis_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Air Condition</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.air_condition
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.air_condition_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Eletric Windows</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.eletric_windows
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.eletric_windows_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Eletric Sunroof</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.eletric_sunroof
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.eletric_sunroof_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Seat Heaters</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.seat_heaters
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.seat_heaters_description
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    
                                    <tr>
                                        <th class="bg-secondary text-center" colspan="5" style="font-size:10px">
                                            EXTERIOR
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">COMPONENTS</th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            CONDITION
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">INSPECTION RESULT</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">Rims </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.rims
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rims_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Mechanical Doors</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.mechanical_doors
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.mechanical_doors_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Vehicle Body</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.vehicle_body
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.vehicle_body_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Windows</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.windows
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.windows_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Hang on Parts</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.hang_on_parts
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.hang_on_parts_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Spare Wheel</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.spare_wheel
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.spare_wheel_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Tires</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.tires
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.tires_description
                                            }}
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    
                                    <tr>
                                        <th class="bg-secondary text-center" colspan="5" style="font-size:10px">
                                            ENGINE, GEARBOX, DRIVETRAIN
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">COMPONENTS</th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            CONDITION
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">INSPECTION RESULT</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">Engine Oil </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.engine_oil
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.engine_oil_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Engine Cooling System</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.engine_cooling_system
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.engine_cooling_system_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Oil Loss Engine</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.oil_loss_engine
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.oil_loss_engine_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Oil Loss Gear Box</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.oil_loss_gear_box
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.oil_loss_gear_box_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Exhaust System</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.exhaust_system
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.exhaust_system_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Gear Shift</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.gearshift
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.gearshift_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Noise Levels Engine</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.noise_levels_engine
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.noise_levels_engine_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Noise Levels Transmissions </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.noise_levels_transmissions
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.noise_levels_transmissions_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Noise Levels Axles </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.noise_levels_axles
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.noise_levels_axles_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Noise Levels Axles Inspection Result </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.noise_levels_axles_description
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.noise_levels_axles_description_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Engine </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.engine
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
                                        <td style="font-size:10px">GearBox </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.gearbox
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.gearbox_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Drive Train </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.drivetrain
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.drivetrain_description
                                            }}
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    
                                    <tr>
                                        <th class="bg-secondary text-center" colspan="5" style="font-size:10px">
                                            AXLES, UNDERCARRIAGE
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px">COMPONENTS</th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            CONDITION
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">INSPECTION RESULT</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">Brake Fluid </td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.brake_fluid
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.brake_fluid_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Brakes</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.brakes
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.brakes_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Brake System</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.brake_system
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.brake_system_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Vehicle Undercarriage</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.vehicle_undercarriage
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.vehicle_undercarriage_description
                                            }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">Axles Suspension</td>
                                        <td style="font-size:10px">
                                            {{
                                                conditionStatus(
                                                    retrievedData.axles_suspension
                                                )
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.axles_suspension_description
                                            }}
                                        </td>
                                    </tr>

                                   
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    
                                    <tr>
                                        <th class="bg-secondary text-center" colspan="5" style="font-size:10px">
                                        BRAKE TEST RESULT                                        
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px"></th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            LEFT
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            RIGHT
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px"></th>
                                        <th class="bg-secondary" style="font-size:10px"></th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px"></th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            Braking Force [KN] 
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            Braking Force [KN] 
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            Axle Weight [KG] 
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            Deceleration [m/s²]
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">FRONT </td>
                                        <td style="font-size:10px">
                                            {{
                                                    retrievedData.front_left
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_right
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_axle_weight
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_deceleration
                                            }}
                                        </td>

                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">REAR </td>
                                        <td style="font-size:10px">
                                            {{
                                                    retrievedData.rear_left
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_right
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_axle_weight
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_deceleration
                                            }}
                                        </td>

                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">EMERGENCY/PARK </td>
                                        <td style="font-size:10px">
                                            {{
                                                    retrievedData.emergency_left
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.emergency_right
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.emergency_axle_weight
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.emergency_deceleration
                                            }}
                                        </td>

                                    </tr>

                                 
                                   
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    
                                    <tr>
                                        <th class="bg-secondary text-center" colspan="8" style="font-size:10px">
                                            TYRE SPECIFICATION & MEASURAMENT                                        
                                        </th>
                                    </tr>
                                    
                                    <tr>
                                        <th class="bg-secondary" style="font-size:10px"></th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            SIZE
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            LOAD/SPEED INDEX 
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            MANUFACTURE 
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            MODEL
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            TYPE
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            PRODUCTION DATE
                                        </th>
                                        <th class="bg-secondary" style="font-size:10px">
                                            TREAD DEPTH (mm)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size:10px">FRONT LEFT </td>
                                        <td style="font-size:10px">
                                            {{
                                                    retrievedData.front_left_size
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_left_load
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_left_manufacture
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_left_model
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_left_type
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_left_date
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_left_thread_depth
                                            }}
                                        </td>

                                    </tr>

                                    

                                    <tr>
                                        <td style="font-size:10px">FRONT RIGHT </td>
                                        <td style="font-size:10px">
                                            {{
                                                    retrievedData.front_right_size
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_right_load
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_right_manufacture
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_right_model
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_right_type
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_right_date
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.front_right_thread_depth
                                            }}
                                        </td>

                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">REAR LEFT </td>
                                        <td style="font-size:10px">
                                            {{
                                                    retrievedData.rear_left_size
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_left_load
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_left_manufacture
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_left_model
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_left_type
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_left_date
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_left_thread_depth
                                            }}
                                        </td>

                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">REAR RIGHT</td>
                                        <td style="font-size:10px">
                                            {{
                                                    retrievedData.rear_right_size
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_right_load
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_right_manufacture
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_right_model
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_right_type
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_right_date
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.rear_right_thread_depth
                                            }}
                                        </td>

                                    </tr>

                                    <tr>
                                        <td style="font-size:10px">SPARE </td>
                                        <td style="font-size:10px">
                                            {{
                                                    retrievedData.spare_size
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.spare_load
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.spare_manufacture
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.spare_model
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.spare_type
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.spare_date
                                            }}
                                        </td>
                                        <td style="font-size:10px">
                                            {{
                                                retrievedData.spare_thread_depth
                                            }}
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
