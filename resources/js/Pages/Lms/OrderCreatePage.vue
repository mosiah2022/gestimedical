<template>

    <div class="card">
        <div class="formgrid grid">
            <div class="field col">
                <label class="font-bold">Paciente - <span>{{patient.personal_id}}</span></label>
                <InputText v-model="patient.full_name" class="inputfield w-full" disabled="true" />
            </div>
            <div class="field col">
                <label class="font-bold text-teal-500">Fecha de generación</label>
                <Calendar id="icon" v-model="form.date_ini" :showIcon="true" dateFormat="dd/mm/yy" />
            </div>
            <div class="field col">
                <label class="font-bold text-teal-500">Seleccione teléfono <span class="pi pi-plus-circle justify-center cursor-pointer text-lime-600" @click="viewCreatePhone" label="Nuevo"  /></label>
                <Dropdown class="w-full"
                    v-model="form.phone_id"
                    :options="phones"
                    optionLabel="name"
                    optionValue="id"
                    :filter="false"
                    filterPlaceholder="Seleccione telefono"
                    :showClear="true"
                />
                <small class="text-red-500">{{ error_phone_id }}</small>
            </div>
        </div>
        <div class="field col">
            <label class="font-bold text-teal-500">Diagnóstico <span class="pi pi-plus-circle justify-center cursor-pointer text-lime-600" label="Nuevo" @click="viewCreateDiagnostic" /></label>
            <Dropdown class="w-full"
                v-model="form.diagnostic_id"
                :options="diagnostics"
                optionLabel="description"
                optionValue="id"
                :filter="false"
                filterPlaceholder="Seleccione diagnostico"
                :showClear="true"
                :editable="true"
                @blur="editDiagnostic($event)"
                @change="setDiagnostic"
            />
            <small class="text-red-500">{{ error_diagnostic_id }}</small>
        </div>

        <div class="field col">
            <label class="font-bold text-teal-500">Seleccione una dirección <span class="pi pi-plus-circle justify-center cursor-pointer text-lime-600" label="Nuevo" @click="viewCreateAddress"  /></label>
            <Dropdown class="w-full"
                v-model="form.address_id"
                :options="addreses"
                optionLabel="name"
                optionValue="id"
                :filter="false"
                filterPlaceholder="Seleccione una direccion"
                :showClear="true"
            />
            <small class="text-red-500">{{ error_address_id }}</small>
        </div>
        <div class="field col">
            <label>Coloque nombre y apellido del Doctor</label>
            <InputText v-model="form.doctor_name" class="inputfield w-full"  />
        </div>

        <div class="field" v-if="activeButton === true">
            <PrimeButton icon="pi pi-save" label="Siguiente" class="sm:-bottom-1.5" @click="submit" />
        </div>

        <MedicinesAdd v-if="display === true" :order_id="order_id" :patient_id="form.patient_id" />

        <div class="formgrid grid" v-if="displayLms === true">
            <div class="field col">
                <label>Código autorización - LM | EC | ARL</label>
                <InputText v-model="form.lm_id" class="inputfield w-full" />
                <small class="text-red-500">{{ error_lm_code }}</small>
            </div>
            <div class="field col">
                <label>Autorizado por:</label>
                <InputText v-model="form.authorized_by" class="inputfield w-full" />
                <small class="text-red-500">{{ error_authorized_by }}</small>
            </div>
        </div>
        <div class="field col" v-if="displayLms === true">
            <label>Observaciones</label>
            <InputText v-model="form.observation" class="inputfield w-full" />
        </div>
        <div class="field" v-if="displayLms === true">
            <PrimeButton icon="pi pi-save" label="Guardar" class="sm:-bottom-1.5" @click="submitLm(order_id)" />
        </div>

        <Dialog :header="'Nuevo número de teléfono'" :style="{width: '25vw'}"
                v-model:visible="displayCreatePhone" :maximizable="false" >
            <CreatePhone :patient_id="patient.id" />
        </Dialog>

        <Dialog :header="'Nueva dirección'" :style="{width: '25vw'}"
                v-model:visible="displayCreateAddress" :maximizable="false" >
            <CreateAddress :patient_id="patient.id" />
        </Dialog>

        <Dialog :header="'Nuevo diagnóstico'" :style="{width: '25vw'}"
                v-model:visible="displayCreateDiagnostic" :maximizable="false" >
            <CreateDiagnostic :patient_id="patient.id" />
        </Dialog>

    </div>

</template>

<script>

import MedicinesAdd from "@/Pages/Medicines/MedicinesAdd";
import CreatePhone from "../Patients/CreatePhone.vue";
import CreateAddress from "../Patients/CreateAddress.vue"
import CreateDiagnostic from "../Patients/CreateDiagnostic.vue";

