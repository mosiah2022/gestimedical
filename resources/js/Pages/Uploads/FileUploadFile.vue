<template>
    <div class="field col">
        <form  enctype="multipart/form-data">
            <label>Seleccione una formula</label>
            <input type="file" @change="onFileChange" class="w-full" />        
        </form>
    </div>   
</template>

<script>
import axios from 'axios';
import Swal from "sweetalert2";
    export default{
        name: "FileUploadFile",
        data() {
            return {
                filename: null,
                file: null,
                success: null
            }
        },
        props: {
            patient_id: Number
        },
        methods: {
            onFileChange(e) {
                e.preventDefault();
                this.filename = "Selected File: " + e.target.files[0].name;
                this.file = e.target.files[0];
                let currentObj = this;
                const config = {
                    headers: {
                        'content-type': 'multipart/form-data',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                }
                let formData = new FormData();
                formData.append('file', this.file);
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
            },
        }
    }
</script>