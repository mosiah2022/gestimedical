<template>
    <div>
        <div class="p-fluid">
            <div class="p-field">
                <label>Nombre</label>
                <InputText v-model="form.name" class="w-100" />
                <small class="text-red-500">{{ error_name }}</small>
            </div>
            <div class="p-field">
                <label>Presentacion <span class="pi pi-plus-circle justify-center cursor-pointer text-lime-600" @click="viewCreatePresentation" label="Nuevo"  /></label>
                <Dropdown v-model="form.presentation_id" :options="presentations" optionLabel="name" optionValue="id"
                placeholder="Seleccione una presentacion" class="w-100" />
                <small class="text-red-500">{{ error_presentation_id }}</small>
            </div>
            <div class="p-field">
                <label>Precio</label>
                <InputNumber v-model="form.price" placeholder="Ingrese un precio" :minFractionDigits="0" />
                <small class="text-red-500">{{ error_price }}</small>
            </div>
            <div class="p-field">
                <label>MetaData</label>
                <AutoComplete
                    v-model="selectedMetadata"
                    :suggestions="filteredMetadata"
                    :lazy="true"
                    :loading="loadingMetadata"
                    field="label"
                    forceSelection
                    complete-on-focus
                    placeholder="Buscar código metadata"
                    @complete="loadMetadata"
                    class="w-100"
                    panelStyle="max-height: 150px; overflow-y: auto;"
                />
            </div>

            <div class="p-field">
                <PrimeButton icon="pi pi-save" label="Guardar" class="sm:-bottom-1.5" @click="submit" />
            </div>
        </div>
        <Dialog :header="'Nueva presentacion'" :style="{width: '25vw'}"
            v-model:visible="displayCreatePresentation" :maxiizable="false">
            <CreatePresentation />
        </Dialog>
    </div>
</template>

<script>
import axios from "axios";
import CreatePresentation from "@/Pages/Presentations/CreatePresentation";

export default {
    name: "ProductForm",
    components: {CreatePresentation},
    data () {
        return {
            form: {
                name: null,
                price: null,
                presentation_id: null,
                observation: null,
            },
            presentations: [],
            brands: [],
            error_name: null,
            error_presentation_id: null,
            error_price: null,
            displayCreatePresentation: false,
            selectedMetadata: null,
            filteredMetadata: [],
            loadingMetadata: false,
            metadataPage: 1,
            metadataTotal: 0,
            error_product_metadata_code: null,
        }
    },
    props: {
        editId: Number,
    },
    methods: {

        async loadMetadata(event) {
            this.loadingMetadata = true;
            try {
                const res = await axios.get('/api/product_metadata', {
                    params: {
                        search: event.query,
                        product_id: this.form.id ?? this.$props.editId,
                        page: 1,
                        per_page: 100
                    }
                });

                this.filteredMetadata = res.data.data.map(item => ({
                    label: `${item.code} - ${item.name}`,
                    code: item.code
                }));
                this.metadataTotal = res.data.total;

            } catch (error) {
                console.error('Error cargando metadata', error);
            }
            this.loadingMetadata = false;
        },

        async getPresentations () {
            await axios.get('api/presentations').then((res) => {
                this.presentations = res.data
            })
        },
        cleanForm () {
            Object.keys(this.form).map((val, index) => this.form[index] = null)
        },
        async submit () {
            if (!this.$props.editId) {
                try {
                    const res = await axios.post('/api/products', this.form)
                    this.cleanForm()
                    return this.emitter.emit('products_reload')
                }
                catch (e) {
                    if (e.response) {
                        switch (e.response.status) {
                            case 422:
                                let err = e.response.data.errors
                                this.error_name = err.name ? err.name[0] : null
                                this.error_presentation_id = err.presentation_id ? err.presentation_id[0]: null
                                this.error_price = err.price ? err.price[0]: null
                        }
                    }
                    return null
                }
            }
            try {
                const res = await axios.put(`/api/products/${this.$props.editId}`, this.form)
                this.cleanForm()
                return this.emitter.emit('products_reload')
            }
            catch (e) {
                if (e.response) {
                    switch (e.response.status) {
                        case 422:
                            let err = e.response.data.errors
                            this.error_name = err.name ? err.name[0] : null
                            this.error_presentation_id= err.presentacion_id ? err.presentacion_id[0] : null
                            this.error_price = err.price ? err.price[0] : null
                    }
                }
                return null
            }
        },
        async getEditData() {
            const res = await axios.get(`/api/products/${this.$props.editId}`)
            this.form.name             = res.data.name
            this.form.presentation_id  = res.data.presentation_id
            this.form.price            = parseFloat(res.data.price)

            // 👇 Buscar si ya está en filteredMetadata
            const found = this.filteredMetadata.find(
                item => item.code === res.data.product_metadata_code
            );

            if (found) {
                this.selectedMetadata = found;
            } else {
                // fallback si no está cargado aún
                this.selectedMetadata = {
                    code: res.data.product_metadata?.code,
                    label: `${res.data.product_metadata?.code ?? ''} - ${res.data.product_metadata?.name ?? ''}`
                }
            }
        },
        viewCreatePresentation() {
            this.displayCreatePresentation = true;
        }
    },
    mounted() {
        this.getPresentations()
        if (this.$props.editId) {
            this.getEditData()
        }

        this.emitter.on('createpresentation_reload', () => {
            this.displayCreatePresentation = false;
            this.getPresentations()
            this.$toast.add({
                severity: 'success', summary: 'SUCCESS!',
                detail: `Presentacion cargado`, life:3000,
            })
        });
    },
    watch: {
        selectedMetadata(newVal) {
            this.form.product_metadata_code = newVal?.code || null;
        }
    }
}
</script>

<style scoped>

</style>
