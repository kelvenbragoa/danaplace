<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const flockFilter = ref('');
const statusFilter = ref('');
const dateFilter = ref('');
const flocks = ref([]);
const pendingToday = ref([]);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);
const loadingAction = ref(null);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);

const statusLabels = {
    pending: 'Pendente',
    applied: 'Aplicada',
    canceled: 'Cancelada',
};

const routeLabels = {
    injectable: 'Injetável',
    water: 'Água',
    feed: 'Ração',
};

const getFlocks = () => {
    axios.get('/admin/flocks-active').then((response) => {
        flocks.value = response.data;
    });
};

const getPendingToday = () => {
    axios.get('/admin/vaccination-schedule/pending-today').then((response) => {
        pendingToday.value = response.data;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/vaccination-schedule?page=${page}`, {
        params: {
            query: searchQuery.value,
            flock_id: flockFilter.value || undefined,
            status: statusFilter.value || undefined,
            date: dateFilter.value || undefined,
        }
    }).then((response) => {
        retriviedData.value = response.data;
        loadingDiv.value = false;
    });
};

const confirmDeletion = (data) => {
    dataIdBeingDeleted.value = data.id;
    $('#deleteModal').modal('show');
};

const deleteData = () => {
    loadingButtonDelete.value = true;

    axios.delete(`/admin/vaccination-schedule/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter(data => data.id !== dataIdBeingDeleted.value);
            $('#deleteModal').modal('hide');
            toastr.success('Registro apagado com sucesso');
            getPendingToday();
        }).catch(() => {
            toastr.error('Erro ao apagar');
            $('#deleteModal').modal('hide');
        }).finally(() => {
            loadingButtonDelete.value = false;
        });
};

const applyVaccination = (schedule) => {
    loadingAction.value = schedule.id;

    axios.post(`/admin/vaccination-schedule/${schedule.id}/apply`, {
        application_date: new Date().toISOString().split('T')[0],
    }).then((response) => {
        const index = retriviedData.value.data.findIndex(item => item.id === schedule.id);
        if (index !== -1) {
            retriviedData.value.data[index] = response.data;
        }
        toastr.success('Vacinação aplicada com sucesso');
        getPendingToday();
    }).catch(() => {
        toastr.error('Erro ao aplicar vacinação');
    }).finally(() => {
        loadingAction.value = null;
    });
};

const cancelVaccination = (schedule) => {
    loadingAction.value = schedule.id;

    axios.post(`/admin/vaccination-schedule/${schedule.id}/cancel`)
        .then((response) => {
            const index = retriviedData.value.data.findIndex(item => item.id === schedule.id);
            if (index !== -1) {
                retriviedData.value.data[index] = response.data;
            }
            toastr.success('Vacinação cancelada com sucesso');
            getPendingToday();
        }).catch(() => {
            toastr.error('Erro ao cancelar vacinação');
        }).finally(() => {
            loadingAction.value = null;
        });
};

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch([flockFilter, statusFilter, dateFilter], () => {
    getData();
});

onMounted(() => {
    getFlocks();
    getPendingToday();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Calendário Vacinal</h1>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card border-warning">
                    <div class="card-body">
                        <h6 class="text-warning mb-2">Vacinações pendentes para hoje ou em atraso</h6>
                        <p class="mb-0" v-if="pendingToday.length > 0">
                            <strong>{{ pendingToday.length }}</strong> vacinação(ões) pendente(s)
                        </p>
                        <p class="mb-0 text-muted" v-else>Nenhuma vacinação pendente para hoje</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Calendário de vacinações. {{ retriviedData.total }} registros encontrados.</h5>

                        <router-link to="/admin/calendario-vacinal/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Agendar
                        </router-link>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar lote ou vacina..." aria-label="Search">
                            </div>
                        </form>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <select class="form-control" v-model="flockFilter">
                                    <option value="">Todos os lotes</option>
                                    <option v-for="flock in flocks" :key="flock.id" :value="flock.id">
                                        {{ flock.code }} - {{ flock.house?.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" v-model="statusFilter">
                                    <option value="">Todos os estados</option>
                                    <option value="pending">Pendente</option>
                                    <option value="applied">Aplicada</option>
                                    <option value="canceled">Cancelada</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" v-model="dateFilter"/>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Data Prevista</th>
                                        <th>Lote</th>
                                        <th>Galpão</th>
                                        <th>Vacina</th>
                                        <th>Via</th>
                                        <th>Dosagem</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ actualData.scheduled_date }}</td>
                                        <td>{{ actualData.flock?.code || '-' }}</td>
                                        <td>{{ actualData.flock?.house?.name || '-' }}</td>
                                        <td>{{ actualData.vaccine?.name || '-' }}</td>
                                        <td>{{ routeLabels[actualData.administration_route] || actualData.administration_route }}</td>
                                        <td>{{ actualData.dosage || '-' }}</td>
                                        <td>
                                            <span class="badge bg-warning" v-if="actualData.status === 'pending'">{{ statusLabels.pending }}</span>
                                            <span class="badge bg-success" v-else-if="actualData.status === 'applied'">{{ statusLabels.applied }}</span>
                                            <span class="badge bg-secondary" v-else>{{ statusLabels.canceled }}</span>
                                        </td>
                                        <td>
                                            <router-link :to="'/admin/calendario-vacinal/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/calendario-vacinal/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
                                            <a v-if="actualData.status === 'pending'" href="#" @click.prevent="applyVaccination(actualData)" title="Aplicar">
                                                <vue-feather type="check-circle" v-if="loadingAction !== actualData.id"></vue-feather>
                                                <span v-else class="spinner-border spinner-border-sm"></span>
                                            </a>
                                            <a v-if="actualData.status === 'pending'" href="#" @click.prevent="cancelVaccination(actualData)" title="Cancelar">
                                                <vue-feather type="x-circle"></vue-feather>
                                            </a>
                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="9" align="center">Nenhum resultado encontrado</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <Bootstrap4Pagination :data="retriviedData" @pagination-change-page="getData"/>
            </div>
        </div>

        <div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Deseja mesmo eliminar este item.</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Ao apagar este agendamento, o registo será removido permanentemente.
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
