<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable, {
    type PaginatedData,
    type TrashedFilter,
} from "@/Components/DataTable.vue";

export interface Score {
    id: string;
    user_id: string;
    score: string;
    category: string;
    difficulty: string;
    created_at: string;
    deleted_at: string | null;
}

export interface ScoreFilters {
    search?: string;
    trashed?: TrashedFilter;
}

const props = defineProps<{
    scores: PaginatedData<Score>;
    filters: ScoreFilters;
}>();

// Format long UUID for clean table layout (e.g., "e2a4...8f91")
const formatUuid = (uuid: string): string => {
    if (!uuid) return "";
    return `${uuid.substring(0, 8)}...${uuid.substring(uuid.length - 4)}`;
};
</script>
<template>
    <Head title="Score" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Score
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <DataTable
                        :data="scores"
                        route-name="score.index"
                        :search="filters.search"
                        :trashed="filters.trashed"
                        search-placeholder="Search player scores..."
                    >
                        <template #head>
                            <th class="px-6 py-4">Rank</th>
                            <th class="px-6 py-4">Player</th>
                            <th class="px-6 py-4">Trivia Score</th>
                            <th class="px-6 py-4">Category</th>
                            <th class="px-6 py-4">Difficulty</th>
                            <th class="px-6 py-4">Date Played</th>
                        </template>

                        <template #body>
                            <tr
                                v-for="(score, index) in scores.data"
                                :key="score.id"
                            >
                                <td class="px-6 py-4 font-bold">
                                    #{{ index + 1 }}
                                </td>
                                <td
                                    class="px-6 py-4 font-mono text-xs text-gray-500"
                                    :title="score.user_id"
                                >
                                    {{ formatUuid(score.user_id) }}
                                </td>
                                <td
                                    class="px-6 py-4 font-mono text-indigo-300 font-bold"
                                >
                                    {{ score.score }} pts
                                </td>
                                <td
                                    class="px-6 py-4 font-mono text-indigo-500 font-bold"
                                >
                                    {{ score.category }}
                                </td>
                                <td
                                    class="px-6 py-4 font-mono text-indigo-700 font-bold"
                                >
                                    {{ score.difficulty }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    {{ score.created_at }}
                                </td>
                            </tr>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
