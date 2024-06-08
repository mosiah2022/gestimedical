<template>
    <Head title="Reports" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reportes
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <div class="formgroup-inline p-6">
                        <div class="field">
                            <label>Buscar por fecha</label>
                            <Calendar v-model="form.dates" selectionMode="range" dateFormat="yy-mm-dd" :showIcon="true" class="w-full" />
                        </div>
                        <div class="field float-right mt-3">
                            <PrimeButton label="Buscar" icon="pi pi-search" iconPos="right" @click="searchOrders()" class="sm:-bottom-1.5" />
                        </div>
                        <div class="field float-right mt-3" v-if="order_checks > 0">
                            <PrimeButton label="Revisar factura" icon="pi pi-check" iconPos="right" @click="prepareInvoice($event)" class="sm:-bottom-1.5" />
                        </div>
                    </div>
                    <div class="p-6 bg-white border-b border-gray-200" v-if="orders.length > 0">
                        <DataTable  :value="orders" dataKey="id" responsiveLayout="scroll" :paginate="true" :rows="20">
                            <Column field="lm_code" header="Código"></Column>
                            <Column field="date_ini" header="Fecha de la orden"></Column>
                            <Column field="total_detail"  header="Total"></Column>
                            <Column>
                                <template #body="slotProps">
                                    <PrimeButton icon="pi pi-check" class="p-button-rounded p-button-success mr-2" @click="selectOrder(slotProps.data.lm_code)" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>

            <Dialog v-model:visible="displayPreInvoice" :header="'Pre factura - su selección'" :style="{width: '50vw'}">
                <CreatePreInvoice />
            </Dialog>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/inertia-vue3';
import Label from '@/Components/Label.vue';
import axios from 'axios';
import Swal from "sweetalert2";
import CreatePreInvoice from "@/Pages/PreInvoices/CreatePreInvoice";

export default {
    name: "ReportPage",
    components: {
        CreatePreInvoice,
        BreezeAuthenticatedLayout,
        Head,
        Label
    },
    data () {
        return {
            form: {
                dates: null,
                lm_result: null
            },
            formPre: {
                patient_lms_id: null,
                total_items: null,
                lm_code: null,
                status: null
            },
            orders: [],
            lms: [],
            displayPreInvoice: false,
            orderLms: null,
            order: null,
            invoice_number: null,
            order_checks: null
        }
    },
    methods: {
        async searchOrders(){
            let selectedDate = this.form.dates;
            let iniDate = new Date(selectedDate[0]).toLocaleDateString('fr-CA');
            let endDate = new Date(selectedDate[1]).toLocaleDateString('fr-CA');

            console.log(iniDate);

            const res = await axios.get(`/api/getOrdersLm/${iniDate}/${endDate}`).then((res) => {
                this.orders = res.data;
                this.checkInvoice();
            })
        },
        async checkInvoice() {
            await axios.get('api/getInvoiceActive').then((res) => {
                this.order_checks = res.data.length
            })
        },
        async prepareInvoice(){
            this.displayPreInvoice = true;
        },
        async selectOrder(id) {
            await axios.get(`api/patientByLm/${id}`).then((res) => {
                this.order = res.data
            });
            if(this.order.length > 0) {
                await this.preInvoice(this.order);
            }else {
                await Swal.fire({
                        icon: 'error',
                        title: 'El código seleccionado no coincide con alguna orden',
                    }
                );
            }
        },
        async updateStatus(id) {
            await axios.patch(`/api/update_lmcode/${id}`, {
                status: 'proccess'
            })
        },
        async preInvoice() {
            //prepare data for the request form
            this.formPre.patient_lms_id = this.order[0].id;
            this.formPre.total_items    = this.order[0].orders.length;
            this.formPre.lm_code        = this.order[0].lm_code;
            this.formPre.status         = 'proccess'

            //get count of pre invoices and proccess the request
            await this.getCountPreInvoice(this.formPre.total_items, this.formPre);

        },
        async getCountPreInvoice(id, formPre) {
            await axios.get(`api/getCountPreInvoices/${id}`).then((res) => {
                if(res.data <= 17) {
                    axios.post('api/pre_invoices', formPre)
                    this.updateStatus(this.order[0].lm_code)
                    this.checkInvoice()
                    this.searchOrders()
                    return this.emitter.emit('lms_reload')
                }else {
                    Swal.fire({
                        icon: 'error',
                        title: 'La factura que esta preparando supera los 17 items configurados y permitidos por el sistema'
                    })
                }
            })
        }
    },
    mounted(){
        this.emitter.on('lms_reload', () => {
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Lm anexado a la factura`, life:3000,
            })
        });
        this.emitter.on('pre_invoice_reload', () => {
            this.$toast.add({
                severity: 'success', summary: 'SUCCESS!',
                detail: 'Se completo proceso de factura', life: 3000,
            })
            this.displayPreInvoice = false;
            this.checkInvoice();
        })
    }
}
</script>
