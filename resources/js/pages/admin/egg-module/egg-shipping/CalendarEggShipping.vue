<script setup>

import axios from 'axios';
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import ptLocale from '@fullcalendar/core/locales/pt';
import VueFeather from 'vue-feather';
import moment from 'moment';

const router = useRouter();
const loadingEvents = ref(true);
const loadingModal = ref(true);
const shipping = ref({});
const monthCount = ref(0);

const loadEvents = (info, successCallback, failureCallback) => {
    loadingEvents.value = true;

    axios.get('/admin/egg-shipping/calendar-events', {
        params: {
            start: info.startStr,
            end: info.endStr,
        },
    }).then((response) => {
        monthCount.value = response.data.length;
        successCallback(response.data);
    }).catch(() => {
        if (failureCallback) {
            failureCallback();
        }
    }).finally(() => {
        loadingEvents.value = false;
    });
};

const calendarOptions = reactive({
    plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay',
    },
    locale: ptLocale,
    initialView: 'dayGridMonth',
    height: 'auto',
    events: loadEvents,
    eventClick(info) {
        openModal(info.event.id);
        info.el.style.borderColor = '#1cbb8c';
    },
});

const openModal = (id) => {
    loadingModal.value = true;
    $('#modalShippingCalendar').modal('show');

    axios.get(`/admin/egg-shipping/${id}`)
        .then((response) => {
            shipping.value = response.data;
            loadingModal.value = false;
        }).catch(() => {
            $('#modalShippingCalendar').modal('hide');
            loadingModal.value = true;
        });
};

const goToDetail = () => {
    if (shipping.value.id) {
        router.push({ path: `/admin/expedicao-ovos/${shipping.value.id}` });
    }
};

</script>

<template>
    <div>
        <h1 class="h3 mb-3">Calendário de Expedições</h1>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body py-3">
                        <h6 class="text-primary mb-1">No período visível</h6>
                        <p class="mb-0"><strong>{{ monthCount }}</strong> expedição(ões)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body py-3">
                        <span class="badge me-2" style="background-color: #3b7ddd;">Expedição</span>
                        <span class="badge" style="background-color: #1cbb8c;">Hoje</span>
                        <small class="text-muted ms-2">Clique num evento para ver detalhes</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <router-link to="/admin/expedicao-ovos" class="btn btn-pill btn-primary mt-3">
                    <vue-feather type="arrow-left"></vue-feather>Voltar à lista
                </router-link>
                <router-link to="/admin/expedicao-ovos/create" class="btn btn-pill btn-success mt-3 ms-2">
                    <vue-feather type="plus"></vue-feather>Nova expedição
                </router-link>
            </div>
            <div class="card-body position-relative">
                <div v-if="loadingEvents" class="text-center py-4">
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                    <p class="mt-2 mb-0 text-muted">Carregando expedições...</p>
                </div>
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>
    </div>

    <div class="modal" id="modalShippingCalendar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes da Expedição</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="!loadingModal">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Transporte</h6>
                                <p><strong>Data:</strong> {{ moment(shipping.shipping_date).format('DD-MM-YYYY') }}</p>
                                <p><strong>Fatura:</strong> {{ shipping.invoice_number }}</p>
                                <p><strong>Transportadora:</strong> {{ shipping.carrier }}</p>
                                <p><strong>Motorista:</strong> {{ shipping.driver_name }}</p>
                                <p><strong>Matrícula:</strong> {{ shipping.vehicle_plate }}</p>
                                <p><strong>Temperatura:</strong> {{ shipping.vehicle_temperature ?? '—' }} °C</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Pedido</h6>
                                <p><strong>Cliente:</strong> {{ shipping.order?.customer_name || '—' }}</p>
                                <p><strong>Categoria:</strong> {{ shipping.order?.category?.name || '—' }}</p>
                                <p><strong>Dúzias:</strong> {{ shipping.order?.quantity_dozens || '—' }}</p>
                                <p><strong>Responsável:</strong> {{ shipping.responsible?.name || '—' }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="d-flex justify-content-center py-4">
                            <div class="spinner-border" role="status"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" @click="goToDetail" :disabled="loadingModal || !shipping.id">
                        Ver expedição completa
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
