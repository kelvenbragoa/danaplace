<script setup>

import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import VueFeather from 'vue-feather';

const retrievedData = ref({});
const loadingDiv = ref(true);
const router = useRouter();

const getData = () => {
    axios.get(`/admin/egg-customers/${router.currentRoute.value.params.id}`)
        .then((response) => {
            loadingDiv.value = false;
            retrievedData.value = response.data;
        }).catch(() => {
            loadingDiv.value = false;
        });
};

const portalUrl = `${window.location.origin}/portal/pedidos-ovos`;

onMounted(() => {
    getData();
});

</script>

<template>
    <div v-if="!loadingDiv">
        <h1 class="h3 mb-3">Cliente de Ovos</h1>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Cliente: {{ retrievedData.name }}</h5>
                        <router-link to="/admin/clientes-ovos" class="btn btn-pill btn-primary mt-3">
                            <vue-feather type="arrow-left"></vue-feather>Voltar
                        </router-link>
                    </div>

                    <div class="card-body">
                        <p><strong>Nome:</strong> {{ retrievedData.name }}</p>
                        <p><strong>NUIT:</strong> {{ retrievedData.tax_id || '—' }}</p>
                        <p><strong>Email:</strong> {{ retrievedData.email || '—' }}</p>
                        <p><strong>Telefone:</strong> {{ retrievedData.phone || '—' }}</p>
                        <p><strong>Morada:</strong> {{ retrievedData.address || '—' }}</p>
                        <p><strong>Código Portal:</strong> <code>{{ retrievedData.portal_code }}</code></p>
                        <p><strong>URL Portal:</strong> <a :href="portalUrl" target="_blank">{{ portalUrl }}</a></p>
                        <p><strong>Pedidos:</strong> {{ retrievedData.orders_count ?? 0 }}</p>
                        <p>
                            <strong>Estado:</strong>
                            <span class="badge" :class="retrievedData.is_active ? 'badge-success' : 'badge-secondary'">
                                {{ retrievedData.is_active ? 'Ativo' : 'Inativo' }}
                            </span>
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
