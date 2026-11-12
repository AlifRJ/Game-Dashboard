<script setup lang="ts">
import { router, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable, {
    type PaginatedData,
    type TrashedFilter,
} from "@/Components/DataTable.vue";
import {
    TrashIcon,
    ArrowUturnLeftIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";

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

const softDeleteScore = (score: Score): void => {
    if (confirm("Delete this score?")) {
        router.delete((window as any).route("score.destroy", score.id), {
            preserveScroll: true,
        });
    }
};

const restoreScore = (score: Score): void => {
    if (confirm("restore this score?")) {
        router.patch((window as any).route("score.restore", score.id), {
            preserveScroll: true,
        });
    }
};

const forceDeleteScore = (score: Score): void => {
    if (confirm("Permanently delete this score?")) {
        router.delete((window as any).route("score.forcedelete", score.id), {
            preserveScroll: true,
        });
    }
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
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
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
                                <td class="px-6 py-4">
                                    <span
                                        :class="
                                            score.deleted_at
                                                ? 'text-rose-500'
                                                : 'text-emerald-500'
                                        "
                                        class="text-xs font-semibold"
                                    >
                                        {{
                                            score.deleted_at
                                                ? "Trashed"
                                                : "Active"
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="inline-flex items-center justify-end gap-2"
                                    >
                                        <button
                                            v-if="!score.deleted_at"
                                            type="button"
                                            @click="softDeleteScore(score)"
                                            title="Move to Trash"
                                            aria-label="Move to Trash"
                                            class="p-1 rounded hover:bg-gray-100 text-rose-500 hover:text-rose-600"
                                        >
                                            <TrashIcon class="w-5 h-5" />
                                        </button>

                                        <template v-else>
                                            <button
                                                type="button"
                                                @click="restoreScore(score)"
                                                title="Restore Score"
                                                aria-label="Restore Score"
                                                class="p-1 rounded hover:bg-gray-100 text-blue-500 hover:text-blue-600"
                                            >
                                                <ArrowUturnLeftIcon
                                                    class="w-5 h-5"
                                                />
                                            </button>

                                            <button
                                                type="button"
                                                @click="forceDeleteScore(score)"
                                                title="Permanently Delete"
                                                aria-label="Permanently Delete"
                                                class="p-1 rounded hover:bg-gray-100 text-rose-500 hover:text-rose-600"
                                            >
                                                <XCircleIcon class="w-5 h-5" />
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
