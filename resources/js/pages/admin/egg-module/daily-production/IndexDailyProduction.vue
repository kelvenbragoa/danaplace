<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';
import moment from 'moment';

const toastr = useToastr();
const searchQuery = ref(null);
const flockFilter = ref('');
const dateFilter = ref('');
const flocks = ref([]);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);

const getFlocks = () => {
    axios.get('/admin/flocks-all').then((response) => {
        flocks.value = response.data;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/daily-production?page=${page}`, {
        params: {
            query: searchQuery.value,
            flock_id: flockFilter.value || undefined,
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

    axios.delete(`/admin/daily-production/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter(data => data.id !== dataIdBeingDeleted.value);
            $('#deleteModal').modal('hide');
            toastr.success('Registro apagado com sucesso');
        }).catch(() => {
            toastr.error('Erro ao apagar');
            $('#deleteModal').modal('hide');
        }).finally(() => {
            loadingButtonDelete.value = false;
        });
};

const getLayingRate = (item) => {
    const birds = item.flock?.current_bird_count || 0;
    if (!birds) return 0;
    return Math.round((item.total_eggs / birds) * 100);
};

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch([flockFilter, dateFilter], () => {
    getData();
});

onMounted(() => {
    getFlocks();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Produção Diária</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Registos de produção diária. {{ retriviedData.total }} registros encontrados.</h5>

                        <router-link to="/admin/producao-diaria/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Adicionar
                        </router-link>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar por código do lote..." aria-label="Search">
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
                                        <th>Data</th>
                                        <th>Lote</th>
                                        <th>Galpão</th>
                                        <th>Total Ovos</th>
                                        <th>Limpos</th>
                                        <th>Partidos</th>
                                        <th>Taxa Postura</th>
                                        <th>Ração (kg)</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ moment(actualData.date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ actualData.flock?.code || '-' }}</td>
                                        <td>{{ actualData.flock?.house?.name || '-' }}</td>
                                        <td>{{ actualData.total_eggs }}</td>
                                        <td>{{ actualData.clean_eggs }}</td>
                                        <td>{{ actualData.cracked_eggs }}</td>
                                        <td>{{ getLayingRate(actualData) }}%</td>
                                        <td>{{ actualData.feed_consumption_kg }}</td>
                                        <td>
                                            <router-link :to="'/admin/producao-diaria/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/producao-diaria/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="10" align="center">Nenhum resultado encontrado</td>
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
                        Ao apagar este registo de produção, os dados serão removidos permanentemente.
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
