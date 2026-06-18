<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingDiv = ref(true);
const loadingDispose = ref(false);
const disposalDate = ref('');
const router = useRouter();
const toastr = useToastr();

const statusLabels = {
    growing: 'Recria',
    laying: 'Postura',
    disposed: 'Descartado',
};

const getData = () => {
    axios.get(`/admin/flocks/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
        });
};

const getMortalityRate = () => {
    if (!retrievedData.value.initial_bird_count) return 0;
    return Math.round(((retrievedData.value.initial_bird_count - retrievedData.value.current_bird_count) / retrievedData.value.initial_bird_count) * 100);
};

const openDisposeModal = () => {
    disposalDate.value = new Date().toISOString().split('T')[0];
    $('#disposeModal').modal('show');
};

const disposeFlock = () => {
    if (!disposalDate.value) {
        toastr.error('Informe a data de descarte');
        return;
    }

    loadingDispose.value = true;

    axios.post(`/admin/flocks/${retrievedData.value.id}/dispose`, {
        actual_disposal_date: disposalDate.value,
    }).then((response) => {
        retrievedData.value = response.data;
        $('#disposeModal').modal('hide');
        toastr.success('Lote descartado com sucesso');
        getData();
    }).catch(() => {
        toastr.error('Erro ao descartar lote');
    }).finally(() => {
        loadingDispose.value = false;
    });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Lote</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Lote: {{ retrievedData.code }}</h5>
                        <router-link to="/admin/lotes" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                        <button
                            v-if="retrievedData.status !== 'disposed'"
                            @click.prevent="openDisposeModal"
                            class="btn btn-pill btn-danger mt-3 ml-2"
                        >
                            Descartar Lote
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-12 col-xxl-12 d-flex">
                                <div class="w-100">
                                    <p><strong>Código:</strong> {{ retrievedData.code }}</p>
                                    <p><strong>Galpão:</strong> {{ retrievedData.house?.name || '-' }}</p>
                                    <p><strong>Granja:</strong> {{ retrievedData.house?.farm?.name || '-' }}</p>
                                    <p><strong>Linhagem:</strong> {{ retrievedData.lineage?.name || '-' }}</p>
                                    <p><strong>Data de Nascimento:</strong> {{ retrievedData.birth_date }}</p>
                                    <p><strong>Data de Alojamento:</strong> {{ retrievedData.housing_date }}</p>
                                    <p><strong>Aves Iniciais:</strong> {{ retrievedData.initial_bird_count }}</p>
                                    <p><strong>Aves Atuais:</strong> {{ retrievedData.current_bird_count }}</p>
                                    <p><strong>Taxa de Mortalidade:</strong> {{ getMortalityRate() }}%</p>
                                    <p><strong>Data Prevista de Descarte:</strong> {{ retrievedData.expected_disposal_date || '-' }}</p>
                                    <p><strong>Data Real de Descarte:</strong> {{ retrievedData.actual_disposal_date || '-' }}</p>
                                    <p>
                                        <strong>Estado:</strong>
                                        <span class="badge bg-info" v-if="retrievedData.status === 'growing'">{{ statusLabels.growing }}</span>
                                        <span class="badge bg-success" v-else-if="retrievedData.status === 'laying'">{{ statusLabels.laying }}</span>
                                        <span class="badge bg-secondary" v-else>{{ statusLabels.disposed }}</span>
                                    </p>
                                    <p v-if="retrievedData.observations"><strong>Observações:</strong> {{ retrievedData.observations }}</p>

                                    <hr>
                                    <h5 class="card-title">Produção Diária ({{ retrievedData.daily_production?.length || 0 }})</h5>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Data</th>
                                                    <th>Total Ovos</th>
                                                    <th>Ovos Partidos</th>
                                                    <th>Ração (kg)</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody v-if="retrievedData.daily_production && retrievedData.daily_production.length > 0">
                                                <tr v-for="(production, index) in retrievedData.daily_production" :key="production.id">
                                                    <td>#{{ index + 1 }}</td>
                                                    <td>{{ production.date }}</td>
                                                    <td>{{ production.total_eggs }}</td>
                                                    <td>{{ production.cracked_eggs }}</td>
                                                    <td>{{ production.feed_consumption_kg }}</td>
                                                    <td>
                                                        <router-link :to="'/admin/producao-diaria/' + production.id"><vue-feather type="eye"></vue-feather></router-link>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tbody v-else>
                                                <tr>
                                                    <td colspan="6" align="center">Nenhum registo de produção</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <hr>
                                    <h5 class="card-title">Mortalidade ({{ retrievedData.mortality?.length || 0 }})</h5>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Data</th>
                                                    <th>Quantidade</th>
                                                    <th>Causa</th>
                                                    <th>Necropsia</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody v-if="retrievedData.mortality && retrievedData.mortality.length > 0">
                                                <tr v-for="(record, index) in retrievedData.mortality" :key="record.id">
                                                    <td>#{{ index + 1 }}</td>
                                                    <td>{{ record.date }}</td>
                                                    <td class="text-danger">{{ record.quantity }}</td>
                                                    <td>{{ record.probable_cause || '-' }}</td>
                                                    <td>
                                                        <span class="badge bg-success" v-if="record.necropsy_performed">Sim</span>
                                                        <span class="badge bg-secondary" v-else>Não</span>
                                                    </td>
                                                    <td>
                                                        <router-link :to="'/admin/mortalidade/' + record.id"><vue-feather type="eye"></vue-feather></router-link>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tbody v-else>
                                                <tr>
                                                    <td colspan="6" align="center">Nenhum registo de mortalidade</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <hr>
                                    <h5 class="card-title">Calendário Vacinal ({{ retrievedData.vaccination_schedule?.length || 0 }})</h5>

                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Data Prevista</th>
                                                    <th>Vacina</th>
                                                    <th>Via</th>
                                                    <th>Estado</th>
                                                    <th>Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody v-if="retrievedData.vaccination_schedule && retrievedData.vaccination_schedule.length > 0">
                                                <tr v-for="(schedule, index) in retrievedData.vaccination_schedule" :key="schedule.id">
                                                    <td>#{{ index + 1 }}</td>
                                                    <td>{{ schedule.scheduled_date }}</td>
                                                    <td>{{ schedule.vaccine?.name || '-' }}</td>
                                                    <td>{{ schedule.administration_route }}</td>
                                                    <td>{{ schedule.status }}</td>
                                                    <td>
                                                        <router-link :to="'/admin/calendario-vacinal/' + schedule.id"><vue-feather type="eye"></vue-feather></router-link>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tbody v-else>
                                                <tr>
                                                    <td colspan="6" align="center">Nenhum agendamento vacinal</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="disposeModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Descartar lote</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" for="disposalDate">Data de descarte</label>
                        <input type="date" class="form-control" id="disposalDate" v-model="disposalDate"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button @click.prevent="disposeFlock" type="button" class="btn btn-danger" :disabled="loadingDispose">
                            <div v-if="loadingDispose" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Confirmar descarte</span>
                        </button>
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
