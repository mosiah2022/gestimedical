<template>
    <Head title="Patient" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pacientes
                <PrimeButton label="Nuevo" icon="pi pi-plus" iconPos="right" class="float-right add-button" @click="createPatient" />
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <DataTable v-model:filters="filters1" :value="patients" dataKey="id"
                         :paginator="true"
                         :rows="5"
                         :loading="loading1"
                         responsiveLayout="scroll"
                         :globalFilterFields="['global','first_name','last_name', 'personal_id']">

                        <template #header>
                            <div class="flex justify-content-center">
                                <span class="p-input-icon-left w-full">
                                    <i class="pi pi-search" />
                                    <InputText v-model="filters1['global'].value" placeholder="Buscar" class="w-full" />
                                </span>
                            </div>
                        </template>

                            <Column field="first_name" header="Nombre" />
                            <Column field="last_name" header="Apellido" />
                            <Column field="personal_id" header="Cedula" />
<!--                             <Column field="lms" header="LM" >
                                <template #body="slotProps">
                                    <div>{{slotProps.data.lms.map(lms => lms.lm_id).join(', ')}}</div>
                                </template>
                            </Column> -->
                            <Column bodyStyle="justify-center" header="Acción"
                                    headerStyle="width: 14rem; justify-center">
                                <template #body="slotProps">
                                    <PrimeButton @click="editPatient(slotProps.data.id)" icon="pi pi-pencil" class="btn_edit" title="editar" />
                                    <PrimeButton @click="newLm(slotProps.data)" icon="pi pi-tags" class="btn_lms" title="Crear Orden" />
                                </template>

                            </Column>
                        </DataTable>
                    </div>
                </div>
            </div>
            <Dialog :header="editId === null ? 'Crear Paciente' : 'Editar Paciente'" :style="{width: '50vw'}"
                    v-model:visible="display">
                <PatientForm :editId="editId" />
            </Dialog>
            <Dialog :header="'Crear Formulación'" :style="{width: '50vw'}"
                    v-model:visible="displayLm" :maximizable="true" >
                <OrderCreatePage :createLm="createLm" />
            </Dialog>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/inertia-vue3';
import Swal from "sweetalert2";
import axios from "axios";
import {FilterMatchMode,FilterOperator} from 'primevue/api';
import PatientForm from "@/Pages/Patients/PatientForm";
import OrderCreatePage from "@/Pages/Lms/OrderCreatePage";
import { Link } from '@inertiajs/inertia-vue3';

export default {
    name: "PatientPage",
    components: {
        BreezeAuthenticatedLayout,
        Head,
        PatientForm,
        Link,
        OrderCreatePage
    },
    data () {
        return {
            patients: null,
            filters1: null,
            filters2: {
                'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
                'first_name': {value: null, matchMode: FilterMatchMode.STARTS_WITH},
                'last_name': {value: null, matchMode: FilterMatchMode.STARTS_WITH},
                'personal_id': {value: null, matchMode: FilterMatchMode.EQUALS},
            },
            loading1: true,
            loading2: true,
            editId: null,
            display: false,
            createLm: null,
            displayLm: false
        }
    },
    methods: {
        createPatient(){
            this.editId = null
            this.display=true;
        },
        async getPatients() {
            await axios.get('/api/patients').then((res) => {
                this.patients = res.data
                this.loading1 = false;
            })
        },
        async editPatient (id) {
            this.editId = id
            this.display = true
        },
        async newLm(id){
            this.createLm = id,
            this.displayLm = true
        },
        initFilters1() {
            this.filters1 = {
                'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
                'first_name': {operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.STARTS_WITH}]},
                'last_name': {operator: FilterOperator.AND, constraints: [{value: null, matchMode: FilterMatchMode.STARTS_WITH}]},
                'personal_id': {value: null, matchMode: FilterMatchMode.EQUALS},
            }
        },
        goHistory(id){
            window.open("http://asispharma.com/app_pharma/public/formulas?lm_code=&fecha-gen=&fecha-disp=&personal_id="+id);
        }
    },
    created() {
        this.initFilters1()
    },
    mounted() {
        this.getPatients()

        this.emitter.on('patients_reload', (evt) => {
            console.log(evt.id);
            this.editPatient(evt.id);
            this.createLm = evt,
            this.getPatients()
            this.display = false
            this.displayLm = true
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Operación realizada con éxito!`, life:3000,
            })
        })

        this.emitter.on('patientLm_reload', () => {
            this.getPatients()
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Primera fase de la orden creada`, life:3000,
            })
        })

        this.emitter.on('patient_update_reload', () => {
            this.getPatients()
            this.display = false
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Paciente editado exitosamente`, life:3000,
            })
        })

        this.emitter.on('order_reload', () => {
            this.getPatients()
            this.displayLm = false
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Orden completada se a cargado LM exitosamente!`, life:3000,
            })
        })

        this.emitter.on('price_update_reload', () => {
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `El precio se actualizo exitosamente!`, life:3000,
            })
        })
    }

}
</script>

<style scoped>
.del-btn{
    background-color: firebrick;
    border-bottom-width: 0px;
}
.btn_edit{
    background-color: green;
    margin-left: 2px;
}
.btn_lms{
    background-color: blueviolet;
    margin-left: 2px;
}
.add-button{
    background-color: blueviolet;
}
</style>
