<template>
    <Card>
        <template #title>
            <h4>Direcciones y teléfonos</h4>
        </template>
        <template #content>
            <DataTable :value="address" dataKey="id"
                       v-model:filters="filters1"
                       responsiveLayout="scroll"
                       :paginator="true"
                       :rows="10"
                       :loading="loading1"
                       :globalFilterFields="['global','address','patient.personal_id','patient.full_name']">
                <template #header>
                    <div class="flex justify-content-center">
                        <span class="p-input-icon-left w-full">
                            <i class="pi pi-search" />
                            <InputText v-model="filters1['global'].value" placeholder="Buscar" class="w-full" />
                        </span>
                    </div>
                </template>
                <Column field="id" header="Id"/>
                <Column field="address" header="Descripción" />
                <Column field="category" header="Categoría" />
                <Column field="patient.full_name" header="Paciente" />
                <Column field="patient.personal_id" header="Cedula" />
                <Column bodyStyle="text-align: center; overflow: visible" header="Acción"
                        headerStyle="width: 14rem; text-align: center">
                    <template #body="slotProps">
                        <PrimeButton class="-right-2.5 del-btn" @click="destroyAddress(slotProps.data.id)" icon="pi pi-trash" title="borrar" />
                    </template>
                </Column>
            </DataTable>
        </template>
    </Card>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import Swal from 'sweetalert2'
import {FilterMatchMode, FilterOperator} from "primevue/api";

export default {
    name: "ListAddress",
    components: {
        BreezeAuthenticatedLayout,
        Head
    },
    data() {
        return {
            address: null,
            loading1: true,
            filter: null,
            filters1: null,
            filters2: {
                'global': {value:null, matchMode: FilterMatchMode.CONTAINS},
                'address': {value:null, matchMode: FilterMatchMode.STARTS_WITH},
                'patient.personal_id': {value:null, matchMode: FilterMatchMode.STARTS_WITH},
                'patient.full_name': {value:null, matchMode: FilterMatchMode.STARTS_WITH},
            },
        }
    },
    methods: {
        async getAddress() {
            await axios.get('api/patient_addresses').then((res) => {
                this.address = res.data
                this.loading1 = false;
            })
        },
        async destroyAddress(id) {
            Swal.fire({
                title: 'Seguro de eliminar el registro',
                showDenyButton: true,
                confirmationButtonText: 'Borrar',
                denyButtonText: 'No borrar',
            }).then((result) => {
                if(result.isConfirmed) {
                    axios.delete(`/api/patient_addresses/${id}`).then(() => {
                        return this.emitter.emit('address_reload')
                    }).catch(() => {
                        Swal.fire('No se logro eliminar', '', 'error')
                    })
                }else if (result.isDenied) {
                    Swal.fire('No se a borrado...', '', 'info')
                }
            })
        },
        initFilters1() {
            this.filters1 = {
                'global': {value:null, matchMode:FilterMatchMode.CONTAINS},
                'address':{operator: FilterOperator.AND, constraints: [{value:null, matchMode: FilterMatchMode.STARTS_WITH}]},
                'patient.personal_id':{operator: FilterOperator.AND, constraints: [{value:null, matchMode: FilterMatchMode.STARTS_WITH}]},
                'patient.full_name':{operator: FilterOperator.AND, constraints: [{value:null, matchMode: FilterMatchMode.STARTS_WITH}]}
            }
        }
    },
    created() {
        this.initFilters1()
    },
    mounted() {
        this.getAddress();
        this.emitter.on('address_reload', () => {
            this.getAddress()
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Se a borrado correctamente el registro`, life:3000
            })
        })
    }
}
</script>
