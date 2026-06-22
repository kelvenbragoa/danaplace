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
const houseFilter = ref('');
const statusFilter = ref('');
const houses = ref([]);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);

const statusLabels = {
    growing: 'Recria',
    laying: 'Postura',
    disposed: 'Descartado',
};

const getHouses = () => {
    axios.get('/admin/houses-all').then((response) => {
        houses.value = response.data;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/flocks?page=${page}`, {
        params: {
            query: searchQuery.value,
            house_id: houseFilter.value || undefined,
            status: statusFilter.value || undefined,
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

    axios.delete(`/admin/flocks/${dataIdBeingDeleted.value}`)
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

const getMortalityRate = (flock) => {
    if (!flock.initial_bird_count) return 0;
    return Math.round(((flock.initial_bird_count - flock.current_bird_count) / flock.initial_bird_count) * 100);
};

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch([houseFilter, statusFilter], () => {
    getData();
});

onMounted(() => {
    getHouses();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Lotes</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tabela dos lotes do sistema. {{ retriviedData.total }} registros encontrados.</h5>
                        <h6 class="card-subtitle text-muted">Para procurar, digite na caixa de procura</h6>

                        <router-link to="/admin/lotes/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Adicionar
                        </router-link>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar código..." aria-label="Search">
                            </div>
                        </form>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <select class="form-control" v-model="houseFilter">
                                    <option value="">Todos os galpões</option>
                                    <option v-for="house in houses" :key="house.id" :value="house.id">
                                        {{ house.name }} ({{ house.farm?.name }})
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control" v-model="statusFilter">
                                    <option value="">Todos os estados</option>
                                    <option value="growing">Recria</option>
                                    <option value="laying">Postura</option>
                                    <option value="disposed">Descartado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Código</th>
                                        <th>Galpão</th>
                                        <th>Granja</th>
                                        <th>Linhagem</th>
                                        <th>Aves Iniciais</th>
                                        <th>Aves Atuais</th>
                                        <th>Mortalidade</th>
                                        <th>Alojamento</th>
                                        <th>Consumo de Ração Diário</th>
                                        <th>Consumo de Água Diário</th>
                                        <th>Horas de Luz Diárias</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ actualData.code }}</td>
                                        <td>{{ actualData.house?.name || '-' }}</td>
                                        <td>{{ actualData.house?.farm?.name || '-' }}</td>
                                        <td>{{ actualData.lineage?.name || '-' }}</td>
                                        <td>{{ actualData.initial_bird_count }}</td>
                                        <td>{{ actualData.current_bird_count }}</td>
                                        <td>{{ getMortalityRate(actualData) }}%</td>
                                        <td>{{ moment(actualData.housing_date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ actualData.daily_feed_consumption_kg }} kg</td>
                                        <td>{{ actualData.daily_water_consumption_liters }} L</td>
                                        <td>{{ actualData.daily_light_hours }} h</td>
                                        <td>
                                            <span class="badge bg-info" v-if="actualData.status === 'growing'">{{ statusLabels.growing }}</span>
                                            <span class="badge bg-success" v-else-if="actualData.status === 'laying'">{{ statusLabels.laying }}</span>
                                            <span class="badge bg-secondary" v-else>{{ statusLabels.disposed }}</span>
                                        </td>
                                        <td>
                                            <router-link :to="'/admin/lotes/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/lotes/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="11" align="center">Nenhum resultado encontrado</td>
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
                        Ao apagar este lote, irá apagar todos os registros relacionados a ele.
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
