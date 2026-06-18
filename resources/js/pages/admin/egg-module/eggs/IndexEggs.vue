<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const qualityFilter = ref('');
const destinationFilter = ref('');
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);

const qualityLabels = {
    clean: 'Limpo',
    dirty: 'Sujo',
    cracked: 'Rachado',
    deformed: 'Deformado',
};

const destinationLabels = {
    packaged: 'Embalado',
    reject: 'Refugo',
    broken: 'Partido',
};

const getData = async (page = 1) => {
    axios.get(`/admin/eggs?page=${page}`, {
        params: {
            query: searchQuery.value,
            quality: qualityFilter.value || undefined,
            destination: destinationFilter.value || undefined,
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

    axios.delete(`/admin/eggs/${dataIdBeingDeleted.value}`)
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

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch([qualityFilter, destinationFilter], () => {
    getData();
});

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tabela de ovos. {{ retriviedData.total }} registros encontrados.</h5>

                        <router-link to="/admin/ovos/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Adicionar
                        </router-link>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar rastreio ou lote..." aria-label="Search">
                            </div>
                        </form>

                        <select class="form-control mt-3 mr-2" style="max-width: 200px; display: inline-block;" v-model="qualityFilter">
                            <option value="">Todas qualidades</option>
                            <option value="clean">Limpo</option>
                            <option value="dirty">Sujo</option>
                            <option value="cracked">Rachado</option>
                            <option value="deformed">Deformado</option>
                        </select>

                        <select class="form-control mt-3" style="max-width: 200px; display: inline-block;" v-model="destinationFilter">
                            <option value="">Todos destinos</option>
                            <option value="packaged">Embalado</option>
                            <option value="reject">Refugo</option>
                            <option value="broken">Partido</option>
                        </select>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Rastreio</th>
                                        <th>Lote</th>
                                        <th>Postura</th>
                                        <th>Categoria</th>
                                        <th>Qualidade</th>
                                        <th>Destino</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ actualData.traceability_code }}</td>
                                        <td>{{ actualData.flock?.code || '-' }}</td>
                                        <td>{{ actualData.lay_date }}</td>
                                        <td>{{ actualData.category?.name || '-' }}</td>
                                        <td>{{ qualityLabels[actualData.quality] || actualData.quality }}</td>
                                        <td>{{ destinationLabels[actualData.destination] || actualData.destination }}</td>
                                        <td>
                                            <router-link :to="'/admin/ovos/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/ovos/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
                                            <a href="#" @click.prevent="confirmDeletion(actualData)"><vue-feather type="trash"></vue-feather></a>
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
                        Ao apagar este ovo, o estoque e rastreabilidade relacionados podem ser afetados.
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
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                </div>
                <br>
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
