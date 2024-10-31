<template>
    <Head title="Analytics" />

    <AuthenticatedLayout>
        <template #header> Analytics </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3 space-y-3">
          <TopStatistics />
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <WinShares />
            <SeasonTimeLine />
          </div>
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
import Paginator from "@/Components/Paginator.vue";
import TopStatistics from "./Module/TopStatistics.vue";
import WinShares from "./Module/WinShares.vue";
import SeasonTimeLine from "./Module/SeasonTimeLine.vue";

onMounted(() => {});
</script>
