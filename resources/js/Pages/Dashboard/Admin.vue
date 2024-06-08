<template>
    <Head title="Administracion" />
    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Administracion general
            </h2>
        </template>
        <template #body>

        </template>
    </BreezeAuthenticatedLayout>
</template>

<script>
    import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        name: "Admin",
        components: {
            BreezeAuthenticatedLayout,
            Head,
        },
    }
</script>