<template>
    <div class="card">

        <div class="formgrid grid">

            <div class="field col-12 md:col-6">
                    <label class="font-bold text-teal-500 flex items-center gap-2">
                        <span>Seleccione Medicamento</span>
                        <i
                        class="pi pi-plus-circle cursor-pointer text-lime-600"
                        @click="viewCreateProduct"
                        title="Nuevo"
                        />
                    </label>
                    <AutoComplete
                        class="w-full"
                        v-model="selectedMedicine"
                        :suggestions="medicineOptions"
                        field="name"
                        @complete="searchMedicines"
                        :minLength="0"
                        :dropdown="true"
                        :forceSelection="false"
                        :virtualScrollerOptions="{ itemSize: 40 }"
                        :loading="searchLoading"
                        placeholder="Buscar medicamento"
                        appendTo="body"
                        @item-select="onSelectMedicine"
                        @clear="onClearMedicine"
                    >
                    <template #option="{ option }">
                        <div class="flex flex-col w-full">
                            <span class="whitespace-normal break-words text-sm">{{ option.name }}</span>
                            <small class="text-gray-500">{{ formatCurrency(option.price ?? 0) }}</small>
                        </div>
                    </template>
                </AutoComplete>
            </div>

             <div class="field col">
                <label class="font-bold text-teal-500">Cantidad</label>
                <InputNumber v-model="formprod.prescription" class="w-full" placeholder="Ingrese una cantidad" :minFractionDigits="0" />
            </div>

            <div class="field col" v-if="save_action">
                <label class="font-bold text-teal-500">Acciones</label>
                <PrimeButton
                    icon="pi pi-plus"
                    label="Guardar"
                    class="w-full"
                    :disabled="!formprod.product_id || !formprod.prescription || formprod.price_detail == null"
                    @click="add" />
            </div>

    </div>

        <div>
            <span class="justify-center" v-if="animation_wait === true">Espere un momento por favor <ProgressSpinner /></span>
            <DataTable
                :filters="filter"
                :value="details"
                dataKey="id"
                responsiveLayout="scroll"
                editMode="row"
                v-model:editingRows="editingRows"
                @row-edit-save="onRowEditSave"
                :paginate="true" :rows="20"
                class="editable-cells-table"
                :rowClass="rowClass"
            >
                <Column field="products.name" header="Medicamento">
                    <template #editor="{data}">
                        <InputText v-model="data.products.name" autofocus />
                    </template>
                </Column>
                <Column field="prescription">
                    <template #header>
                        <span class="flex justify-center">Cantidad</span>
                    </template>
                    <template #body="slotProps">
                        <span class="flex justify-center">{{ slotProps.data.prescription }}</span>
                    </template>
                </Column>
                <Column field="products.price" dataType="numeric">
                    <template #header>
                        <span class="text-right w-full block">Precio</span>
                    </template>
                    <template #body="slotProps">
                        <span class="text-right w-full block font-medium">{{ formatCurrency(slotProps.data.products.price) }}</span>
                    </template>
                    <template #editor="{data}" class="flex justify-end font-medium">
                        <!-- {{ data.products.price[field] }} -->
                        <InputText v-model="data.products.price" autofocus />
                    </template>
                </Column>
                <Column>
                    <template #header>
                        <span class="text-right w-full block">Total</span>
                    </template>
                    <template #body="{data}">
                        <span class="text-right w-full block font-bold italic">{{ formatCurrency(data.products.price*data.prescription) }}</span>
                    </template>
                </Column>
                <Column :rowEditor="true" style="width:10%; min-width:8rem" bodyStyle="text-align:center"></Column>
                <Column bodyStyle="text-align: center; overflow: visible" header="Acción"
                        headerStyle="width: 14rem; text-align: center">
                    <template #body="slotProps">
                        <PrimeButton class="del-btn" @click="editProduct(slotProps.data.products.id)" icon="pi pi-pencil" title="editar medicamento" />
                        <PrimeButton class="-right-2.5 del-btn" @click="destroyItem(slotProps.data.id)" icon="pi pi-trash" title="borrar" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <Dialog :header="editId === null ? 'Crear Producto' : 'Editar Producto'" :style="{width: '25vw'}"
                v-model:visible="displayCreateProduct" :maximizable="false" >
            <ProductForm :editId="editId" />
        </Dialog>
    </div>
</template>

