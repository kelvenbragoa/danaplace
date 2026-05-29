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
const image = ref();
const destination = ref();
const uploads = ref();
let openedbyuser = ref();
let closedbyuser = ref();
let jobtasks = ref();

let destinationuser = ref();
let requeststock = ref([]);
let requesttool = ref([]);
let requesttechnician = ref([]);
let resolutions = ref();
let preresolutions = ref();
let equipmentref = ref(0);
let jobcardnr = ref(0);
const toastr = useToastr();

let { paperize } = usePaperizer("print-me", {
    styles: [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
    ],
    windowTitle: `REF: ${equipmentref.value} - JOBCARD Nº: #${router.currentRoute.value.params.id}`,
});

const downloadMcscr = () => {
    loadingprint.value = true;
    paperize();
    loadingprint.value = false;
};

const getData = () => {
    axios
        .get(`/mcscr/+${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data.mcscr;
            jobtasks.value = response.data.jobtasks;
            requeststock.value = response.data.requeststock;
            requesttool.value = response.data.requesttool;
            requesttechnician.value = response.data.requesttechnician;
            destination.value = response.data.destination;
            uploads.value = response.data.uploads;
            destinationuser.value = response.data.destinationuser
            openedbyuser.value = response.data.openedby;
            closedbyuser.value = response.data.closedby;
            preresolutions.value = response.data.preresolutions;
            resolutions.value = response.data.resolutions;
            equipmentref.value = response.data.mcscr.equipment.ref;
            jobcardnr.value = response.data.mcscr.id;

            if (destination.value.image != null) {
                image.value = "/storage/" + destination.value.image;
            } else {
                image.value = "/files/img/sys/companylogo.png";
            }
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
        <h1 class="h3 mb-3">MCSCR</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body m-sm-3 m-md-5 border">
                        <div class="mb-4">
                            <a @click="$router.go(-1)" class="btn btn-pill btn-primary mt-3"><vue-feather type="arrow-left"></vue-feather>Voltar</a> 
                            
                        </div>
                        <div id="print-me">
                            <div class="row">
                                <!-- <div class="col">
                                                <div class="col-2">
                                                    <img src="/files/img/sys/companylogo.png" class="img-fluid" alt="M+D" width="450" height="450">
                                                </div>
                                            </div> -->
                                <br />
                                <div
                                    class="col text-left"
                                    style="text-align: left"
                                >
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
                            </div>
                            
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="font-size: 10px;">
                                            DATE:
                                            {{
                                                moment(
                                                    retrievedData.created_at
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            FLEET:
                                            {{
                                                retrievedData.equipment
                                                    .type_equipment.name ??
                                                "N/A"
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            REF:
                                            {{
                                                retrievedData.equipment.ref ??
                                                "N/A"
                                            }}
                                        </th>
                                        <th style="font-size: 10px;">
                                            JOB CARD Nº: #{{ retrievedData.id }}
                                        </th>
                                    </tr>
                                </thead>
                                
                            </table>
                            <div class="row">
                                <div class="col-6">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th
                                                    colspan="2"
                                                    class="bg-secondary"
                                                    style="font-size: 10px;"
                                                >
                                                    CUSTOMER INFORMATION
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">COMPANY NAME</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .company_name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">NAME OF RESPONSIBLE</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination.name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">ADDRESS</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .company_address ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">CITY</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData
                                                            .destination
                                                            .province.name ??
                                                        "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">MOBILE</td>
                                                <td style="font-size: 10px;">
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
                                                    style="font-size: 10px;"
                                                >
                                                    ENGINE INFORMATION
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">Nº OF CHASSI</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.equipment
                                                            .chassis ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">MODEL</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.equipment
                                                            .model ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">Nº SERIE</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.equipment
                                                            .serial ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">YEAR</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.equipment
                                                            .buy_year ?? "N/A"
                                                    }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">HOUR METER</td>
                                                <td style="font-size: 10px;">
                                                    {{
                                                        retrievedData.distance ??
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
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            SUBJECT OF THE OCCURRENCE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.reason ?? ""
                                            }}
                                            /
                                            {{
                                                retrievedData.reason_name ==
                                                null
                                                    ? ""
                                                    : retrievedData.reason_name
                                                          .name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            CAUSE OF FAILURE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{ retrievedData.cause ?? "" }} /
                                            {{
                                                retrievedData.cause_name == null
                                                    ? ""
                                                    : retrievedData.cause_name
                                                          .name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">SOLUTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.solution ?? ""
                                            }}
                                            /
                                            {{
                                                retrievedData.solution_name ==
                                                null
                                                    ? ""
                                                    : retrievedData
                                                          .solution_name.name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            CONSEQUENCE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.consequence ??
                                                ""
                                            }}
                                            /
                                            {{
                                                retrievedData.consequence_name ==
                                                null
                                                    ? ""
                                                    : retrievedData
                                                          .consequence_name.name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            RECOMMENDATION
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.recommendation ??
                                                ""
                                            }}
                                            /
                                            {{
                                                retrievedData.recommendation_name ==
                                                null
                                                    ? ""
                                                    : retrievedData
                                                          .recommendation_name
                                                          .name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">COMMENTS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.first_observation ??
                                                "N/A"
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" colspan="4" style="font-size: 10px;">
                                            RECOMMENDATION TASKS
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(jobtask, index) in jobtasks" :key="jobtask.id">
                                        <td colspan="1" style="font-size: 10px;">
                                            #{{ index + 1  }}
                                        </td>
                                        <td colspan="2" style="font-size: 10px;">
                                            {{ jobtask.task  }}
                                        </td>
                                        <td colspan="1" style="font-size: 10px;">
                                            {{ jobtask.status.name  }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="4">WORK REQUEST</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(preresolution, index) in preresolutions" :key="preresolution.id">
                                        <td colspan="1" style="font-size: 10px;">
                                            #{{ index + 1  }}
                                        </td>
                                        <td colspan="2" style="font-size: 10px;">
                                            {{ preresolution.resolution_name  }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>


                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="4">ACTIONS TAKEN TO RESOLVE THE FAULT</th>
                                        <!-- <th class="bg-secondary" style="font-size: 10px;" colspan="4">STATUS</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(resolution, index) in resolutions" :key="resolution.id">
                                        <td colspan="1" style="font-size: 10px;">
                                            #{{ index + 1  }}
                                        </td>
                                        <td colspan="3" style="font-size: 10px;">
                                            {{ resolution.resolution_name  }}
                                        </td>
                                        <!-- <td colspan="3" style="font-size: 10px;">
                                            {{ resolution.mcscr_status != null ? resolution.mcscr_status.name : ''  }}
                                        </td> -->
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">OPENED AT:</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            DIAGNOSIS AT:
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            EXECUTION AT:
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            APPROVAL AT:
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">CLOSED AT:</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                moment(
                                                    retrievedData.opened_at
                                                ).format("DD-MM-YYYY H:mm")
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.diagnosis_start_at ==
                                                null
                                                    ? "N/A"
                                                    : moment(
                                                          retrievedData.diagnosis_start_at
                                                      ).format(
                                                          "DD-MM-YYYY H:mm"
                                                      )
                                            }}
                                            |

                                            {{
                                                retrievedData.diagnosis_start_at ==
                                                null
                                                    ? "N/A"
                                                    : moment(
                                                          retrievedData.diagnosis_start_at
                                                      ).diff(
                                                          retrievedData.opened_at,
                                                          "hours"
                                                      ) +
                                                      " H (" +
                                                      moment(
                                                          retrievedData.diagnosis_start_at
                                                      ).diff(
                                                          retrievedData.opened_at,
                                                          "minutes"
                                                      ) +
                                                      " Min)"
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.execution_start_at ==
                                                null
                                                    ? "N/A"
                                                    : moment(
                                                          retrievedData.execution_start_at
                                                      ).format(
                                                          "DD-MM-YYYY H:mm"
                                                      )
                                            }}
                                            |
                                            {{
                                                retrievedData.execution_start_at ==
                                                null
                                                    ? "N/A"
                                                    : moment(
                                                          retrievedData.execution_start_at
                                                      ).diff(
                                                          retrievedData.diagnosis_start_at,
                                                          "hours"
                                                      ) +
                                                      " H (" +
                                                      moment(
                                                          retrievedData.execution_start_at
                                                      ).diff(
                                                          retrievedData.diagnosis_start_at,
                                                          "minutes"
                                                      ) +
                                                      " Min)"
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.awaiting_approval_start_at ==
                                                null
                                                    ? "N/A"
                                                    : moment(
                                                          retrievedData.awaiting_approval_start_at
                                                      ).format(
                                                          "DD-MM-YYYY H:mm"
                                                      )
                                            }}
                                            |
                                            {{
                                                retrievedData.awaiting_approval_start_at ==
                                                null
                                                    ? "N/A"
                                                    : moment(
                                                          retrievedData.awaiting_approval_start_at
                                                      ).diff(
                                                          retrievedData.execution_start_at,
                                                          "hours"
                                                      ) +
                                                      " H (" +
                                                      moment(
                                                          retrievedData.awaiting_approval_start_at
                                                      ).diff(
                                                          retrievedData.execution_start_at,
                                                          "minutes"
                                                      ) +
                                                      " Min)"
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.closed_at == null
                                                    ? "N/A"
                                                    : moment(
                                                          retrievedData.closed_at
                                                      ).format(
                                                          "DD-MM-YYYY H:mm"
                                                      )
                                            }}
                                            |
                                            {{
                                                retrievedData.closed_at == null
                                                    ? "N/A"
                                                    : moment(
                                                          retrievedData.closed_at
                                                      ).diff(
                                                          retrievedData.awaiting_approval_start_at,
                                                          "hours"
                                                      ) +
                                                      " H (" +
                                                      moment(
                                                          retrievedData.closed_at
                                                      ).diff(
                                                          retrievedData.awaiting_approval_start_at,
                                                          "minutes"
                                                      ) +
                                                      " Min)"
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            FAILURE DETAILS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            TYPE OF FAILURE
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">COMPONENT</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            SUBCOMPONENT
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">REWORK</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            REASON FOR WAITING (STOP)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.type_malfunction
                                                    .name ?? "N/A"
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.component == null
                                                    ? "N/A"
                                                    : retrievedData.component
                                                          .name
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.subcomponent ==
                                                null
                                                    ? "N/A"
                                                    : retrievedData.subcomponent
                                                          .name
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.is_rework == 1
                                                    ? "YES"
                                                    : "NO"
                                            }}
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.waiting_status
                                                    .name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            COSTS INVOLVED
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">LABOR</th>
                                        <th class="bg-secondary" style="font-size: 10px;">MATERIAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.material_labor ??
                                                "0"
                                            }}
                                            MT
                                        </td>
                                        <td style="font-size: 10px;">
                                            {{
                                                retrievedData.material_cost ??
                                                "0"
                                            }}
                                            MT
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table
                                class="table table-bordered"
                                v-for="(request, idx) in requeststock"
                                :key="request.id"
                            >
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            MATERIALS REQUIREMENT
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                        <th class="bg-secondary" style="font-size: 10px;">REQUEST ID</th>
                                        <th class="bg-secondary" style="font-size: 10px;">MATERIAL</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            QUANTITY REQUIRED
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            QUANTITY DELIVERED
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            material, index
                                        ) in request.requestitens"
                                        :key="material.id"
                                    >
                                        <td style="font-size: 10px;">#{{ index + 1 }}</td>
                                        <td style="font-size: 10px;">{{ request.id }}</td>
                                        <td style="font-size: 10px;">{{ material.product.name }}</td>
                                        <td style="font-size: 10px;">{{ material.quantity }}</td>
                                        <td style="font-size: 10px;">
                                            {{ material.delivered_quantity }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table
                                class="table table-bordered"
                                v-for="(requesttech, idx) in requesttechnician"
                                :key="requesttech.id"
                            >
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            TECHNICAL REQUIREMENTS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                        <th class="bg-secondary" style="font-size: 10px;">REQUEST ID</th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            DEPARTAMENT
                                        </th>
                                        <th class="bg-secondary" style="font-size: 10px;">
                                            REQUIRED TECHNICIAN
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            tech, index
                                        ) in requesttech.requestitens"
                                        :key="tech.id"
                                    >
                                        <td style="font-size: 10px;">#{{ index + 1 }}</td>
                                        <td style="font-size: 10px;">{{ requesttech.id }}</td>
                                        <td style="font-size: 10px;">{{ tech.department.name }}</td>
                                        <td style="font-size: 10px;">
                                            {{
                                                tech.technician == null
                                                    ? "------"
                                                    : tech.technician.name
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <table
                                class="table table-bordered"
                                v-for="(requesttoolshop, idx) in requesttool"
                                :key="requesttoolshop.id"
                            >
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="5">
                                            REQUEST TOOLS
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;">#</th>
                                        <th class="bg-secondary" style="font-size: 10px;">REQUEST ID</th>
                                        <th class="bg-secondary" style="font-size: 10px;">TOOL</th>
                                        <th class="bg-secondary" style="font-size: 10px;">CODE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(
                                            toolshop, index
                                        ) in requesttoolshop.requestitens"
                                        :key="toolshop.id"
                                    >
                                        <td style="font-size: 10px;">{{ index + 1 }}</td>
                                        <td style="font-size: 10px;">{{ requesttoolshop.id }}</td>
                                        <td style="font-size: 10px;">{{ toolshop.tool.name }}</td>
                                        <td style="font-size: 10px;">{{ toolshop.tool.code }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="2">
                                            SIGNATURE
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-size: 10px;">TECHNICIAN NAME: {{ openedbyuser.firstName }} {{ openedbyuser.lastName }}</td>
                                        <td style="font-size: 10px;">CLIENT NAME: {{ retrievedData.destination.company_name }} </td>
                                        
                                    </tr>
                                    <tr>
                                        <td style="font-size: 10px;">SIGNATURE: 
                                            <div v-if="openedbyuser.signature != null">
                                                <img :src="openedbyuser.signature" 
                                                    style="height: auto; 
                                                    width: auto; 
                                                    max-width: 140px; 
                                                    max-height: 140px;">
                                            </div>
                                        </td>
                                        
                                        <td style="font-size: 10px;">SIGNATURE:
                                            <div v-if="destinationuser != null">
                                                <img v-if="destinationuser.signature != null" :src="destinationuser.signature"  
                                                    style="height: auto; 
                                                    width: auto; 
                                                    max-width: 140px; 
                                                    max-height: 140px;">
                                            </div>
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
                                                    style="font-size: 10px;"
                                                >
                                                    TRIP INFORMATION
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">START DATE:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.trip_start_date }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">RETURN DATE:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.trip_return_date }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">TRAVEL HOURS:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.trip_travel_hours }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">TRAVEL OF:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.trip_travel_of }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">TRIP TO:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.trip_travel_to }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">DISTANCE TRAVELED:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.trip_distance_traveled }}</td>
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
                                                    style="font-size: 10px;"
                                                >
                                                    WORK
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="font-size: 10px;">START TIME:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.work_start_time }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">RETURN TIME:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.work_return_time }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">TOTAL AMOUNT OF HOURS:</td>
                                                <td style="font-size: 10px;">{{ retrievedData.work_total_amount_of_hours }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">NIGHTS AT THE HOTEL</td>
                                                <td style="font-size: 10px;">{{ retrievedData.work_nights_at_hotel }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">START TIMES (EXTRA)</td>
                                                <td style="font-size: 10px;">{{ retrievedData.work_extra_start_times }}</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 10px;">ENDING TIMES (EXTRA)</td>
                                                <td style="font-size: 10px;">{{ retrievedData.work_extra_ending_times }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                          
                            <div style="page-break-before:always">&nbsp;</div>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="bg-secondary" style="font-size: 10px;" colspan="2">
                                            ATTACHMENTS
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <td style="font-size: 10px;">

                                    
                                    <div class="row">
                                                <!-- imagem -->
                                                <div class="col-sm-6 col-xl-3" v-for="upload in uploads" :key="upload.id">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col mt-0">
                                                                    <img :src='upload.file' alt="" class="w-100">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- imagem -->
                                    </div>
                                </td>
                                </tbody>
                            </table>

                            
                           
                        
                        
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

