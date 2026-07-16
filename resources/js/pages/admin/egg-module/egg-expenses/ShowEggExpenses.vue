<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';
import moment from 'moment';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const formatMoney = (value) => {
    return Number(value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getData = () => {
    axios.get(`/admin/egg-expenses/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
        });
};

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Despesa de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Despesa: {{ retrievedData.title }}</h5>
                        <router-link to="/admin/despesas-ovos" class="btn btn-pill btn-primary mt-3 me-2">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                        <router-link :to="'/admin/despesas-ovos/' + retrievedData.id + '/edit'" class="btn btn-pill btn-secondary mt-3">
                            <vue-feather type="edit-2"></vue-feather>Editar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Título:</strong> {{ retrievedData.title }}</p>
                                <p><strong>Categoria:</strong> {{ retrievedData.category_label }}</p>
                                <p><strong>Valor:</strong> {{ formatMoney(retrievedData.amount) }}</p>
                                <p><strong>Data:</strong> {{ moment(retrievedData.expense_date).format('DD-MM-YYYY') }}</p>
                                <p><strong>Método de pagamento:</strong> {{ retrievedData.payment_method_label || '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Granja:</strong> {{ retrievedData.farm?.name || '-' }}</p>
                                <p><strong>Galpão:</strong> {{ retrievedData.house?.name || '-' }}</p>
                                <p><strong>Lote:</strong> {{ retrievedData.flock?.code || '-' }}</p>
                                <p><strong>Fornecedor:</strong> {{ retrievedData.vendor_name || '-' }}</p>
                                <p><strong>Nº Fatura:</strong> {{ retrievedData.invoice_number || '-' }}</p>
                            </div>
                        </div>

                        <hr>

                        <p><strong>Descrição:</strong></p>
                        <p>{{ retrievedData.description || '-' }}</p>

                        <p><strong>Notas:</strong></p>
                        <p>{{ retrievedData.notes || '-' }}</p>

                        <p class="text-muted mb-0">
                            <small>
                                Registado por: {{ retrievedData.created_by?.name || '-' }}
                                · {{ retrievedData.created_at ? moment(retrievedData.created_at).format('DD-MM-YYYY HH:mm') : '-' }}
                            </small>
                        </p>
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