<script>
import AutoComplete from "primevue/autocomplete";
import ProductForm from "@/Components/Products/ProductForm";
import Swal from "sweetalert2";
import axios from "axios";

    export default {
        name: "MedicineAdd",
        components: {
            ProductForm,
            AutoComplete
        },
        data() {
            return {
                editingRows: [],
                details: [],
                filter: [],
                selectedMedicine: null,
                medicineOptions: [],
                searchLoading: false,
                cancelSrc: null,
                abortCtrl: null,
                appendToTarget: null,
                formprod: {
                    product_id: null,
                    order_id: null,
                    patient_id: null,
                    prescription: null,
                    price_detail: null,
                },
                displayCreateProduct: false,
                editId: null,
                save_action:true,
                animation_wait: false
            }
        },
        props: {
            order_id: Number,
            patient_id: Number
        },
        methods: {
            onRowEditSave(event) {
                const res = axios.patch(`api/update_price/${event.data.products.id}`, {
                    price: event.data.products.price,
                    name: event.data.products.name
                }).then
                return this.emitter.emit('price_update_reload')
            },
            cleanFormMed () {
                Object.keys(this.formprod).forEach((key) => {
                    this.formprod[key] = null; // en vez de this.formprod[index] = ''
                });
            },
            async searchMedicines(event) {
                const q = (event?.query ?? "").trim();
                //console.log('[AC] query =', q);

                // cancela petición previa si el usuario sigue tecleando
                if (this.abortCtrl) this.abortCtrl.abort();
                this.abortCtrl = new AbortController();

                this.searchLoading = true;
                try {
                    const { data } = await axios.get('api/medicines/search', {
                        params: { q, limit: 30 },
                        signal: this.abortCtrl.signal,
                        withCredentials: true,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    this.medicineOptions = Array.isArray(data) ? data : [];
                } catch (e) {
                    if (e.name !== 'CanceledError' && e.name !== 'AbortError') {
                        console.warn('AC error', e?.response?.status, e?.message);
                        this.$toast?.add?.({ severity:'error', summary:'Búsqueda', detail:'No se pudo buscar', life:1500 });
                    }
                this.medicineOptions = [];
                } finally {
                    this.searchLoading = false;  // ✅ el spinner se apaga siempre
                }

            },
            onSelectMedicine(e) {
                const med = e?.value;
                if (!med) return this.onClearMedicine();
                this.selectedMedicine = med;
                this.formprod.product_id   = med.id;
                this.formprod.price_detail = Number(med.price ?? 0);
            },

            onClearMedicine() {
                this.selectedMedicine = null;
                this.formprod.product_id   = null;
                this.formprod.price_detail = null;
            },
            async add () {
                // Validaciones mínimas para evitar enviar null
                if (!this.formprod.product_id) {
                    this.$toast.add({ severity:'warn', summary:'Falta medicamento', detail:'Seleccione un medicamento', life:2000 });
                    return;
                }
                if (!this.formprod.prescription || Number(this.formprod.prescription) <= 0) {
                    this.$toast.add({ severity:'warn', summary:'Cantidad inválida', detail:'Ingrese una cantidad mayor a 0', life:2000 });
                    return;
                }
                if (this.formprod.price_detail == null) {
                    this.$toast.add({ severity:'warn', summary:'Precio no definido', detail:'Espere a que cargue el precio', life:2000 });
                    return;
                }

                this.save_action = false;
                this.animation_wait = true;

                try {
                    await axios.post('api/patient_lm_details', this.formprod);

                    // Guarda antes de limpiar para no perderlo
                    const orderId = this.formprod.order_id;

                    this.cleanFormMed();
                    // Restituye order_id y patient_id para que el flujo siga igual
                    this.formprod.order_id = orderId;
                    this.formprod.patient_id = this.$props.patient_id;

                    this.getDetailLms(orderId);
                    this.save_action = true;
                    return this.emitter.emit('patient_lm_detail_reload');
                }
                catch (e) {
                    if (e.response && e.response.status === 422) {
                    // Manejo 422 si lo necesitas
                    }
                    return null;
                }
                finally {
                    this.animation_wait = false;
                }
            },
            async getDetailLms(id) {
                await axios.get(`api/showlmdetail/${id}`).then((res) => {
                    this.details = res.data;

                    console.log(this.details.map(d => ({
                        name: d.products?.name,
                        exists: d.products?.exists_in_metadata
                    })));

                    this.animation_wait = false
                })
            },
            formatCurrency(value) {
                return value.toLocaleString('en-US', {style: 'currency', currency: 'USD', minimumFractionDigits:0});
            },
            viewCreateProduct() {
                this.editId = null;
                this.displayCreateProduct = true;
            },
            editProduct(id) {
                this.editId = id
                this.displayCreateProduct = true;
            },
            async destroyItem(id) {
                this.animation_wait = true;
                axios.delete(`/api/patient_lm_details/${id}`).then(() => {
                    this.animation_wait = false
                    return this.emitter.emit('patient_lm_destroy_reload')
                })
            },
            async handleChange(event) {
                const product_id = event.value;

                // Si limpiaron el dropdown, resetea el precio
                if (!product_id) {
                    this.formprod.price_detail = null;
                    return;
                }

                try {
                    const { data } = await axios.get(`/api/products/${product_id}`);
                    const price = Number(data?.price);
                    this.formprod.price_detail = Number.isFinite(price) ? price : 0; // nunca null
                } catch (e) {
                    this.formprod.price_detail = null; // fuerza validación
                    this.$toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo cargar el precio del producto',
                    life: 2000,
                    });
                }
            },
            rowClass(data) {
                const exists = data.products?.exists_in_metadata;
                return exists ? 'row-green' : 'row-red';
            }
        },
        mounted(){
            this.formprod.order_id = this.$props.order_id;
            this.formprod.patient_id = this.$props.patient_id;
            this.getDetailLms(this.formprod.order_id);
            this.appendToTarget = document.body;

            this.emitter.on('products_reload', () => {
                this.selectedMedicine = null;
                this.displayCreateProduct = false;
                this.$toast.add({
                    severity:'success', summary: 'SUCCESS!',
                    detail: `Medicamento creado con exito!`, life:3000,
                })
            })
            this.emitter.on('patient_lm_detail_reload', () => {
                this.getDetailLms(this.formprod.order_id);
                this.$toast.add({
                    severity:'success', summary: 'SUCCESS!',
                    detail: `Item agregado exitosamente`, life:3000,
                })
            })
            this.emitter.on('patient_lm_destroy_reload', () => {
                this.getDetailLms(this.formprod.order_id);
                this.$toast.add({
                    severity:'success', summary: 'SUCCESS!',
                    detail: `Item eliminado`, life:3000,
                })
            })
        }

    }
</script>

<style>
.row-green {
    background-color: #e0f9e0 !important; /* Verde suave */
}

.row-red {
    background-color: #ffe0e0 !important; /* Rojo suave */
}
</style>
