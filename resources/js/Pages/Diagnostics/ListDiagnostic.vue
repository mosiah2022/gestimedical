<template>
    <DataTable :value="diagnostics" dataKey="id" :totalRecords="totalRecords"
                stripedRows
                lazy paginator
                v-model:filters="filters1"
                responsiveLayout="scroll"
                :paginator="true"
                :rows="perPage"
                :loading="loading1"
                @page="handlePageChange">
        <template #header>
            <div class="flex justify-content-center">
                <span class="p-input-icon-left w-full">
                    <i class="pi pi-search" />
                    <InputText v-model="filters1.global.value" placeholder="Buscar" class="w-full" @input="handleFilterChange" />
                </span>
            </div>
        </template>
        <Column field="id" header="Id"/>
        <Column field="patient.full_name" header="Paciente" />
        <Column field="description" header="Descripción" />
        <Column field="patient.personal_id" header="Cedula" />
        <Column bodyStyle="text-align: center; overflow: visible" header="Acción"
                headerStyle="text-align: center">
            <template #body="slotProps">
                <PrimeButton class="-right-2.5 del-btn" @click="destroyDiagnostic(slotProps.data.id)" icon="pi pi-trash" title="borrar" />
            </template>
        </Column>
    </DataTable>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/inertia-vue3';
import axios from 'axios';
import Swal from 'sweetalert2'
import {FilterMatchMode, FilterOperator} from "primevue/api";
import debounce from 'lodash/debounce';

export default {
    name: "ListDiagnostic",
    components: {
        BreezeAuthenticatedLayout,
        Head
    },
    data() {
        return {
            diagnostics: [],
            totalRecords: 0,
            currentPage: 1,
            perPage: 10,
            loading1: true,
            filters1: {
                global: { value: ''}
            },
            lazyParams: {
                first: 0,
                rows: 10,
                sortField: null,
                sortOrder: null,
                filters: {}
            }
        }
    },
    methods: {
        async loadLazyData() {
            this.loading1 = false;
            try {
                const response = await axios.get('api/patient_diagnostics', {
                    params: {
                        page: this.currentPage,
                        perPage: this.perPage,
                        filters: this.filters1.global.value
                    }
                });
                this.diagnostics = response.data.diagnostics;
                this.totalRecords = response.data.totalRecords;
                this.loading1 = false;
            } catch (error) {
                console.log('Error al traer los diagnosticos:', error);
                this.loading1 = false;
            }
        },
        handlePageChange(event) {
            this.lazyParams.first = event.first;
            this.lazyParams.rows = event.rows;
            this.currentPage = Math.floor(event.first / event.rows) + 1; // Usamos Math.floor para asegurarnos de que la página es correcta
            this.loadLazyData();
        },
        handleFilterChange() {
            this.currentPage = 1; // Reset page to 1 when a new filter is applied
            this.lazyParams.first = 0;
            this.debouncedLoadLazyData();
        },
        debouncedLoadLazyData: debounce(function() {
            this.loadLazyData();
        }, 500),
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
        this.loadLazyData();
        this.emitter.on('diagnostic_reload', () => {
            this.loadLazyData()
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Se a borrado correctamente el registro`, life:3000
            })
        })
    }
}
</script>

<style scoped>
    .del-btn{
        color: red;
        background-color: transparent;
        border-width: 0;
        border-bottom-width: 0px;
        height: 24px;
        width: 24px;
    }
    .edit_btn{
        background-color: blue;
    }
    .add-btn{
        margin-bottom: 20px;
        border-radius: 50%;
    }
</style>