export default {
    name: "OrderCreatePage",
    components: {
        MedicinesAdd,
        CreatePhone,
        CreateAddress,
        CreateDiagnostic
    },
    data () {
        return {
            patient:[],
            doctors: null,
            addreses: [],
            diagnostics: [],
            addressCount: 0,
            selectedDoctor: null,
            filterdDoctor: null,
            order_id: null,
            display: false,
            form:{
                lm_id: null,
                date_ini: null,
                date_end: null,
                diagnostic_id: null,
                doctor_name: null,
                patient_id: null,
                authorized_by: null,
                observartion: null,
                address_id: null,
                phone_id: null
            },
            error_lm_code: null,
            error_authorized_by: null,
            error_phone_id: null,
            error_address_id: null,
            error_diagnostic_id: null,
            saveLm: false,
            activeButton: true,
            displayLms: false,
            phones: [],
            displayCreatePhone: false,
            displayCreateAddress: false,
            displayCreateDiagnostic: false,
            diagnostic_idold: null,
        }
    },
    props: {
        createLm: Object,
        editId: Number,
    },
    methods: {
         async getDoctors() {
            await axios.get('api/doctors').then((res) => {
                this.doctors = res.data
            })
        },
        async getAddress(id, category) {
            await axios.get(`api/address_patient/${id}/${category}`).then((res) =>{
                this.addreses = res.data;
            })
        },
        async getPhones(id, category) {
            await axios.get(`api/address_patient/${id}/${category}`).then((res) =>{
                this.phones = res.data;
            })
        },
        async getDiagnostics(id) {
            await axios.get(`api/diagnostic_patient/${id}`).then((res) => {
                this.diagnostics = res.data;
            })
        },
        async submit () {
            this.activeButton = false;
            this.form.patient_id = this.$props.createLm.id;
            if(!this.$props.editId) {
                try {
                    const res = await axios.post('api/patient_lms', this.form)
                    this.saveLm = true
                    this.order_id = res.data.id;
                    this.display = true;
                    this.displayLms = true;

                    return this.emitter.emit('patientLm_reload')
                }
                catch (e) {
                    if (e.response) {
                        this.activeButton = true;
                        switch (e.response.status) {
                            case 422:
                                let err = e.response.data.errors
                                this.error_lm_code       = err.lm_code ? err.lm_code[0] : null
                                this.error_phone_id      = err.phone_id ? err.phone_id[0]: null
                                this.error_address_id    = err.address_id ? err.address_id[0]: null
                                this.error_diagnostic_id = err.diagnostic_id ? err.diagnostic_id[0]: null
                        }
                    }
                    return null
                }
            }
            try {
                const res = await axios.put(`/api/patient_lm/${this.$props.editId}`, this.form)
                return this.emitter.emit('patientLm_reload')
            }
            catch (e) {
                if (e.response) {
                    switch (e.response.status) {
                        case 422:
                            let err = e.response.data.errors
                            this.error_lm_code = err.lm_code ? err.lm_code[0]: null
                    }
                }
            }
        },
        async submitLm (order_id) {
            try {
                const res = await axios.patch(`/api/update_order/${order_id}`, {
                    lm_code:this.form.lm_id,
                    authorized_by:this.form.authorized_by,
                    observation:this.form.observation
                })
                return this.emitter.emit('order_reload')
            }
            catch(e) {
                if(e.response) {
                    switch (e.response.status) {
                        case 422:
                            let err = e.response.data.errors
                            this.error_lm_code = err.lm_code ? err.lm_code[0]: null
                            this.error_authorized_by = err.authorized_by ? err.authorized_by[0]: null
                    }
                }
            }

        },
        async setDiagnostic(){
            this.diagnostic_idold = this.form.diagnostic_id;
        },
        async editDiagnostic(e){
            if(this.form.diagnostic_id != null){
                const res = await axios.patch(`api/update_diagnostic/${this.diagnostic_idold}`, {
                    description: e.target.value
                }).then
                this.getDiagnostics(this.patient.id);
                this.form.diagnostic_id = this.diagnostic_idold
            }
        },
        searchDoctor(event) {
            setTimeout(() => {
                if (!event.query.trim().length) {
                    this.filteredDoctor = [...this.doctors];
                }
                else {
                    this.filteredDoctor = this.doctors.filter((doctor) => {
                        return `${doctor.complete_name}`.toLowerCase().startsWith(event.query.toLowerCase());
                    });
                }
            }, 250);
        },
        viewCreatePhone(){
            this.displayCreatePhone = true;
        },
        viewCreateAddress(){
            this.displayCreateAddress = true;
        },
        viewCreateDiagnostic() {
            this.displayCreateDiagnostic = true;
        }
    },
    mounted() {

        this.patient = this.$props.createLm;
        this.getAddress(this.patient.id, 'address');
        this.getPhones(this.patient.id, 'phone');
        this.getDiagnostics(this.patient.id);

        this.emitter.on('createphone_reload', () => {
            this.displayCreatePhone = false;
            this.getPhones(this.patient.id, 'phone');
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Teléfono creado`, life: 3000,
            })
        });

        this.emitter.on('patient_lm_detail_reload', () => {
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Medicamento cargado a la orden`, life:3000,
            })
        });

        this.emitter.on('createaddress_reload', () => {
            this.displayCreateAddress = false;
            this.getAddress(this.patient.id, 'address');
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Dirección creada`, life: 3000,
            })
        });

        this.emitter.on('creatediagnostic_reload', () => {
            this.displayCreateDiagnostic = false;
            this.getDiagnostics(this.patient.id);

            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Diagnóstico`, life: 3000,
            })
        });

    }
}
</script>

<style scoped>
.p-inputtext{
    width: 100%;
}
</style>
