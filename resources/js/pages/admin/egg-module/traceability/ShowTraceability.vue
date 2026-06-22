<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';

const loadingDiv = ref(true);
const result = ref(null);
const router = useRouter();
const toastr = useToastr();

const qualityLabels = {
    clean: 'Limpo',
    dirty: 'Sujo',
    cracked: 'Rachado',
    deformed: 'Deformado',
};

const packageTypeLabels = {
    tray: 'Bandeja',
    box: 'Caixa',
};

const destinationLabels = {
    packaged: 'Embalado',
    reject: 'Refugo',
    broken: 'Partido',
};

const statusLabels = {
    available: 'Disponível',
    reserved: 'Reservado',
    shipped: 'Expedido',
};

const getData = () => {
    const code = decodeURIComponent(router.currentRoute.value.params.code);

    axios.get('/admin/traceability/search', { params: { code } })
        .then((response) => {
            loadingDiv.value = false;
            result.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Registo não encontrado');
            router.push({ path: '/admin/rastreabilidade' });
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv && result">
        <h1 class="h3 mb-3">Rastreabilidade</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            {{ result.type === 'egg' ? 'Ovo' : 'Embalagem' }}:
                            {{ result.type === 'egg' ? result.data.traceability_code : result.data.qr_code }}
                        </h5>
                        <router-link to="/admin/rastreabilidade" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <template v-if="result.type === 'egg'">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Ovo</h6>
                                    <p><strong>Código:</strong> {{ result.traceability_chain.egg?.code }}</p>
                                    <p><strong>Postura:</strong> {{ moment(result.traceability_chain.egg?.lay_date).format('DD-MM-YYYY') }}</p>
                                    <p><strong>Qualidade:</strong> {{ qualityLabels[result.traceability_chain.egg?.quality] || result.traceability_chain.egg?.quality }}</p>
                                    <p><strong>Categoria:</strong> {{ result.traceability_chain.egg?.category || '-' }}</p>
                                    <p><strong>Destino:</strong> {{ destinationLabels[result.traceability_chain.egg?.destination] || result.traceability_chain.egg?.destination }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Origem</h6>
                                    <p><strong>Lote:</strong> {{ result.traceability_chain.flock?.code || '-' }}</p>
                                    <p><strong>Linhagem:</strong> {{ result.traceability_chain.flock?.lineage || '-' }}</p>
                                    <p><strong>Alojamento:</strong> {{ moment(result.traceability_chain.flock?.housing_date).format('DD-MM-YYYY') || '-' }}</p>
                                    <p><strong>Galpão:</strong> {{ result.traceability_chain.house?.name || '-' }}</p>
                                    <p><strong>Granja:</strong> {{ result.traceability_chain.house?.farm || '-' }}</p>
                                </div>
                            </div>

                            <hr v-if="result.traceability_chain.classification || result.traceability_chain.inventory">

                            <div class="row" v-if="result.traceability_chain.classification">
                                <div class="col-md-6">
                                    <h6>Classificação</h6>
                                    <p><strong>Data:</strong> {{ moment(result.traceability_chain.classification.date).format('DD-MM-YYYY') }}</p>
                                    <p><strong>% Refugo:</strong> {{ result.traceability_chain.classification.reject_percentage }}%</p>
                                </div>
                            </div>

                            <div class="row mt-2" v-if="result.traceability_chain.inventory">
                                <div class="col-md-6">
                                    <h6>Estoque</h6>
                                    <p><strong>Quantidade:</strong> {{ result.traceability_chain.inventory.quantity }}</p>
                                    <p><strong>Estado:</strong> {{ statusLabels[result.traceability_chain.inventory.status] || result.traceability_chain.inventory.status }}</p>
                                    <p><strong>Localização:</strong> {{ result.traceability_chain.inventory.location || '-' }}</p>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Embalagem</h6>
                                    <p><strong>QR Code:</strong> {{ result.traceability_chain.packaging?.qr_code }}</p>
                                    <p><strong>Tipo:</strong> {{ packageTypeLabels[result.traceability_chain.packaging?.type] || result.traceability_chain.packaging?.type }}</p>
                                    <p><strong>Ovos:</strong> {{ result.traceability_chain.packaging?.packaged_eggs }}</p>
                                    <p><strong>Validade:</strong> {{ moment(result.traceability_chain.packaging?.expiry_date).format('DD-MM-YYYY') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Classificação</h6>
                                    <p><strong>Data:</strong> {{ moment(result.traceability_chain.classification?.date).format('DD-MM-YYYY') }}</p>
                                    <p><strong>Ovos lavados:</strong> {{ result.traceability_chain.classification?.washed_eggs }}</p>
                                    <p><strong>% Refugo:</strong> {{ result.traceability_chain.classification?.reject_percentage }}%</p>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Origem</h6>
                                    <p><strong>Lote:</strong> {{ result.traceability_chain.flock?.code || '-' }}</p>
                                    <p><strong>Linhagem:</strong> {{ result.traceability_chain.flock?.lineage || '-' }}</p>
                                    <p><strong>Galpão:</strong> {{ result.traceability_chain.house?.name || '-' }}</p>
                                    <p><strong>Granja:</strong> {{ result.traceability_chain.house?.farm || '-' }}</p>
                                </div>
                            </div>
                        </template>
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
