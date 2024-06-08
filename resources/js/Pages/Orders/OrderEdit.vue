<template>
    <div class="card">
        <span v-if="animation_wait === true" class="justify-center">Espere un momento por favor<ProgressSpinner  /></span>
        <div class="formgrid grid">
            <div class="field col">
                <label class="font-bold">Paciente - <span>{{patient.personal_id}} </span></label>
                <InputText v-model="patient.full_name" class="inputfield w-full" disabled="true" />
            </div>
            <div class="field col">
                <label class="font-bold text-teal-500">Fecha de generación</label>
                <Calendar id="icon" v-model="form.date_ini" :showIcon="true" dateFormat="dd/mm/yy" class="w-full" />
            </div>
            <div class="field col">
                <label class="font-bold text teal-500">Seleccione teléfono <span class="pi pi-plus-circle justify-center cursor-pointer text-lime-600" @click="viewCreatePhone" label="Nuevo"  /></label>
                <Dropdown class="w-full"
                    v-model="form.phone_id"
                    :options="phones"
                    optionLabel="name"
                    optionValue="id"
                    :filter="false"
                    filterPlaceholder="Seleccione telefono"
                    :showClear="true"
                />
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
        </div>
        <div class="field col">
            <label>Coloque nombre y apellido del Doctor</label>
            <InputText v-model="form.doctor_name" class="inputfield w-full"  />
        </div>        
        <MedicinesAdd :order_id="$props.editId" :patient_id="$props.patient_id" />

        <div class="formgrid grid">
            <div class="field col">
                <label>Código autorización - LM | EC | ARL</label>
                <InputText v-model="form.lm_code" class="inputfield w-full" />
                <small class="text-red-500">{{ error_lm_code }}</small>
            </div>
            <div class="field col">
                <label>Autorizado por:</label>
                <InputText v-model="form.authorized_by" class="inputfield w-full" />
            </div>
        </div>
        <div class="field col">
            <label>Observaciones</label>
            <InputText v-model="form.observation" class="inputfield w-full" />
        </div>
        <div class="field-checkbox">
            <Checkbox id="copago" name="copago" value="0" v-model="copago_check" :binary="true" />
            <label>Indique si tiene copago</label>
        </div>
        <div class="field col" v-if="copago_check === true">
            <h5>Seleccione el Copago  {{ form.discount_percent }} %</h5>
            <Slider v-model="form.discount_percent" :min="0" :max="100" />
        </div>
        
        <FileUploadFile :patient_id="$props.patient_id" />

        <div class="field">
            <PrimeButton icon="pi pi-save" label="Guardar" class="sm:-bottom-1.5" @click="submitLm($props.editId)" />
        </div>

        <Dialog :header="'Nuevo número de teléfono'" :style="{width: '25vw'}"
                v-model:visible="displayCreatePhone" :maximizable="false" >
            <CreatePhone :patient_id="$props.patient_id" />
        </Dialog>
        <Dialog :header="'Nueva dirección'" :style="{width: '25vw'}"
                v-model:visible="displayCreateAddress" :maximizable="false" >
            <CreateAddress :patient_id="$props.patient_id" />
        </Dialog>

        <Dialog :header="'Nuevo diagnóstico'" :style="{width: '25vw'}"
                v-model:visible="displayCreateDiagnostic" :maximizable="false" >
            <CreateDiagnostic :patient_id="$props.patient_id" />
        </Dialog>

    </div>
</template>

<script>
import axios from 'axios'
import CreatePhone from '../Patients/CreatePhone.vue'
import CreateAddress from '../Patients/CreateAddress.vue'
import CreateDiagnostic from '../Patients/CreateDiagnostic.vue'
import MedicinesAdd from '../Medicines/MedicinesAdd.vue'
import FileUploadFile from '../Uploads/FileUploadFile.vue'

export default {
    name: "OrderEdit",
    components: {
        CreatePhone,
        CreateAddress,
        CreateDiagnostic,
        MedicinesAdd,
        FileUploadFile
    },
    data () {
        return {
            order: [],
            patient: [],
            orders: [],
            edit_id: null,
            fullname: null,
            form:{
                date_ini: null,
                phone_id: null,
                diagnostic_id: null,
                address_id: null,
                lm_code: null,
                authorized_by: null,
                observation: null,
                discount_percent: null
            },
            phones: [],
            diagnostics: [],
            products: [],
            displayCreatePhone: null,
            displayCreateDiagnostic: null,
            displayCreateAddress: null,
            diagnostic_idold: null,
            addreses: null,
            error_lm_code: null,
            copago_check: false,
            animation_wait:false,
            lm_info: null,
            pdfFile: null
        }
    },
    props: {
        editId: Number,
        patient_id: Number
    },
    methods: {
        async getOrder() {
            this.animation_wait = true
            await axios.get(`api/patient_lms/${this.edit_id}`).then((res) => {
                this.order = res.data
                this.orders = res.data.orders
                this.form = this.order

                //Initiate methods
                this.getPhones(this.order.patient_id,'phone')
                this.getDiagnostics(this.order.patient_id)
                this.getAddress(this.order.patient_id, 'address');
                this.getPatient(this.order.patient_id);
            })
        },
        async getPatient(id) {
            await axios.get(`api/patients/${id}`).then((res) => {
                this.patient = res.data
                this.animation_wait = false
            })
        },
        async getPhones(id, category) {
            await axios.get(`api/address_patient/${id}/${category}`).then((res) =>{
                this.phones = res.data;
            })
        },
        viewCreatePhone(){
            this.displayCreatePhone = true;
        },
        async getDiagnostics(id) {
            await axios.get(`api/diagnostic_patient/${id}`).then((res) => {
                this.diagnostics = res.data;
            })
        },
        viewCreateDiagnostic() {
            this.displayCreateDiagnostic = true;
        },
        async getAddress(id, category) {
            await axios.get(`api/address_patient/${id}/${category}`).then((res) =>{
                this.addreses = res.data;
            })
        },
        viewCreateAddress(){
            this.displayCreateAddress = true;
        },
        async getProducts() {
            await axios.get('api/products').then((res) => {
                this.products = res.data;
            })
        },
        async setDiagnostic(){
            this.diagnostic_idold = this.form.diagnostic_id;
        },

        async submitLm(order) {
            try {
                await axios.put(`/api/patient_lms/${order}`, this.form)
                return this.emitter.emit('patientLm_reload')
            }
            catch (e) {
                if(e.response) {
                    switch (e.response.status) {
                        case 422:
                            let err = e.response.data.errors
                            this.error_lm_code = err.lm_code ? err.lm_code[0]: null
                    }
                }
            }
        },
    },
    mounted () {
        this.edit_id = this.$props.editId
        this.getOrder();
        this.getProducts();
        
        this.emitter.on('photo_reload', ()=> {
            this.$toast.add({
                severity:'success', summary: 'SUCCESS',
                detail: 'Se a actualizado la información de la orden correctamente', life:3000
            })
        })
    }
}
</script>
