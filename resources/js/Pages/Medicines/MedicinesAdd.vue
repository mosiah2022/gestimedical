<template>
    <div class="card">
        <div class="formgrid grid">
            <div class="field col">
                <label class="font-bold text-teal-500">Seleccione Medicamento<span class="pi pi-plus-circle justify-center cursor-pointer text-lime-600" @click="viewCreateProduct" label="Nuevo"  /></label>
                <Dropdown class="w-full"
                    v-model="formprod.product_id"
                    :options="medicines"
                    optionLabel="full_name"
                    optionValue="id"
                    :filter="true"
                    filterPlaceholder="Buscar medicamento"
                    :showClear="true"
                    @change="handleChange"
                />
            </div>
             <div class="field col">
                <label class="font-bold text-teal-500">Cantidad</label>
                <InputNumber v-model="formprod.prescription" class="w-full" placeholder="Ingrese una cantidad" :minFractionDigits="0" />
            </div>
            <div class="field col" v-if="save_action === true">
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

import ProductForm from "@/Components/Products/ProductForm";
import Swal from "sweetalert2";
import axios from "axios";

    export default {
        name: "MedicineAdd",
        components: {
            ProductForm
        },
        data() {
            return {
                editingRows: [],
                medicines: [],
                details: [],
                filter: [],
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
            async getMedicines() {
                await axios.get('api/getMedicines').then((res) => {
                    this.medicines = res.data
                })
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
            this.getMedicines();
            this.formprod.order_id = this.$props.order_id;
            this.formprod.patient_id = this.$props.patient_id;
            this.getDetailLms(this.formprod.order_id);

            this.emitter.on('products_reload', () => {
                this.getMedicines()
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
