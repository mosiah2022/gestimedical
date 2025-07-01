<template>
    <div>
        <div class="formgrid grid">
            <div class="field col">
                <label>Ciudad</label>
                <Dropdown class="w-full" v-model="form.city_id" :options="cities" optionLabel="city" optionValue="id" placeholder="Selecione ciudad" :filter="true" />
                <small class="text-red-500">{{ error_city_id }}</small>
            </div>
            <div class="field col">
                <label>Nombre</label>
                <InputText v-model="form.first_name" class="w-full" />
                <small class="text-red-500">{{ error_first_name }}</small>
            </div>
            <div class="field col">
                <label>Apellido</label>
                <InputText v-model="form.last_name" class="w-full" />
                <small class="text-red-500">{{ error_last_name }}</small>
            </div>
        </div>

        <div class="formgrid grid">
            <!-- Tipo de documento -->
            <div class="field col">
                <label>Tipo de documento</label>
                <Dropdown v-model="form.type_document" :options="documentTypes" class="w-full" placeholder="Seleccione tipo" />
            </div>

            <!-- Sexo -->
            <div class="field col">
                <label>Sexo</label>
                <Dropdown v-model="form.sex" :options="sexOptions" class="w-full" placeholder="Seleccione sexo" />
            </div>

            <!-- Fecha de nacimiento -->
            <div class="field col">
                <label>Fecha de nacimiento</label>
                <Calendar  v-model="form.birthday"
                    class="w-full"
                    dateFormat="yy-mm-dd"
                    showIcon
                    showButtonBar
                    view="date"
                    monthNavigator
                    yearNavigator
                    yearRange="1900:2050"
                />
            </div>
        </div>

        <div class="formgrid grid">
            <!-- Ciudad de nacimiento -->
            <div class="field col">
                <label>Ciudad de nacimiento</label>
                <Dropdown class="w-full" v-model="form.born_city_id" :options="cities" optionLabel="city" optionValue="id" placeholder="Seleccione ciudad" :filter="true" />
            </div>

            <!-- Dirección -->
            <div class="field col">
                <label>Dirección</label>
                <InputText v-model="form.address" class="w-full" />
            </div>

            <!-- Teléfono -->
            <div class="field col">
                <label>Teléfono</label>
                <InputText v-model="form.phone" class="w-full" />
            </div>
        </div>

        <div class="formgrid grid">
            <!-- Email -->
            <div class="field col">
                <label>Email</label>
                <InputText v-model="form.email" class="w-full" />
            </div>

            <!-- Tipo de usuario -->
            <div class="field col">
                <label>Tipo de Usuario</label>
                <Dropdown v-model="form.type_user" :options="userTypes" class="w-full" placeholder="Seleccione tipo" />
            </div>

            <!-- Incapacidad -->
            <div class="field col">
                <label>¿Incapacidad?</label>
                <Dropdown v-model="form.disability" :options="disabilityOptions" class="w-full" placeholder="Seleccione" />
            </div>
        </div>

        <div class="formgrid grid">
            <div class="field col">
                <label>Identificacion</label>
                <InputText v-model="form.personal_id" class="w-full" />
                <small class="text-red-500">{{ error_personal_id }}</small>
            </div>
            <div class="field col">
                <label>Edad</label>
                <InputNumber v-model="form.age" class="w-full" />
                <small class="text-red-500">{{ error_age }}</small>
            </div>
            <div class="field col mt-4" v-if="next_view === true">
                <PrimeButton icon="pi pi-save" :label="editId === null ? 'Siguiente':'Guardar'" class="sm:-bottom-1.5" @click="submit" />
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "PatientForm",
    data () {
        return {
            form: {
                first_name: null,
                last_name: null,
                personal_id: null,
                age: null,
                city_id: null,
                type_document: null,
                sex: null,
                birthday: null,
                born_city_id: null,
                address: null,
                phone: null,
                email: null,
                type_user: null,
                disability: null,
            },
            cities: [],
            error_first_name: null,
            error_last_name: null,
            error_personal_id: null,
            error_age: null,
            error_city_id: null,
            next_view: true,
            documentTypes: ['OTRO', 'CONSUMIDOR FINAL', 'REGISTRO CIVIL', 'TI', 'CC', 'TE', 'Cédula de extranjería', 'NIT', 'PASAPORTE', 'Documento de identidad', 'Sin identificación', 'Permiso especial', 'NUIP'],
            sexOptions: ['male', 'female'],
            userTypes: ['Contributivo cotizante', 'Contributivo beneficiario', 'Contributivo adicional', 'Subsidiado', 'Sin régimen', 'Especiales o de Excepción cotizante', 'Especiales o de Excepción beneficiario', 'Particular', 'Tomador/Amparo ARL', 'Tomador/Amparo SOAT', 'Tomador/Amparo Planes voluntarios de salud'],
            disabilityOptions: ['Si', 'NO'],
        }
    },
    props: {
        editId: Number,
    },

    methods: {
        async getCities () {
            await axios.get('api/cities').then((res) => {
                this.cities = res.data
            })
        },

        cleanForm () {
            Object.keys(this.form).map((val, index) => this.form[index] = null)
        },

        formatDate(date) {
            if (!date) return null;
            const d = new Date(date)
            const year = d.getFullYear()
            const month = String(d.getMonth() + 1).padStart(2, '0')
            const day = String(d.getDate()).padStart(2, '0')
            return `${year}-${month}-${day}`
        },
        async submit () {

            this.next_view = false
            this.form.birthday = this.formatDate(this.form.birthday)

            if (!this.$props.editId) {
                try {
                    const res = await axios.post('/api/patients', this.form)
                    this.cleanForm()
                    return this.emitter.emit('patients_reload', res.data)
                }
                catch (e) {
                    if (e.response) {
                        this.next_view = true
                        switch (e.response.status) {
                            case 422:
                                let err = e.response.data.errors
                                this.error_first_name = err.first_name ? err.first_name[0] : null
                                this.error_last_name = err.last_name ? err.last_name[0] : null
                                this.error_personal_id = err.personal_id ? err.personal_id[0] : null
                                this.error_city_id = err.city_id ? err.city_id[0] : null
                                this.error_age = err.age ? err.age[0] : null
                        }
                    }
                    return null
                }
            }
            try {
                this.next_view = true
                const res = await axios.put(`/api/patients/${this.$props.editId}`, this.form)
                return this.emitter.emit('patient_update_reload')
            }
            catch (e) {
                if (e.response) {
                    switch (e.response.status) {
                        case 422:
                            let err = e.response.data.errors
                            this.error_first_name = err.first_name ? err.first_name[0] : null
                            this.error_last_name = err.last_name ? err.last_name[0] : null
                            this.error_personal_id = err.personal_id ? err.personal_id[0] : null
                            this.error_age = err.age ? err.age[0] : null
                            this.error_city_id = err.city_id ? err.city_id[0] : null
                    }
                }
                return null
            }
        },
        async getEditData() {
            const res = await axios.get(`/api/patients/${this.$props.editId}`)
            this.form.first_name     = res.data.first_name
            this.form.last_name      = res.data.last_name
            this.form.personal_id    = res.data.personal_id
            this.form.age            = res.data.age
            this.form.city_id        = res.data.city_id
            this.form.type_document  = res.data.type_document
            this.form.sex            = res.data.sex
            this.form.birthday       = res.data.birthday
            this.form.born_city_id   = res.data.born_city_id
            this.form.address        = res.data.address
            this.form.phone          = res.data.phone
            this.form.email          = res.data.email
            this.form.type_user      = res.data.type_user
            this.form.disability     = res.data.disability
        }
    },
    mounted() {
        this.getCities();
        if (this.$props.editId) {
            this.getEditData()
        }
    }
}
</script>

<style scoped>

</style>
