<template>

        <div class="flex justify-end p-2">
            <PrimeButton @click="createProduct" class="add-btn" icon="pi pi-plus" title="nuevo" />
        </div>

        <DataTable v-model:filters="filters1" :value="products" :totalRecords="totalRecords" dataKey="id"
                stripedRows
                lazy paginator
                responsiveLayout="scroll"
                :paginator="true"
                :rows="perPage"
                :loading="loading1"
                :globalFilterFields="['global', 'name']"
                @page="handlePageChange">
            <template #header>
                <div class="flex justify-content-center">
                    <span class="p-input-icon-left w-full">
                        <i class="pi pi-search" />
                        <InputText v-model="filters1.global.value" placeholder="Buscar" class="w-full" @input="handleFilterChange" />
                    </span>
                </div>
            </template>
            <Column field="name" header="Nombre"></Column>
            <Column field="company.name" header="Compañía">
                <template #body="slotProps">
                {{ slotProps.data.company?.name || '-' }}
                </template>
            </Column>
            <Column bodyStyle="text-align: center; overflow: visible" header="Acción"
                    headerStyle="text-align: center">
                <template #body="slotProps">
                    <PrimeButton class="edit_btn" @click="editProduct(slotProps.data.id)" icon="pi pi-pencil" title="editar" />
                    <PrimeButton class="-right-2.5 del-btn" @click="destroyProduct(slotProps.data.id)" icon="pi pi-trash" title="borrar" />
                </template>
            </Column>
        </DataTable>

    <Dialog :header="editId === null ? 'Crear Producto' : 'Editar Producto'" :style="{width: '50vw'}"
            v-model:visible="display">
        <ProductForm :editId="editId" />
    </Dialog>
</template>

<script>
import ProductForm from "@/Components/Products/ProductForm";
import Swal from 'sweetalert2'
import axios from 'axios';
import {FilterMatchMode, FilterOperator} from "primevue/api";
import debounce from 'lodash/debounce';

export default {
  data() {
    return {
      products: [],
      editId: null,
      display: false,
      totalRecords: 0,
      currentPage: 1,
      perPage: 10,
      loading1: false,
      filters1: {
        global: { value: '' }
      },
      lazyParams: {
        first: 0,
        rows: 10,
        sortField: null,
        sortOrder: null,
        filters: {}
      }
    };
  },
  components: {
    ProductForm
  },
  methods: {
        async loadLazyData() {
            this.loading1 = true;
            try {
                const response = await axios.get('api/products', {
                params: {
                    page: this.currentPage,
                    perPage: this.lazyParams.rows,
                    filters: this.filters1.global.value
                }
                });
                this.products = response.data.products;
                this.totalRecords = response.data.totalRecords;
                this.loading1 = false;
            } catch (error) {
                console.error('Error fetching products:', error);
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
        async createProduct () {
            this.editId = null
            this.display = true
        },
        async editProduct (id) {
            this.editId = id
            this.display = true
        },
        async destroyProduct(id) {
            Swal.fire( {
                title: 'Seguro de eliminar el producto?',
                showDenyButton: true,
                confirmButtonText: `Borrar`,
                denyButtonText: `No borrar`,
            }).then((result) => {
                if(result.isConfirmed) {
                    axios.delete(`/api/products/${id}`).then(() => {
                        return this.emitter.emit('products_reload')
                    }).catch(() => {
                        Swal.fire('No se logro eliminar', '', 'error')
                    })
                } else if (result.isDenied) {
                    Swal.fire('No se a borrado...', '', 'info')
                }
            })
        },
        initFilters1() {
            this.filters1 = {
                'global': {value:null, matchMode:FilterMatchMode.CONTAINS},
                'name':{operator: FilterOperator.AND, constraints: [{value:null, matchMode: FilterMatchMode.STARTS_WITH}]}
            }
        }
    },
    created() {
        this.initFilters1()
    },
    mounted() {
        this.loadLazyData()
        this.emitter.on('products_reload', () => {
            this.loadLazyData()
            this.display = false
            this.$toast.add({
                severity:'success', summary: 'SUCCESS!',
                detail: `Operación realizada con éxito!`, life:3000,
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
        height: 24px;
        width: 24px;
}
.edit_btn{
    color: blue;
    background-color: transparent;
    border-width: 0;
    width: 24px;
    height: 24px;
}
.add-btn{
    margin-bottom: 20px;
    border-radius: 50%;
}
</style>
