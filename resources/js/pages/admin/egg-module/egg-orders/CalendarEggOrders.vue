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
const order = ref({});
const monthCount = ref(0);

const statusLabels = {
    pending: 'Pendente',
    approved: 'Aprovado',
    picked: 'Separado',
    shipped: 'Expedido',
    canceled: 'Cancelado',
};

const statusBadgeClass = {
    pending: 'bg-warning text-dark',
    approved: 'bg-info text-dark',
    picked: 'bg-primary',
    shipped: 'bg-success',
    canceled: 'bg-secondary',
};

const loadEvents = (info, successCallback, failureCallback) => {
    loadingEvents.value = true;

    axios.get('/admin/egg-orders/calendar-events', {
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
    $('#modalOrderCalendar').modal('show');

    axios.get(`/admin/egg-orders/${id}`)
        .then((response) => {
            order.value = response.data;
            loadingModal.value = false;
        }).catch(() => {
            $('#modalOrderCalendar').modal('hide');
            loadingModal.value = true;
        });
};

const goToDetail = () => {
    if (order.value.id) {
        router.push({ path: `/admin/pedidos/${order.value.id}` });
    }
};

const formatTotal = () => {
    if (!order.value.unit_price) return '—';
    return (order.value.quantity_dozens * order.value.unit_price).toFixed(2);
};

</script>

<template>
    <div>
        <h1 class="h3 mb-3">Calendário de Pedidos</h1>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body py-3">
                        <h6 class="text-primary mb-1">No período visível</h6>
                        <p class="mb-0"><strong>{{ monthCount }}</strong> entrega(s) prevista(s)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body py-3">
                        <span class="badge bg-warning text-dark me-1">Pendente</span>
                        <span class="badge bg-info text-dark me-1">Aprovado</span>
                        <span class="badge bg-primary me-1">Separado</span>
                        <span class="badge bg-success me-1">Expedido</span>
                        <span class="badge me-1" style="background-color: #1cbb8c;">Hoje</span>
                        <small class="text-muted ms-2">Clique num evento para ver detalhes</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <router-link to="/admin/pedidos" class="btn btn-pill btn-primary mt-3">
                    <vue-feather type="arrow-left"></vue-feather>Voltar à lista
                </router-link>
                <router-link to="/admin/pedidos/create" class="btn btn-pill btn-success mt-3 ms-2">
                    <vue-feather type="plus"></vue-feather>Novo pedido
                </router-link>
            </div>
            <div class="card-body position-relative">
                <div v-if="loadingEvents" class="text-center py-4">
                    <div class="spinner-border" role="status"><span class="sr-only"></span></div>
                    <p class="mt-2 mb-0 text-muted">Carregando pedidos...</p>
                </div>
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>
    </div>

    <div class="modal" id="modalOrderCalendar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalhes do Pedido</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="!loadingModal">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Cliente</h6>
                                <p><strong>Nome:</strong> {{ order.customer_name || '—' }}</p>
                                <p><strong>Telefone:</strong> {{ order.customer_phone || '—' }}</p>
                                <p><strong>Email:</strong> {{ order.customer_email || '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Pedido</h6>
                                <p><strong>Data do pedido:</strong> {{ order.order_date ? moment(order.order_date).format('DD-MM-YYYY') : '—' }}</p>
                                <p><strong>Entrega prevista:</strong> {{ order.expected_delivery_date ? moment(order.expected_delivery_date).format('DD-MM-YYYY') : '—' }}</p>
                                <p><strong>Categoria:</strong> {{ order.category?.name || '—' }}</p>
                                <p><strong>Quantidade:</strong> {{ order.quantity_dozens || '—' }}</p>
                                <p><strong>Total:</strong> {{ formatTotal() }}</p>
                                <p>
                                    <strong>Estado:</strong>
                                    <span class="badge" :class="statusBadgeClass[order.status] || 'bg-light text-dark'">
                                        {{ statusLabels[order.status] || order.status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <p v-if="order.observations" class="mb-0 mt-2"><strong>Observações:</strong> {{ order.observations }}</p>
                    </div>
                    <div v-else>
                        <div class="d-flex justify-content-center py-4">
                            <div class="spinner-border" role="status"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" @click="goToDetail" :disabled="loadingModal || !order.id">
                        Ver pedido completo
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
