<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useToastr } from '../../../../toastr';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const loadingApply = ref(false);
const loadingCancel = ref(false);
const applicationDate = ref('');
const router = useRouter();
const toastr = useToastr();

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

const getData = () => {
    axios.get(`/admin/vaccination-schedule/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
        });
};

const openApplyModal = () => {
    applicationDate.value = new Date().toISOString().split('T')[0];
    $('#applyModal').modal('show');
};

const applyVaccination = () => {
    if (!applicationDate.value) {
        toastr.error('Informe a data de aplicação');
        return;
    }

    loadingApply.value = true;

    axios.post(`/admin/vaccination-schedule/${retrievedData.value.id}/apply`, {
        application_date: applicationDate.value,
    }).then((response) => {
        retrievedData.value = response.data;
        $('#applyModal').modal('hide');
        toastr.success('Vacinação aplicada com sucesso');
        getData();
    }).catch(() => {
        toastr.error('Erro ao aplicar vacinação');
    }).finally(() => {
        loadingApply.value = false;
    });
};

const cancelVaccination = () => {
    loadingCancel.value = true;

    axios.post(`/admin/vaccination-schedule/${retrievedData.value.id}/cancel`)
        .then((response) => {
            retrievedData.value = response.data;
            toastr.success('Vacinação cancelada com sucesso');
            getData();
        }).catch(() => {
            toastr.error('Erro ao cancelar vacinação');
        }).finally(() => {
            loadingCancel.value = false;
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Calendário Vacinal</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Vacinação: {{ retrievedData.vaccine?.name }}</h5>
                        <router-link to="/admin/calendario-vacinal" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                        <button
                            v-if="retrievedData.status === 'pending'"
                            @click.prevent="openApplyModal"
                            class="btn btn-pill btn-success mt-3 ml-2"
                        >
                            Aplicar Vacinação
                        </button>
                        <button
                            v-if="retrievedData.status === 'pending'"
                            @click.prevent="cancelVaccination"
                            class="btn btn-pill btn-secondary mt-3 ml-2"
                            :disabled="loadingCancel"
                        >
                            Cancelar Agendamento
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Lote:</strong> {{ retrievedData.flock?.code || '-' }}</p>
                                <p><strong>Galpão:</strong> {{ retrievedData.flock?.house?.name || '-' }}</p>
                                <p><strong>Granja:</strong> {{ retrievedData.flock?.house?.farm?.name || '-' }}</p>
                                <p><strong>Vacina:</strong> {{ retrievedData.vaccine?.name || '-' }}</p>
                                <p><strong>Lote da Vacina:</strong> {{ retrievedData.vaccine?.batch || '-' }}</p>
                                <p><strong>Fabricante:</strong> {{ retrievedData.vaccine?.manufacturer || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Data Prevista:</strong> {{ moment(retrievedData.scheduled_date).format('DD-MM-YYYY') }}</p>
                                <p><strong>Data de Aplicação:</strong> {{ moment(retrievedData.application_date).format('DD-MM-YYYY') || '-' }}</p>
                                <p><strong>Via:</strong> {{ routeLabels[retrievedData.administration_route] || retrievedData.administration_route }}</p>
                                <p><strong>Dosagem:</strong> {{ retrievedData.dosage || '-' }}</p>
                                <p><strong>Responsável:</strong> {{ retrievedData.responsible?.name || '-' }}</p>
                                <p>
                                    <strong>Estado:</strong>
                                    <span class="badge bg-warning" v-if="retrievedData.status === 'pending'">{{ statusLabels.pending }}</span>
                                    <span class="badge bg-success" v-else-if="retrievedData.status === 'applied'">{{ statusLabels.applied }}</span>
                                    <span class="badge bg-secondary" v-else>{{ statusLabels.canceled }}</span>
                                </p>
                            </div>
                        </div>

                        <p v-if="retrievedData.observations"><strong>Observações:</strong> {{ retrievedData.observations }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal" id="applyModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Aplicar vacinação</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label" for="applicationDate">Data de aplicação</label>
                        <input type="date" class="form-control" id="applicationDate" v-model="applicationDate"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button @click.prevent="applyVaccination" type="button" class="btn btn-success" :disabled="loadingApply">
                            <div v-if="loadingApply" class="spinner-border spinner-border-sm" role="status"></div>
                            <span v-else>Confirmar aplicação</span>
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
