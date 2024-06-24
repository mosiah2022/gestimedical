<template>
    <div class="card">
        <span v-if="animation_wait === true" class="justify-center">Espere un momento por favor<ProgressSpinner  /></span>
        <div class="formgrid grid">
            <div class="field col">
                <label class="font-bold text-teal-500">Paciente - <span>{{patient.personal_id}} </span></label>
                <InputText v-model="patient.full_name" class="inputfield w-full" disabled="true" />
            </div>
            <div class="field col">
                <label class="font-bold text-teal-500">Fecha de generación</label>
                <Calendar id="icon" v-model="form.date_ini" :showIcon="true" dateFormat="dd/mm/yy" class="w-full" />
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
            <label class="font-bold text-teal-500">Coloque nombre y apellido del Doctor</label>
            <InputText v-model="form.doctor_name" class="inputfield w-full"  />
        </div>        
        <MedicinesAdd :order_id="$props.editId" :patient_id="$props.patient_id" />

        <div class="formgrid grid mt-4">
            <div class="field col">
                <label class="font-bold text-teal-500">Código autorización - LM | EC | ARL</label>
                <InputText v-model="form.lm_code" class="inputfield w-full" />
                <small class="text-red-500">{{ error_lm_code }}</small>
            </div>
            <div class="field col">
                <label class="font-bold text-teal-500">Autorizado por:</label>
                <InputText v-model="form.authorized_by" class="inputfield w-full" />
            </div>
        </div>
        <div class="field col">
            <label class="font-bold text-teal-500">Observaciones</label>
            <InputText v-model="form.observation" class="inputfield w-full" />
        </div>
        <div class="field-checkbox my-4">
            <Checkbox id="copago" name="copago" value="0" v-model="copago_check" :binary="true" />
            <label>Indique si tiene copago</label>
        </div>
        <div class="field col" v-if="copago_check === true">
            <h5>Seleccione el Copago  {{ form.discount_percent }} %</h5>
            <Slider v-model="form.discount_percent" :min="0" :max="100" />
        </div>

        <FileUpload
            name="file"
            @uploader="uploadFiles($event)"
            :multiple="true"
            customUpload
            :maxFileSize="1000000"
            chooseLabel="Seleccionar"
            uploadLabel="Subir"
            cancelLabel="Cancelar"
        >
            <template #empty>
                <span>Arrastre y suelte los archivos que quiera adjuntar a la orden</span>
            </template>
        </FileUpload>

        <ConfirmPopup></ConfirmPopup>

        <h2 v-if="uploadedFiles" class="font-bold text-lg text-blue-800 mt-4">Archivos adjuntos a la orden</h2>
        <DataTable :value="uploadedFiles" tableStyle="min-width: 50rem" dataKey="id">
            <Column field="name" class="lowercase">
                <template #header>
                    <span class="capitalize">Nombre</span>
                </template>
                <template #body="slotProps">
                    <span class="lowercase">{{ slotProps.data.name }}</span>
                </template>
            </Column>
            <Column bodyStyle="justify-end" header="Acción" headerStyle="width: 14rem; justify-center">
                <template #body="slotProps">
                    <a :href="slotProps.data.uri" target="_blank" title="Detalle">
                        <i class="pi pi-eye mr-2 text-blue-600 font-bold" style="font-size: 1rem"></i>
                    </a>
                    <a href="#" title="Eliminar" @click.prevent="removeFile($event, slotProps.data.id,slotProps.data.name)">
                        <i class="pi pi-trash mr-2 text-red-600 font-bold" style="font-size: 1rem"></i>
                    </a>
                </template>
            </Column>
        </DataTable>

        <div class="field flex justify-end mt-4">
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
import DataTable from 'primevue/datatable'
import { values } from 'lodash'
import Column from 'primevue/column'
import Swal from 'sweetalert2'

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
                discount_percent: null,
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
            pdfFile: null,
            filename: null,
            files: [],
            uploadedFiles: null
        }
    },
    props: {
        editId: Number,
        patient_id: Number
    },
    methods: {
        checkFile(id) {
            console.log(id)
        },
        removeFile(event, id,fileName) {
            this.$confirm.require({
                target: event.currentTarget,
                message: 'Seguro de eliminar este archivo?',
                icon: 'pi pi-info-circle',
                rejectProps: {
                    label: 'Cancelar',
                    severity: 'secondary',
                    outlined: true
                },
                acceptProps: {
                    label: 'Borrar',
                    severity: 'danger'
                },
                accept: () => {
                    axios.post('/api/delete_file', { id:id, name: fileName, patient_id: this.patient_id }).then(response => {
                        this.uploadedFiles = this.uploadedFiles.filter(file => file.id !== id); 
                    })
                    this.$toast.add({ severity: 'info', summary: 'Confirmed', detail: 'Archivo eliminado', life: 3000 });
                },
                reject: () => {
                    this.$toast.add({ severity: 'error', summary: 'Rejected', detail: 'No se logro eliminar el registro', life: 3000 });
                }
            })
        },
        async uploadFiles(event) {
            this.files = event.files;
            if (this.files && this.files.length > 0) {
                let currentObj = this;
                const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }
                let formData = new FormData();
                for (let i = 0; i < this.files.length; i++) {
                    formData.append('files[]', this.files[i]);
                }
                formData.append('patient_id', this.$props.patient_id);
             
                axios.post('/api/store_file', formData, config).then(function (response){
                    currentObj.success = response.data.success;
                    currentObj.filename = "";
                    console.log("Se a guardado correctamente el archivo");
                    return this.emitter.emit('photo_reload')               
                })
                .catch(function (error){
                    currentObj.output = error;
                })

                this.filename = "";
                this.files = [];
                return this.emitter.emit('photo_reload');
            }
        },
        async getListFiles() {
            await axios.get(`/api/list_files/${this.patient_id}`)
                .then(response => {
                this.uploadedFiles = response.data.files;
                })
                .catch(error => {
                console.error('Error fetching files:', error);
                });
        },
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
        this.getListFiles();
        
        this.emitter.on('photo_reload', ()=> {
            this.$toast.add({
                severity:'success', summary: 'SUCCESS',
                detail: 'Se a actualizado la información de la orden correctamente', life:3000
            })
        })
    }
}
</script>
