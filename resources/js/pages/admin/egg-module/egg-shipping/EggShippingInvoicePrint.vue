<script setup>
import moment from 'moment';

const props = defineProps({
    shipping: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (value) => {
    if (value === null || value === undefined || isNaN(value)) {
        return '0,00';
    }

    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(value));
};

const lineTotal = () => {
    const quantity = Number(props.shipping.order?.quantity_dozens || 0);
    const unitPrice = Number(props.shipping.order?.unit_price || 0);
    return quantity * unitPrice;
};
</script>

<template>
    <div style="display: none;">
        <div id="print-egg-shipping-invoice">
            <div class="row">
                <div class="col text-left" style="text-align: left">
                    <img
                        src="/files/img/sys/companylogo.png"
                        class="img-fluid"
                        alt="Dana Place"
                        width="150"
                        height="150"
                        style="text-align: left"
                    />
                </div>
                <div class="col"><br /></div>
                <div class="col text-right" style="text-align: right"></div>
            </div>

            <div class="row">
                <div class="col text-left" style="text-align: left">
                    <p style="font-size:10px">
                        Dana Place
                        <br />
                        Cimento a Ponta de Ouro
                        <br />
                        Matutuine, Moçambique
                        <br />
                        Tel: +258 84 0127200
                        <br />
                        Email: info@danaplace.co.mz
                        <br />
                        www.danaplace.co.mz
                    </p>
                </div>
                <div class="col"><br /></div>
                <div class="col text-right" style="text-align: right">
                    <strong style="font-size:10px">FATURA DE EXPEDIÇÃO</strong>
                    <p style="font-size:10px">{{ shipping.invoice_number }}</p>
                </div>
            </div>

            <hr />

            <div class="row">
                <div class="col">
                    <p style="font-size:10px">
                        Cliente: <strong>{{ shipping.order?.customer_name || '-' }}</strong>
                        <br />
                        NUIT: <strong>{{ shipping.order?.customer_tax_id || '-' }}</strong>
                        <br />
                        Email: <strong>{{ shipping.order?.customer_email || '-' }}</strong>
                        <br />
                        Contacto: <strong>{{ shipping.order?.customer_phone || '-' }}</strong>
                    </p>
                </div>
                <div class="col"><br /></div>
                <div class="col text-right">
                    <p style="font-size:10px">
                        Data expedição: <strong>{{ moment(shipping.shipping_date).format('DD/MM/YYYY') }}</strong>
                        <br />
                        Pedido: <strong>#{{ String(shipping.order?.id || '').padStart(3, '0') }}</strong>
                        <br />
                        Ref. expedição: <strong>#{{ shipping.id }}</strong>
                        <br />
                        <span v-if="shipping.delivery_note_number">
                            Guia entrega: <strong>{{ shipping.delivery_note_number }}</strong>
                        </span>
                    </p>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="bg-secondary" style="font-size:10px">#</th>
                        <th class="bg-secondary" style="font-size:10px">Descrição</th>
                        <th class="bg-secondary" style="font-size:10px">Categoria</th>
                        <th class="bg-secondary" style="font-size:10px">Quantidade (dz)</th>
                        <th class="bg-secondary" style="font-size:10px">Preço Unit.</th>
                        <th class="bg-secondary" style="font-size:10px">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-size:10px">1</td>
                        <td style="font-size:10px">Ovos — Produção Avícola</td>
                        <td style="font-size:10px">{{ shipping.order?.category?.name || '-' }}</td>
                        <td style="font-size:10px">{{ shipping.order?.quantity_dozens || '-' }}</td>
                        <td style="font-size:10px">{{ formatCurrency(shipping.order?.unit_price) }} MZN</td>
                        <td style="font-size:10px">{{ formatCurrency(lineTotal()) }} MZN</td>
                    </tr>
                </tbody>
            </table>

            <p class="text-right" style="font-size:10px">
                <strong>TOTAL</strong>: {{ formatCurrency(lineTotal()) }} MZN
            </p>

            <p style="font-size:10px">
                <strong>Transporte</strong>
                <br />
                Transportadora: <strong>{{ shipping.carrier }}</strong>
                <br />
                Motorista: <strong>{{ shipping.driver_name }}</strong>
                <br />
                Matrícula: <strong>{{ shipping.vehicle_plate }}</strong>
                <br />
                Temperatura: <strong>{{ shipping.vehicle_temperature ?? '-' }} °C</strong>
                <br />
                Lacre: <strong>{{ shipping.seal_number || '-' }}</strong>
                <br />
                Certificado Sanitário: <strong>{{ shipping.health_certificate || '-' }}</strong>
            </p>

            <p style="font-size:10px">
                <strong>Rastreabilidade</strong>
                <br />
                Código: <strong>{{ shipping.inventory?.egg?.traceability_code || '-' }}</strong>
                <br />
                Galpão: <strong>{{ shipping.inventory?.house?.name || '-' }}</strong>
                <br />
                Quantidade estoque: <strong>{{ shipping.inventory?.quantity || '-' }}</strong>
            </p>

            <p v-if="shipping.delivered_at" style="font-size:10px">
                <strong>Entrega</strong>
                <br />
                Entregue a: <strong>{{ shipping.delivered_to }}</strong>
                <br />
                Data/Hora: <strong>{{ moment(shipping.delivered_at).format('DD/MM/YYYY HH:mm') }}</strong>
            </p>

            <p v-if="shipping.order?.observations" style="font-size:10px">
                Observações: <strong>{{ shipping.order.observations }}</strong>
            </p>

            <p style="font-size:10px">
                Responsável: <strong>{{ shipping.responsible?.name || '-' }}</strong>
                <br />
                Emitido em: <strong>{{ moment().format('DD/MM/YYYY HH:mm') }}</strong>
            </p>
        </div>
    </div>
</template>
