<script setup lang="ts" generic="T extends Record<string, any>">
import { ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<DataItems> {
    data: DataItems[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
    current_page: number;
    last_page: number;
}

export type TrashedFilter = "with" | "only" | "";

const props = withDefaults(
    defineProps<{
        /** Paginated Inertia response structure from Laravel */
        data: PaginatedData<T>;
        /** Named route used for re-fetching search/filter parameters */
        routeName: string;
        /** Search input string state */
        search?: string;
        /** Soft delete filter state */
        trashed?: TrashedFilter;
        /** Input placeholder for search field */
        searchPlaceholder?: string;
    }>(),
    {
        search: "",
        trashed: "",
        searchPlaceholder: "Search records...",
    },
);

const searchFilter = ref<string>(props.search);
const trashedFilter = ref<TrashedFilter>(props.trashed);
let searchDebounceTimeout: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (): void => {
    const targetRoute = (window as any).route(props.routeName);

    router.get(
        targetRoute,
        {
            search: searchFilter.value,
            trashed: trashedFilter.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

watch(searchFilter, () => {
    if (searchDebounceTimeout) clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch(trashedFilter, () => {
    applyFilters();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Controls Bar -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="relative max-w-sm flex-1">
                <div
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
                >
                    <svg
                        class="h-4 w-4 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </div>
                <input
                    v-model="searchFilter"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                />
            </div>

            <div class="flex items-center space-x-3">
                <select
                    v-model="trashedFilter"
                    class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                >
                    <option value="">Active Records</option>
                    <option value="with">With Trashed</option>
                    <option value="only">Only Trashed</option>
                </select>

                <slot name="header-actions" />
            </div>
        </div>

        <!-- Main Table -->
        <div
            class="w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead
                        class="border-b border-gray-200 bg-gray-50/50 text-xs uppercase tracking-wider text-gray-500"
                    >
                        <tr>
                            <slot name="head" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <slot name="body" />
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer -->
            <div
                v-if="data.links && data.links.length > 3"
                class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 px-6 py-4 sm:flex-row"
            >
                <p class="text-xs text-gray-500">
                    Showing
                    <span class="font-medium text-gray-900">{{
                        data.from ?? 0
                    }}</span>
                    to
                    <span class="font-medium text-gray-900">{{
                        data.to ?? 0
                    }}</span>
                    of
                    <span class="font-medium text-gray-900">{{
                        data.total
                    }}</span>
                    results
                </p>

                <div class="flex flex-wrap gap-1">
                    <Component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, index) in data.links"
                        :key="index"
                        :href="link.url ?? undefined"
                        v-html="link.label"
                        class="rounded-lg px-3 py-1.5 text-xs transition"
                        :class="{
                            'bg-indigo-600 font-semibold text-white':
                                link.active,
                            'text-gray-600 hover:bg-gray-100':
                                link.url && !link.active,
                            'cursor-not-allowed opacity-40': !link.url,
                        }"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>
    </div>
</template>
