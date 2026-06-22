<script setup>

import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { useToastr } from '../../../../toastr';
import { debounce } from 'lodash';
import { Bootstrap4Pagination } from 'laravel-vue-pagination';
import VueFeather from 'vue-feather';
import { useRouter } from 'vue-router';
import moment from 'moment';

const toastr = useToastr();
const router = useRouter();
const searchQuery = ref(null);
const codeSearch = ref('');
const flockFilter = ref('');
const flocks = ref([]);
const loadingDiv = ref(true);
const loadingSearch = ref(false);
const loadingFlock = ref(false);
const loadingExport = ref(false);
const searchResult = ref(null);
const flockSummary = ref(null);

const startDate = ref('');
const endDate = ref('');

const retriviedData = ref({ data: [] });

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

const getFlocks = () => {
    axios.get('/admin/flocks-all').then((response) => {
        flocks.value = response.data;
    });
};

const getData = async (page = 1) => {
    axios.get(`/admin/traceability?page=${page}`, {
        params: {
            query: searchQuery.value,
            flock_id: flockFilter.value || undefined,
        }
    }).then((response) => {
        retriviedData.value = response.data;
        loadingDiv.value = false;
    });
};

const searchByCode = () => {
    if (!codeSearch.value.trim()) {
        toastr.error('Informe um código de rastreio ou QR');
        return;
    }

    loadingSearch.value = true;
    searchResult.value = null;

    axios.get('/admin/traceability/search', { params: { code: codeSearch.value.trim() } })
        .then((response) => {
            searchResult.value = response.data;
        }).catch(() => {
            toastr.error('Nenhum registo encontrado para este código');
        }).finally(() => {
            loadingSearch.value = false;
        });
};

const loadFlockSummary = () => {
    if (!flockFilter.value) {
        flockSummary.value = null;
        return;
    }

    loadingFlock.value = true;

    axios.get(`/admin/traceability/by-flock/${flockFilter.value}`)
        .then((response) => {
            flockSummary.value = response.data;
        }).catch(() => {
            toastr.error('Erro ao carregar resumo do lote');
        }).finally(() => {
            loadingFlock.value = false;
        });
};

const exportData = () => {
    loadingExport.value = true;

    axios.get('/admin/traceability/export', {
        params: {
            start_date: startDate.value || undefined,
            end_date: endDate.value || undefined,
        }
    }).then((response) => {
        const blob = new Blob([JSON.stringify(response.data, null, 2)], { type: 'application/json' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `rastreabilidade-${new Date().toISOString().split('T')[0]}.json`;
        link.click();
        window.URL.revokeObjectURL(url);
        toastr.success(`${response.data.length} registos exportados`);
    }).catch(() => {
        toastr.error('Erro ao exportar');
    }).finally(() => {
        loadingExport.value = false;
    });
};

const openDetail = (code) => {
    router.push({ path: `/admin/rastreabilidade/detalhe/${encodeURIComponent(code)}` });
};

watch(searchQuery, debounce(() => {
    getData();
}, 300));

watch(flockFilter, () => {
    getData();
    loadFlockSummary();
});

onMounted(() => {
    getFlocks();
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Rastreabilidade</h1>

        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pesquisar por código</h5>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" class="form-control" v-model="codeSearch" placeholder="Código de rastreio ou QR de embalagem..." @keyup.enter="searchByCode">
                            <div class="input-group-append">
                                <button class="btn btn-primary" @click.prevent="searchByCode" :disabled="loadingSearch">
                                    <div v-if="loadingSearch" class="spinner-border spinner-border-sm" role="status"></div>
                                    <span v-else><vue-feather type="search"></vue-feather> Pesquisar</span>
                                </button>
                            </div>
                        </div>

                        <div v-if="searchResult" class="mt-3 p-3 border rounded bg-light">
                            <h6>
                                Resultado: {{ searchResult.type === 'egg' ? 'Ovo' : 'Embalagem' }}
                                <button class="btn btn-sm btn-link" @click.prevent="openDetail(searchResult.type === 'egg' ? searchResult.data.traceability_code : searchResult.data.qr_code)">
                                    Ver detalhe completo
                                </button>
                            </h6>
                            <pre class="mb-0 small">{{ JSON.stringify(searchResult.traceability_chain, null, 2) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3" v-if="flockSummary">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-body">
                        <h6 class="text-info">Resumo do lote {{ flockSummary.flock?.code }}</h6>
                        <p class="mb-1"><strong>Total ovos:</strong> {{ flockSummary.total_eggs }}</p>
                        <p class="mb-0"><strong>Granja:</strong> {{ flockSummary.flock?.house?.farm?.name || '-' }} — <strong>Galpão:</strong> {{ flockSummary.flock?.house?.name || '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Registos de rastreabilidade. {{ retriviedData.total }} encontrados.</h5>

                        <form class="d-none d-sm-inline-block mt-3">
                            <div class="input-group input-group-navbar">
                                <input type="text" class="form-control" v-model="searchQuery" placeholder="Procurar rastreio ou lote..." aria-label="Search">
                            </div>
                        </form>

                        <select class="form-control mt-3" style="max-width: 220px;" v-model="flockFilter">
                            <option value="">Todos os lotes</option>
                            <option v-for="flock in flocks" :key="flock.id" :value="flock.id">{{ flock.code }}</option>
                        </select>

                        <div class="mt-3 d-flex flex-wrap align-items-end">
                            <div class="mr-2 mb-2">
                                <label class="form-label small mb-0">Exportar de</label>
                                <input type="date" class="form-control form-control-sm" v-model="startDate">
                            </div>
                            <div class="mr-2 mb-2">
                                <label class="form-label small mb-0">até</label>
                                <input type="date" class="form-control form-control-sm" v-model="endDate">
                            </div>
                            <button class="btn btn-outline-secondary mb-2" @click.prevent="exportData" :disabled="loadingExport">
                                <vue-feather type="download"></vue-feather> Exportar JSON
                            </button>
                        </div>
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
                                        <th>Granja</th>
                                        <th>Qualidade</th>
                                        <th>Destino</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody v-if="retriviedData.data.length > 0">
                                    <tr v-for="(item, index) in retriviedData.data" :key="item.id">
                                        <td>#{{ index + 1 }}</td>
                                        <td>{{ item.traceability_code }}</td>
                                        <td>{{ item.flock?.code || '-' }}</td>
                                        <td>{{ moment(item.lay_date).format('DD-MM-YYYY') }}</td>
                                        <td>{{ item.flock?.house?.farm?.name || '-' }}</td>
                                        <td>{{ qualityLabels[item.quality] || item.quality }}</td>
                                        <td>{{ destinationLabels[item.destination] || item.destination }}</td>
                                        <td>
                                            <a href="#" @click.prevent="openDetail(item.traceability_code)"><vue-feather type="eye"></vue-feather></a>
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
