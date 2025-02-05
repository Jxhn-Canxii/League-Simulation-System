<template>
    <Head title="Playoff Records" />

    <AuthenticatedLayout>
        <template #header> Playoff Records </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3 space-y-3">
            <Suspense>
                <template #default>
                    <PlayerPlayoffAppearance />
                </template>
                <template #fallback>
                    <div class="text-center py-10 text-gray-500">Loading Playoff Apearance...</div>
                </template>
            </Suspense>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios"; // Ensure axios is imported
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";
import PlayerPlayoffAppearance from "./Module/PlayerPlayoffAppearance.vue";


onMounted(() => {});
</script>
