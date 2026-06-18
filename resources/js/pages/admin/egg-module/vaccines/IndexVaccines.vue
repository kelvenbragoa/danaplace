<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';

const toastr = useToastr();
const searchQuery = ref(null);
const expiringSoon = ref([]);
const loadingDiv = ref(true);
const loadingButtonDelete = ref(false);

const retriviedData = ref({ data: [] });
const dataIdBeingDeleted = ref(null);

const isExpired = (date) => new Date(date) < new Date(new Date().toDateString());

const isExpiringSoon = (date) => {
    const expiry = new Date(date);
    const today = new Date();
    const limit = new Date();
    limit.setDate(today.getDate() + 30);
    return expiry >= today && expiry <= limit;
};

const getExpiringSoon = () => {
    axios.get('/admin/vaccines/expiring-soon').then((response) => {
        expiringSoon.value = response.data;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/vaccines?page=${page}`, {
        params: { query: searchQuery.value }
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

    axios.delete(`/admin/vaccines/${dataIdBeingDeleted.value}`)
        .then(() => {
            retriviedData.value.data = retriviedData.value.data.filter(data => data.id !== dataIdBeingDeleted.value);
            $('#deleteModal').modal('hide');
            toastr.success('Registro apagado com sucesso');
            getExpiringSoon();
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

onMounted(() => {
    getExpiringSoon();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Vacinas</h1>

        <div class="row mb-3" v-if="expiringSoon.length > 0">
            <div class="col-md-12">
                <div class="card border-warning">
                    <div class="card-body">
                        <h6 class="text-warning mb-2">Vacinas a expirar nos próximos 30 dias</h6>
                        <p class="mb-0"><strong>{{ expiringSoon.length }}</strong> vacina(s) próxima(s) do vencimento</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Tabela de vacinas. {{ retriviedData.total }} registros encontrados.</h5>

                        <router-link to="/admin/vacinas/create" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="plus"></vue-feather>Adicionar
                        </router-link>

                        <br>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar nome, fabricante ou lote..." aria-label="Search">
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Fabricante</th>
                                        <th>Lote</th>
                                        <th>Validade</th>
                                        <th>Stock Mín.</th>
                                        <th>Agendamentos</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(actualData, index) in retriviedData.data" :key="actualData.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ actualData.name }}</td>
                                        <td>{{ actualData.manufacturer }}</td>
                                        <td>{{ actualData.batch }}</td>
                                        <td>
                                            <span :class="{'text-danger': isExpired(actualData.expiry_date), 'text-warning': isExpiringSoon(actualData.expiry_date)}">
                                                {{ actualData.expiry_date }}
                                            </span>
                                        </td>
                                        <td>{{ actualData.min_stock }}</td>
                                        <td>{{ actualData.vaccination_schedule_count }}</td>
                                        <td>
                                            <router-link :to="'/admin/vacinas/' + actualData.id + '/edit'"><vue-feather type="edit-2"></vue-feather></router-link>
                                            <router-link :to="'/admin/vacinas/' + actualData.id"><vue-feather type="eye"></vue-feather></router-link>
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
                        Ao apagar esta vacina, os agendamentos relacionados podem ser afetados.
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
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
