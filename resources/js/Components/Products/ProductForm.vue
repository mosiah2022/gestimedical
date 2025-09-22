<template>
  <div>
    <div class="p-fluid">
      <div class="p-field">
        <label>Nombre</label>
        <InputText v-model="form.name" class="w-100" />
        <small class="text-red-500">{{ error_name }}</small>
      </div>

      <div class="p-field">
        <label>
          Presentacion
          <span
            class="pi pi-plus-circle justify-center cursor-pointer text-lime-600"
            @click="viewCreatePresentation"
            label="Nuevo"
          />
        </label>
        <Dropdown
          v-model="form.presentation_id"
          :options="presentations"
          optionLabel="name"
          optionValue="id"
          placeholder="Seleccione una presentacion"
          class="w-100"
        />
        <small class="text-red-500">{{ error_presentation_id }}</small>
      </div>

      <div class="p-field">
        <label>Precio</label>
        <InputNumber
          v-model="form.price"
          placeholder="Ingrese un precio"
          :minFractionDigits="0"
        />
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

      <div class="p-field" v-if="editId">
        <label>Compañía</label>
        <InputText :value="companyDisplay" class="w-100" disabled />
        <small class="text-gray-500">Este valor proviene del producto y no puede editarse.</small>
      </div>

      <div class="p-field">
        <PrimeButton icon="pi pi-save" label="Guardar" class="sm:-bottom-1.5" @click="submit" />
      </div>
    </div>

    <Dialog
      :header="'Nueva presentacion'"
      :style="{ width: '25vw' }"
      v-model:visible="displayCreatePresentation"
      :maximizable="false"
    >
      <CreatePresentation />
    </Dialog>
  </div>
</template>

<script>
import axios from "axios";
import CreatePresentation from "@/Pages/Presentations/CreatePresentation";
// ❌ No importes un Dropdown local: PrimeVue ya registra el suyo globalmente
// import Dropdown from "../Dropdown.vue";

export default {
  name: "ProductForm",
  components: { CreatePresentation },
  props: {
    editId: Number,
  },
  data() {
    return {
      form: {
        id: null,
        name: null,
        price: 0,
        presentation_id: null,
        observation: null,
        product_metadata_code: null,
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

      // 👇 para mostrar la compañía en solo lectura
      companyDisplay: "-",
    };
  },
  methods: {
    async loadMetadata(event) {
      this.loadingMetadata = true;
      try {
        const res = await axios.get("/api/product_metadata", {
          params: {
            search: event.query,
            product_id: this.form.id ?? this.$props.editId,
            page: 1,
            per_page: 100,
          },
        });

        this.filteredMetadata = (res.data.data || []).map((item) => ({
          label: `${item.code} - ${item.name}`,
          code: item.code,
        }));
        this.metadataTotal = res.data.total ?? 0;
      } catch (error) {
        console.error("Error cargando metadata", error);
      }
      this.loadingMetadata = false;
    },

    async getPresentations() {
      const res = await axios.get("/api/presentations");
      // Asegura IDs numéricos para que el Dropdown seleccione correctamente
      this.presentations = (res.data || []).map((r) => ({
        ...r,
        id: Number(r.id),
      }));
    },

    cleanForm() {
      Object.keys(this.form).forEach((k) => (this.form[k] = null));
      this.form.price = 0;
    },

    async submit() {
      if (!this.$props.editId) {
        try {
          await axios.post("/api/products", this.form);
          this.cleanForm();
          return this.emitter.emit("products_reload");
        } catch (e) {
          if (e.response && e.response.status === 422) {
            const err = e.response.data.errors || {};
            this.error_name = err.name ? err.name[0] : null;
            this.error_presentation_id = err.presentation_id ? err.presentation_id[0] : null;
            this.error_price = err.price ? err.price[0] : null;
          }
          return null;
        }
      }
      try {
        await axios.put(`/api/products/${this.$props.editId}`, this.form);
        this.cleanForm();
        return this.emitter.emit("products_reload");
      } catch (e) {
        if (e.response && e.response.status === 422) {
          const err = e.response.data.errors || {};
          this.error_name = err.name ? err.name[0] : null;
          this.error_presentation_id = err.presentation_id ? err.presentation_id[0] : null; // 👈 corregido
          this.error_price = err.price ? err.price[0] : null;
        }
        return null;
      }
    },

    async getEditData() {
      const res = await axios.get(`/api/products/${this.$props.editId}`);
      // Soporta respuestas con o sin wrapper "data"
      const p = res.data?.data ?? res.data;

      // Helper: número seguro (evita NaN y soporta coma decimal)
      const numOrNull = (v) => {
        if (v === null || v === undefined || v === "") return null;
        const n = Number(String(v).replace(",", "."));
        return Number.isFinite(n) ? n : null;
      };

      // Seteo de campos
      this.form.id = p.id ?? null;
      this.form.name = p.name ?? "";
      this.form.presentation_id = numOrNull(p.presentation_id);
      this.form.price = numOrNull(p.price) ?? 0;

      // Compañía (solo lectura): nombre si viene la relación; si no, "ID x"; si nada, "-"
      const cid = numOrNull(p.company_id);
      if (p.company?.name) {
        this.companyDisplay = p.company.name;
      } else if (cid !== null) {
        this.companyDisplay = `ID ${cid}`;
      } else {
        this.companyDisplay = "-";
      }

      // MetaData (como ya la tenías)
      const found = this.filteredMetadata.find(
        (item) => item.code === p.product_metadata_code
      );
      if (found) {
        this.selectedMetadata = found;
      } else {
        this.selectedMetadata = {
          code: p.product_metadata?.code,
          label: `${p.product_metadata?.code ?? ""} - ${p.product_metadata?.name ?? ""}`,
        };
      }
    },

    viewCreatePresentation() {
      this.displayCreatePresentation = true;
    },
  },

  mounted() {
    (async () => {
      await this.getPresentations();
      if (this.$props.editId) {
        await this.getEditData();
      }
    })();

    this.emitter.on("createpresentation_reload", () => {
      this.displayCreatePresentation = false;
      this.getPresentations();
      this.$toast.add({
        severity: "success",
        summary: "SUCCESS!",
        detail: "Presentacion cargado",
        life: 3000,
      });
    });
  },

  watch: {
    selectedMetadata(newVal) {
      this.form.product_metadata_code = newVal?.code || null;
    },
  },
};
</script>

<style scoped>
</style>
