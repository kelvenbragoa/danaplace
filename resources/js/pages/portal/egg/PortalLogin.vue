<script setup>

import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const portalCode = ref('');
const loading = ref(false);
const errorMessage = ref('');
const router = useRouter();

const handleLogin = () => {
    loading.value = true;
    errorMessage.value = '';

    axios.post('/portal/ovos/login', {
        portal_code: portalCode.value.trim().toUpperCase(),
    }).then(() => {
        router.push({ path: '/portal/pedidos-ovos/pedidos' });
    }).catch((error) => {
        errorMessage.value = error.response?.data?.message || 'Código de acesso inválido ou cliente inativo.';
    }).finally(() => {
        loading.value = false;
    });
};

</script>

<template>
    <div class="form-body portal-login">
        <div class="website-logo">
            <a href="#">
                <div class="logo"></div>
            </a>
        </div>
        <div class="row">
            <div class="img-holder">
                <div class="bg"></div>
                <div class="info-holder"></div>
            </div>
            <div class="form-holder">
                <div class="form-content">
                    <div class="form-items align-center">
                        <img src="/files/img/sys/companylogo.png" alt="M+D InoGest" style="width: 100%;">

                        <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show with-icon" role="alert">
                            {{ errorMessage }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="errorMessage = ''">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <p class="lead text-center mt-3 mb-1">Portal de Pedidos</p>
                        <p class="text-muted text-center mb-4" style="font-size: 14px;">Produção Avícola — Ovos</p>

                        <form @submit.prevent="handleLogin">
                            <input
                                v-model="portalCode"
                                class="form-control text-uppercase"
                                type="text"
                                name="portal_code"
                                placeholder="Código de acesso (ex: OVOS-XXXXXXXX)"
                                required
                                autocomplete="off"
                            >
                            <div class="form-button">
                                <button id="submit" type="submit" class="ibtn" :disabled="loading">
                                    <span v-if="loading">Por Favor Aguarde ...</span>
                                    <span v-else>Entrar</span>
                                </button>
                            </div>
                        </form>

                        <div class="form-footer portal-login-footer">
                            <div class="text-muted text-center">
                                <small>Desenvolvido pela INOVATIS MZ LTD</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/* Corrige layout fixo 1280px do iofrm-theme3 em ecrãs normais */
.portal-login.form-body {
    overflow-x: hidden;
}

.portal-login.form-body > .row {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: stretch;
    height: 100%;
    width: 100%;
    position: relative;
}

.portal-login .img-holder {
    position: relative !important;
    flex: 1 1 58%;
    width: auto !important;
    max-width: none;
    min-height: 100vh;
    height: auto !important;
    z-index: 1;
}

.portal-login .form-holder {
    flex: 0 0 42%;
    width: auto !important;
    max-width: 480px;
    min-width: 300px;
    margin-left: 0 !important;
    z-index: 2;
}

.portal-login .form-holder .form-content {
    min-height: 100vh;
    display: flex;
    align-items: center;
}

.portal-login .portal-login-footer {
    margin-top: 2rem !important;
}

@media (max-width: 992px) {
    .portal-login.form-body > .row {
        flex-direction: column;
    }

    .portal-login .img-holder {
        display: block !important;
        flex: 0 0 240px;
        min-height: 240px;
        width: 100% !important;
    }

    .portal-login .form-holder {
        flex: 1 1 auto;
        max-width: none;
        min-width: 0;
        width: 100% !important;
    }

    .portal-login .form-holder .form-content {
        min-height: auto;
        padding: 60px 30px 40px;
    }
}
</style>
