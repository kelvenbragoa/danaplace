<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const statusFilter = ref('');
const typeFilter = ref('');
const unreadOnly = ref(false);
const unreadCount = ref(0);
const loadingDiv = ref(true);
const loadingTest = ref(false);
const selectedIds = ref([]);

const retriviedData = ref({ data: [] });

const typeLabels = {
    laying: 'Postura',
    mortality: 'Mortalidade',
    inventory: 'Estoque',
    expiry: 'Validade',
    vaccine: 'Vacina',
};

const statusLabels = {
    sent: 'Não lido',
    read: 'Lido',
    resolved: 'Resolvido',
};

const statusBadgeClass = {
    sent: 'badge-warning',
    read: 'badge-info',
    resolved: 'badge-success',
};

const getUnreadCount = () => {
    axios.get('/admin/egg-alerts/unread-count').then((response) => {
        unreadCount.value = response.data.count;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/egg-alerts?page=${page}`, {
        params: {
            query: searchQuery.value,
            status: statusFilter.value || undefined,
            type: typeFilter.value || undefined,
            unread_only: unreadOnly.value ? 1 : undefined,
        }
    }).then((response) => {
        retriviedData.value = response.data;
        loadingDiv.value = false;
        selectedIds.value = [];
    });
};

const toggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

const bulkMarkRead = () => {
    if (selectedIds.value.length === 0) {
        toastr.error('Selecione pelo menos um alerta');
        return;
    }

    axios.post('/admin/egg-alerts/bulk-mark-read', { ids: selectedIds.value })
        .then(() => {
            toastr.success('Alertas marcados como lidos');
            getUnreadCount();
            getData();
        }).catch(() => {
            toastr.error('Erro ao atualizar alertas');
        });
};

const triggerTest = () => {
    loadingTest.value = true;

    axios.get('/admin/egg-alerts/trigger-test')
        .then(() => {
            toastr.success('Alerta de teste criado');
            getUnreadCount();
            getData();
        }).catch(() => {
            toastr.error('Erro ao criar alerta de teste');
        }).finally(() => {
            loadingTest.value = false;
        });
};

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch([statusFilter, typeFilter, unreadOnly], () => {
    getData();
});

onMounted(() => {
    getUnreadCount();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Alertas</h1>

        <div class="row mb-3" v-if="unreadCount > 0">
            <div class="col-md-12">
                <div class="card border-warning">
                    <div class="card-body">
                        <h6 class="text-warning mb-2">Alertas não lidos</h6>
                        <p class="mb-0"><strong>{{ unreadCount }}</strong> alerta(s) por ler</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tabela de alertas. {{ retriviedData.total }} registros encontrados.</h5>

                        <button class="btn btn-pill btn-outline-secondary mt-3 mr-2" @click.prevent="triggerTest" :disabled="loadingTest">
                            <vue-feather type="bell"></vue-feather> Gerar teste
                        </button>
                        <button class="btn btn-pill btn-info mt-3" @click.prevent="bulkMarkRead" :disabled="selectedIds.length === 0">
                            <vue-feather type="check"></vue-feather> Marcar selecionados como lidos
                        </button>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar título ou mensagem..." aria-label="Search">
                            </div>
                        </form>

                        <select class="form-control mt-3 mr-2" style="max-width: 180px; display: inline-block;" v-model="statusFilter">
                            <option value="">Todos os estados</option>
                            <option value="sent">Não lido</option>
                            <option value="read">Lido</option>
                            <option value="resolved">Resolvido</option>
                        </select>

                        <select class="form-control mt-3 mr-2" style="max-width: 180px; display: inline-block;" v-model="typeFilter">
                            <option value="">Todos os tipos</option>
                            <option value="laying">Postura</option>
                            <option value="mortality">Mortalidade</option>
                            <option value="inventory">Estoque</option>
                            <option value="expiry">Validade</option>
                            <option value="vaccine">Vacina</option>
                        </select>

                        <label class="mt-3 ml-2">
                            <input type="checkbox" v-model="unreadOnly"> Apenas não lidos
                        </label>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>#</th>
                                        <th>Tipo</th>
                                        <th>Título</th>
                                        <th>Lote</th>
                                        <th>Data</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>
                                            <input type="checkbox" :checked="selectedIds.includes(actualData.id)" @change="toggleSelect(actualData.id)" :disabled="actualData.status !== 'sent'">
                                        </td>
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ typeLabels[actualData.type] || actualData.type }}</td>
                                        <td>{{ actualData.title }}</td>
                                        <td>{{ actualData.flock?.code || '-' }}</td>
                                        <td>{{ actualData.alert_datetime }}</td>
                                        <td>
                                            <span class="badge" :class="statusBadgeClass[actualData.status] || 'badge-light'">
                                                {{ statusLabels[actualData.status] || actualData.status }}
                                            </span>
                                        </td>
                                        <td>
                                            <router-link :to="'/admin/alertas-ovos/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="8" align="center">Nenhum resultado encontrado</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <Bootstrap4Pagination :data="retriviedData" @pagination-change-page="getData"/>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                </div>
                <br>
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
