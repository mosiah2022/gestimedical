<template>
    <Card>
        <template #title>
            <h4>Diagnósticos</h4>
        </template>
        <template #content>
            <DataTable :value="diagnostics" dataKey="id"
                       v-model:filters="filters1"
                       responsiveLayout="scroll"
                       :paginator="true"
                       :rows="10"
                       :loading="loading1"
                       :globalFilterFields="['global','description','patient.full_name','patient.personal_id']">
                <template #header>
                    <div class="flex justify-content-center">
                        <span class="p-input-icon-left w-full">
                            <i class="pi pi-search" />
                            <InputText v-model="filters1['global'].value" placeholder="Buscar" class="w-full" />
                        </span>
                    </div>
                </template>
                <Column field="id" header="Id"/>
                <Column field="description" header="Descripción" />
                <Column field="patient.full_name" header="Paciente" />
                <Column field="patient.personal_id" header="Cedula" />
                <Column bodyStyle="text-align: center; overflow: visible" header="Acción"
                        headerStyle="width: 14rem; text-align: center">
                    <template #body="slotProps">
                        <PrimeButton class="-right-2.5 del-btn" @click="destroyDiagnostic(slotProps.data.id)" icon="pi pi-trash" title="borrar" />
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
    name: "ListDiagnostic",
    components: {
        BreezeAuthenticatedLayout,
        Head
    },
    data() {
        return {
            diagnostics: null,
            loading1: true,
            filter: null,
            filters1: null,
            filters2: {
                'global': {value:null, matchMode: FilterMatchMode.CONTAINS},
                'description': {value:null, matchMode: FilterMatchMode.STARTS_WITH},
                'patient.full_name': {value:null, matchMode: FilterMatchMode.STARTS_WITH},
                'patient.personal_id': {value:null, matchMode: FilterMatchMode.STARTS_WITH}
            },
        }
    },
    methods: {
        async getDiagnostics() {
            await axios.get('api/patient_diagnostics').then((res) => {
                this.diagnostics = res.data
                this.loading1 = false;
            })
        },
        async destroyDiagnostic(id) {
            Swal.fire({
                title: 'Seguro de eliminar el diagnostico',
                showDenyButton: true,
                confirmationButtonText: 'Borrar',
                denyButtonText: 'No borrar',
            }).then((result) => {
                if(result.isConfirmed) {
                    axios.delete(`/api/patient_diagnostics/${id}`).then(() => {
                        return this.emitter.emit('diagnostic_reload')
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
                'description':{operator: FilterOperator.AND, constraints: [{value:null, matchMode: FilterMatchMode.STARTS_WITH}]},
                'patient.full_name':{operator: FilterOperator.AND, constraints: [{value:null, matchMode: FilterMatchMode.STARTS_WITH}]},
                'patient.personal_id':{operator: FilterOperator.AND, constraints: [{value:null, matchMode: FilterMatchMode.STARTS_WITH}]}
            }
        }
    },
    created() {
        this.initFilters1()
    },
    mounted() {
        this.getDiagnostics();
        this.emitter.on('diagnostic_reload', () => {
            this.getDiagnostics()
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Se a borrado correctamente el registro`, life:3000
            })
        })
    }
}
</script>
