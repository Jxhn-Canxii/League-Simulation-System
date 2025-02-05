<template>
    <Head title="Analytics" />

    <AuthenticatedLayout>
        <template #header> Analytics </template>

        <div class="overflow-hidden shadow-sm sm:rounded-lg min-h-screen p-3 space-y-3">
            <Suspense>
                <template #default>
                    <TopStatistics />
                </template>
                <template #fallback>
                    <div class="text-center py-10 text-gray-500">Loading Top Statistics...</div>
                </template>
            </Suspense>
            <Suspense>
                <template #default>
                    <DraftSummary />
                </template>
                <template #fallback>
                    <div class="text-center py-10 text-gray-500">Loading Draft Summary...</div>
                </template>
            </Suspense>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Suspense>
                    <template #default>
                        <ChampionshipShares />
                    </template>
                    <template #fallback>
                        <div class="text-center py-10 text-gray-500">Loading Championship Shares...</div>
                    </template>
                </Suspense>

                <!-- <Suspense>
                    <template #default>
                        <ScoringShares />
                    </template>
                    <template #fallback>
                        <div class="text-center py-10 text-gray-500">Loading Scoring Shares...</div>
                    </template>
                </Suspense> -->
                <Suspense>
                    <template #default>
                        <WinShares />
                    </template>
                    <template #fallback>
                        <div class="text-center py-10 text-gray-500">Loading Win Shares...</div>
                    </template>
                </Suspense>
            </div>
            <Suspense>
                <template #default>
                    <AllTimeGameRecords />
                </template>
                <template #fallback>
                    <div class="text-center py-10 text-gray-500">Loading All Time Game Records...</div>
                </template>
            </Suspense>

            <Suspense>
                <template #default>
                    <SeasonTimeLine />
                </template>
                <template #fallback>
                    <div class="text-center py-10 text-gray-500">Loading Season Timeline...</div>
                </template>
            </Suspense>

            <!-- <Suspense>
                <template #default>
                    <Top20Players />
                </template>
                <template #fallback>
                    <div class="text-center py-10 text-gray-500">Loading Season Leaders...</div>
                </template>
            </Suspense> -->
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
import ChampionshipShares from "./Module/ChampionshipShares.vue";
import ScoringShares from "./Module/ScoringShares.vue";
import SeasonLeaders from "./Module/SeasonLeaders.vue";
import AllTimeGameRecords from "./Module/AllTimeGameRecords.vue";
import DraftSummary from "./Module/DraftSummary.vue";

onMounted(() => {});
</script>
