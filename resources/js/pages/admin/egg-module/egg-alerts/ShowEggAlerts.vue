<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const loadingRead = ref(false);
const loadingResolve = ref(false);
const router = useRouter();
const toastr = useToastr();

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

const getData = () => {
    axios.get(`/admin/egg-alerts/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
            toastr.error('Alerta não encontrado');
            router.push({ path: '/admin/alertas-ovos' });
        });
};

const markAsRead = () => {
    loadingRead.value = true;

    axios.post(`/admin/egg-alerts/${retrievedData.value.id}/mark-as-read`)
        .then((response) => {
            retrievedData.value = response.data;
            toastr.success('Marcado como lido');
        }).catch(() => {
            toastr.error('Erro ao atualizar');
        }).finally(() => {
            loadingRead.value = false;
        });
};

const markAsResolved = () => {
    loadingResolve.value = true;

    axios.post(`/admin/egg-alerts/${retrievedData.value.id}/mark-as-resolved`)
        .then((response) => {
            retrievedData.value = response.data;
            toastr.success('Marcado como resolvido');
        }).catch(() => {
            toastr.error('Erro ao atualizar');
        }).finally(() => {
            loadingResolve.value = false;
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Alerta</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ retrievedData.title }}</h5>
                        <router-link to="/admin/alertas-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p><strong>Tipo:</strong> {{ typeLabels[retrievedData.type] || retrievedData.type }}</p>
                        <p><strong>Estado:</strong> {{ statusLabels[retrievedData.status] || retrievedData.status }}</p>
                        <p><strong>Data:</strong> {{ moment(retrievedData.alert_datetime).format('DD-MM-YYYY') }}</p>
                        <p><strong>Lote:</strong> {{ retrievedData.flock?.code || '-' }}</p>
                        <p><strong>Galpão:</strong> {{ retrievedData.flock?.house?.name || '-' }}</p>
                        <p><strong>Lido em:</strong> {{ moment(retrievedData.read_datetime).format('DD-MM-YYYY') || '-' }}</p>
                        <p><strong>Resolvido em:</strong> {{ moment(retrievedData.resolved_datetime).format('DD-MM-YYYY') || '-' }}</p>
                        <hr>
                        <p><strong>Mensagem:</strong></p>
                        <p>{{ retrievedData.message }}</p>

                        <div class="mt-3" v-if="retrievedData.status === 'sent'">
                            <button class="btn btn-info mr-2" @click.prevent="markAsRead" :disabled="loadingRead">
                                <div v-if="loadingRead" class="spinner-border spinner-border-sm" role="status"></div>
                                <span v-else>Marcar como lido</span>
                            </button>
                        </div>
                        <div class="mt-2" v-if="retrievedData.status !== 'resolved'">
                            <button class="btn btn-success" @click.prevent="markAsResolved" :disabled="loadingResolve">
                                <div v-if="loadingResolve" class="spinner-border spinner-border-sm" role="status"></div>
                                <span v-else>Marcar como resolvido</span>
                            </button>
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
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                </div>
                <br>
                <div class="d-flex justify-content-center">Carregando Dados...</div>
            </div>
        </div>
    </div>
</template>
