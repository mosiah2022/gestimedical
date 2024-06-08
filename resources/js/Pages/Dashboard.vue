<template>
    <Head title="Dashboard" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>
        <div class="py-4">
            <div class="mx-auto sm:px-6 lg:px-8 mt-4">
                <TabView>
                    <TabPanel header="Administrar Medicamentos">
                        <div class="flex justify-end p-2">
                            <PrimeButton @click="activateProducts" class="add-btn" icon="pi pi-angle-down" title="nuevo" />
                        </div>
                        <ListProducts v-if="product === true" />
                    </TabPanel>
                    <TabPanel header="Administrar Diagnósticos">
                        <div class="flex justify-end p-2">
                            <PrimeButton @click="activateDiagnostic" class="add-btn" icon="pi pi-angle-down" title="nuevo" />
                        </div>
                        <ListDiagnostic v-if="diagnostic === true" />
                    </TabPanel>
                    <TabPanel header="Administrar Direcciones">
                        <div class="flex justify-end p-2">
                            <PrimeButton @click="activateAddress" class="add-btn" icon="pi pi-angle-down" title="nuevo" />
                        </div>
                        <ListAddress  v-if="address  === true" />
                    </TabPanel>
                    <TabPanel header="Administrar Presentaciones">
                        <div class="flex justify-end p-2">
                            <PrimeButton @click="activatePresentation" class="add-btn" icon="pi pi-angle-down" title="nuevo" />
                        </div>
                        <ListPresentation v-if="presentation === true" />
                    </TabPanel>
                    <TabPanel header="Administrar Pacientes">
                        <div class="flex justify-end p-2">
                            <PrimeButton @click="activatePatient" class="add-btn" icon="pi pi-angle-down" title="nuevo" />
                        </div>
                        <div v-if="patient === true">
                            <ListPatient v-if="$page.props.auth.user.role==='Admin'" />
                            <Message v-if="$page.props.auth.user.role==='Basic'" severity="warn" :closable="false">No cuenta con acceso a esta sección</Message>
                        </div>
                    </TabPanel>

                </TabView>
            </div>
        </div>

    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head } from '@inertiajs/inertia-vue3';
import ListProducts from "@/Pages/Products/ListProducts";
import ListDiagnostic from "@/Pages/Diagnostics/ListDiagnostic";
import ListAddress from "@/Pages/Address/ListAddress";
import ListPresentation from "@/Pages/Presentations/ListPresentation";
import ListPatient from "@/Pages/Patients/ListPatient";

export default {
    components: {
        ListProducts,
        ListDiagnostic,
        ListAddress,
        ListPresentation,
        ListPatient,
        BreezeAuthenticatedLayout,
        Head,
    },
    data() {
        return {
            product: false,
            diagnostic: false,
            address: false,
            presentation: false,
            patient: false
        }
    },
    methods: {
        activateProducts() {
            this.address = false;
            this.diagnostic = false;
            this.presentation = false;
            this.patient = false;
            this.product = true;
        },
        activateDiagnostic() {
            this.address = false;
            this.product = false;
            this.presentation = false;
            this.patient = false;
            this.diagnostic = true;
        },
        activateAddress() {
            this.diagnostic = false;
            this.product = false;
            this.presentation = false;
            this.patient = false;
            this.address= true;
        },
        activatePresentation() {
            this.diagnostic = false;
            this.product = false;
            this.address= false;
            this.patient = false;
            this.presentation = true;
        },
        activatePatient() {
            this.diagnostic = false;
            this.product = false;
            this.address= false;
            this.presentation = false;
            this.patient = true;
        }
    },
}
</script>
<style scoped>
.del-btn{
    background-color: firebrick;
    border-bottom-width: 0px;
}
.edit_btn{
    background-color: blue;
}
.add-btn{
    margin-bottom: 20px;
    border-radius: 50%;
}
</style>
