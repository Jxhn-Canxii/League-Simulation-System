<template>
    <div class="grid grid-cols-4 gap-6">
        <div
            class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs"
        >
            <div
                class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500"
            >
                <i class="fa fa-users"></i>
            </div>
            <div>
                <p
                    class="mb-2 text-sm font-medium text-gray-600"
                >
                    Total Players
                </p>
                <p
                    class="text-lg font-semibold text-black"
                >
                    1,230
                </p>
            </div>
        </div>
        <div
            class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs"
        >
            <div
                class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500"
            >
                <i class="fa fa-users"></i>
            </div>
            <div>
                <p
                    class="mb-2 text-sm font-medium text-gray-600"
                >
                    Total Rookies
                </p>
                <p
                    class="text-lg font-semibold text-black"
                >
                    1,230
                </p>
            </div>
        </div>
        <div
            class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs"
        >
            <div
                class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500"
            >
                <i class="fa fa-users"></i>
            </div>
            <div>
                <p
                    class="mb-2 text-sm font-medium text-gray-600"
                >
                    Total Seasons
                </p>
                <p
                    class="text-lg font-semibold text-black"
                >
                    1,230
                </p>
            </div>
        </div>
        <div
            class="flex items-center border shadow-xs p-4 bg-white rounded-lg shadow-xs"
        >
            <div
                class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full dark:text-orange-100 dark:bg-orange-500"
            >
                <i class="fa fa-users"></i>
            </div>
            <div>
                <p
                    class="mb-2 text-sm font-medium text-gray-600"
                >
                    Total Guest
                </p>
                <p
                    class="text-lg font-semibold text-black"
                >
                    1,230
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios"; // Ensure axios is imported
import Swal from "sweetalert2";
import Modal from "@/Components/Modal.vue";
import Paginator from "@/Components/Paginator.vue";

onMounted(() => {});
</script>
