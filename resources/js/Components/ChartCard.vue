<script setup lang="ts">
import { computed, type Component } from "vue";

interface Props {
    title: string;
    subtitle: string;
    icon: Component;
    accent?: "sky" | "emerald";
    isEmpty?: boolean;
    emptyTitle?: string;
    emptyDescription?: string;
    heightClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    accent: "sky",
    isEmpty: false,
    emptyTitle: "No data available yet",
    emptyDescription:
        "Metrics will appear once players complete trivia rounds.",
    heightClass: "min-h-[300px] flex-1",
});

const iconBadgeClasses = computed(() =>
    props.accent === "emerald"
        ? "bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-500/20"
        : "bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-500/20",
);
</script>

<template>
    <div
        class="flex flex-col rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm"
    >
        <!-- Card Header -->
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-slate-900">
                    {{ title }}
                </h3>
                <p class="text-sm text-slate-500">
                    {{ subtitle }}
                </p>
            </div>
            <div
                :class="[
                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg',
                    iconBadgeClasses,
                ]"
            >
                <component :is="icon" class="h-5 w-5" aria-hidden="true" />
            </div>
        </div>

        <!-- Chart / Content Slot -->
        <div v-if="!isEmpty" :class="['relative w-full', heightClass]">
            <slot />
        </div>

        <!-- Built-in Empty State -->
        <div
            v-else
            :class="[
                'flex w-full flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 bg-slate-50/50 p-6 text-center',
                heightClass,
            ]"
        >
            <component
                :is="icon"
                class="h-8 w-8 text-slate-300"
                aria-hidden="true"
            />
            <p class="mt-2 text-sm font-medium text-slate-600">
                {{ emptyTitle }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
                {{ emptyDescription }}
            </p>
        </div>
    </div>
</template>
