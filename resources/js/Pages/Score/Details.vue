<script setup lang="ts">
import { ref } from "vue";
import { router, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    ArrowLeftIcon,
    TrashIcon,
    ArrowUturnLeftIcon,
    XCircleIcon,
    ClipboardDocumentIcon,
    CheckIcon,
    TrophyIcon,
} from "@heroicons/vue/24/outline";

export interface Score {
    id: string;
    user_id: string;
    score: number;
    category: string;
    difficulty: string;
    created_at: string;
    deleted_at: string | null;
}

const props = defineProps<{
    score: Score;
}>();

const copiedField = ref<"id" | "user_id" | null>(null);

const copyToClipboard = async (
    value: string,
    field: "id" | "user_id",
): Promise<void> => {
    await navigator.clipboard.writeText(value);
    copiedField.value = field;
    setTimeout(() => {
        copiedField.value = null;
    }, 2000);
};

const formatDate = (dateString: string | null | undefined): string => {
    if (!dateString) return "—";
    return new Intl.DateTimeFormat("en-US", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(dateString));
};

const goBack = (): void => {
    router.get((window as any).route("score.index"));
};

const viewPlayer = (): void => {
    router.get((window as any).route("user.show", props.score.user_id));
};

const softDeleteScore = (): void => {
    if (confirm("Delete this score?")) {
        router.delete((window as any).route("score.destroy", props.score.id), {
            preserveScroll: true,
        });
    }
};

const restoreScore = (): void => {
    if (confirm("Restore this score?")) {
        router.patch(
            (window as any).route("score.restore", props.score.id),
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const forceDeleteScore = (): void => {
    if (confirm("Permanently delete this score?")) {
        router.delete(
            (window as any).route("score.forcedelete", props.score.id),
            {
                preserveScroll: true,
            },
        );
    }
};
</script>

<template>
    <Head :title="`Score - ${score.score} pts`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="goBack"
                        title="Back to Scores"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition hover:border-sky-300 hover:text-sky-700"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <div>
                        <h2
                            class="text-xl font-semibold leading-tight text-gray-800"
                        >
                            Score Details
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <template v-if="!score.deleted_at">
                        <button
                            type="button"
                            @click="softDeleteScore"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-rose-300 hover:text-rose-700"
                        >
                            <TrashIcon
                                class="h-4 w-4 text-rose-500"
                                aria-hidden="true"
                            />
                            <span>Move to Trash</span>
                        </button>
                    </template>

                    <template v-else>
                        <button
                            type="button"
                            @click="restoreScore"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700"
                        >
                            <ArrowUturnLeftIcon
                                class="h-4 w-4 text-blue-500"
                                aria-hidden="true"
                            />
                            <span>Restore</span>
                        </button>

                        <button
                            type="button"
                            @click="forceDeleteScore"
                            class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-2 text-sm font-medium text-rose-700 shadow-sm transition hover:bg-rose-100"
                        >
                            <XCircleIcon
                                class="h-4 w-4 text-rose-600"
                                aria-hidden="true"
                            />
                            <span>Permanently Delete</span>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm"
                >
                    <!-- Score Summary Header -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 px-6 py-5"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-indigo-700"
                            >
                                <TrophyIcon
                                    class="h-6 w-6"
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <h3
                                    class="font-mono text-lg font-bold text-gray-900"
                                >
                                    {{ score.score.toLocaleString() }} pts
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ score.category }} •
                                    {{ score.difficulty }}
                                </p>
                            </div>
                        </div>

                        <span
                            :class="
                                score.deleted_at
                                    ? 'bg-rose-50 text-rose-600 ring-rose-500/20'
                                    : 'bg-emerald-50 text-emerald-600 ring-emerald-500/20'
                            "
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset"
                        >
                            {{ score.deleted_at ? "Trashed" : "Active" }}
                        </span>
                    </div>

                    <!-- Score Information Grid -->
                    <dl class="divide-y divide-gray-100">
                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                Score UUID
                            </dt>
                            <dd
                                class="mt-1 flex items-center gap-2 font-mono text-sm text-gray-900 sm:col-span-2 sm:mt-0"
                            >
                                <span class="break-all">{{ score.id }}</span>
                                <button
                                    type="button"
                                    @click="copyToClipboard(score.id, 'id')"
                                    title="Copy Score UUID"
                                    class="rounded p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                >
                                    <CheckIcon
                                        v-if="copiedField === 'id'"
                                        class="h-4 w-4 text-emerald-500"
                                    />
                                    <ClipboardDocumentIcon
                                        v-else
                                        class="h-4 w-4"
                                    />
                                </button>
                            </dd>
                        </div>

                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                Player UUID
                            </dt>
                            <dd
                                class="mt-1 flex flex-wrap items-center gap-2 font-mono text-sm text-gray-900 sm:col-span-2 sm:mt-0"
                            >
                                <button
                                    type="button"
                                    @click="viewPlayer"
                                    class="break-all text-left text-indigo-600 hover:text-indigo-800 hover:underline"
                                >
                                    {{ score.user_id }}
                                </button>
                                <button
                                    type="button"
                                    @click="
                                        copyToClipboard(
                                            score.user_id,
                                            'user_id',
                                        )
                                    "
                                    title="Copy Player UUID"
                                    class="rounded p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                >
                                    <CheckIcon
                                        v-if="copiedField === 'user_id'"
                                        class="h-4 w-4 text-emerald-500"
                                    />
                                    <ClipboardDocumentIcon
                                        v-else
                                        class="h-4 w-4"
                                    />
                                </button>
                            </dd>
                        </div>

                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                Trivia Score
                            </dt>
                            <dd
                                class="mt-1 font-mono text-sm font-bold text-indigo-600 sm:col-span-2 sm:mt-0"
                            >
                                {{ score.score.toLocaleString() }} pts
                            </dd>
                        </div>

                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500"></dt>
                        </div>
                    </dl>
                </div>
            </div></div
    ></AuthenticatedLayout>
</template>
