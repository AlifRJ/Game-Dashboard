<script setup lang="ts">
import { computed, type Component } from "vue";
import {
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from "@heroicons/vue/24/outline";

interface Props {
    title: string;
    stat: string | number;
    description: string;
    icon: Component;
    accent?: "sky" | "emerald";
    trend?: string;
    trendType?: "positive" | "negative" | "neutral";
}

const props = withDefaults(defineProps<Props>(), {
    accent: "sky",
    trendType: "neutral",
});

const iconBadgeClasses = computed(() =>
    props.accent === "emerald"
        ? "bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-500/20"
        : "bg-sky-50 text-sky-600 ring-1 ring-inset ring-sky-500/20",
);

const trendClasses = computed(() => {
    switch (props.trendType) {
        case "positive":
            return "text-emerald-600";
        case "negative":
            return "text-rose-600";
        default:
            return "text-slate-500";
    }
});
</script>

<template>
    <div
        class="rounded-xl border border-slate-200/80 bg-white p-6 shadow-sm transition-shadow hover:shadow-md"
    >
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-slate-500">
                {{ title }}
            </h3>
            <div
                :class="[
                    'flex h-10 w-10 items-center justify-center rounded-lg',
                    iconBadgeClasses,
                ]"
            >
                <component :is="icon" class="h-5 w-5" aria-hidden="true" />
            </div>
        </div>

        <div class="mt-4">
            <p
                class="text-3xl font-bold tracking-tight text-slate-900 tabular-nums"
            >
                {{ stat }}
            </p>
        </div>

        <p class="mt-2 flex items-center gap-1.5 text-sm text-slate-500">
            <span
                v-if="trend"
                :class="[
                    'inline-flex items-center gap-1 font-medium',
                    trendClasses,
                ]"
            >
                <ArrowTrendingUpIcon
                    v-if="trendType === 'positive'"
                    class="h-4 w-4"
                    aria-hidden="true"
                />
                <ArrowTrendingDownIcon
                    v-else-if="trendType === 'negative'"
                    class="h-4 w-4"
                    aria-hidden="true"
                />
                {{ trend }}
            </span>
            <span>{{ description }}</span>
        </p>
    </div>
</template>
