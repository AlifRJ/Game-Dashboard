<script setup lang="ts">
import { computed, ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { Bar, Doughnut, Line } from "vue-chartjs";
import {
    TrophyIcon,
    UsersIcon,
    AcademicCapIcon,
    ArrowPathIcon,
    TagIcon,
    PuzzlePieceIcon,
    PresentationChartLineIcon,
    ChartBarSquareIcon,
    FireIcon,
    ExclamationTriangleIcon,
    SparklesIcon,
} from "@heroicons/vue/24/outline";
import KpiCard from "@/Components/KpiCard.vue";
import ChartCard from "@/Components/ChartCard.vue";
import {
    useAnalyticsCharts,
    type AnalyticsData,
} from "../Composables/useAnalyticsCharts";
import { mockAnalyticsData } from "@/Data/mockAnalytics";

const props = defineProps<{
    analytics?: { data: AnalyticsData } | null;
    isApiConfigured?: boolean;
}>();

const isDemoMode = computed(() => !props.isApiConfigured);
const isApiOffline = computed(
    () => Boolean(props.isApiConfigured) && !props.analytics?.data,
);

const resolvedAnalytics = computed<AnalyticsData>(
    () => props.analytics?.data ?? mockAnalyticsData,
);

const {
    categoriesChartData,
    difficultiesChartData,
    trendsChartData,
    scoreDistributionChartData,
    hasCategoriesData,
    hasDifficultiesData,
    hasTrendsData,
    hasScoreDistributionData,
    horizontalBarOptions,
    verticalBarOptions,
    doughnutOptions,
    areaLineOptions,
} = useAnalyticsCharts(resolvedAnalytics);

const topPlayers = computed(() => resolvedAnalytics.value.top_players ?? []);

const isRefreshing = ref(false);

function refreshAnalytics() {
    router.reload({
        only: ["analytics"],
        data: { refresh: 1 },
        onStart: () => {
            isRefreshing.value = true;
        },
        onFinish: () => {
            isRefreshing.value = false;
            window.history.replaceState({}, "", window.location.pathname);
        },
    });
}

function rankBadgeClasses(index: number): string {
    if (index === 0)
        return "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-500/30";
    if (index === 1)
        return "bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-500/30";
    if (index === 2)
        return "bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-300";
    return "text-slate-500";
}
</script>

<template>
    <Head title="Home" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-slate-900">
                    Home
                </h2>

                <button
                    type="button"
                    :disabled="isRefreshing"
                    @click="refreshAnalytics"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-sky-300 hover:text-sky-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <ArrowPathIcon
                        :class="[
                            'h-4 w-4 text-sky-600',
                            { 'animate-spin': isRefreshing },
                        ]"
                        aria-hidden="true"
                    />
                    <span>{{
                        isRefreshing ? "Refreshing..." : "Refresh Data"
                    }}</span>
                </button>
            </div>
        </template>

        <div class="bg-slate-50 py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div
                    v-if="isDemoMode"
                    class="flex flex-col justify-between gap-4 rounded-xl border border-sky-200/80 bg-sky-50/70 p-4 shadow-sm sm:flex-row sm:items-center"
                >
                    <div class="flex items-start gap-3 sm:items-center">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600 ring-1 ring-inset ring-sky-500/20"
                        >
                            <SparklesIcon class="h-5 w-5" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">
                                Previewing Sample Analytics Data
                            </h3>
                            <p class="text-sm text-slate-600">
                                Live game API credentials are not connected.
                                Showing demo metrics so you can explore the
                                charts and tooltips.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :disabled="isRefreshing"
                        @click="refreshAnalytics"
                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg border border-sky-200 bg-white px-3.5 py-2 text-sm font-medium text-sky-700 shadow-sm transition hover:bg-sky-50 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <ArrowPathIcon
                            :class="[
                                'h-4 w-4 text-sky-600',
                                { 'animate-spin': isRefreshing },
                            ]"
                            aria-hidden="true"
                        />
                        <span>{{
                            isRefreshing
                                ? "Checking API..."
                                : "Connect Live API"
                        }}</span>
                    </button>
                </div>
                <div
                    v-else-if="isApiOffline"
                    role="alert"
                    class="flex flex-col justify-between gap-4 rounded-xl border border-amber-200/90 bg-amber-50/80 p-4 shadow-sm sm:flex-row sm:items-center"
                >
                    <div class="flex items-start gap-3 sm:items-center">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 ring-1 ring-inset ring-amber-500/20"
                        >
                            <ExclamationTriangleIcon
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-amber-950">
                                Live game analytics unavailable
                            </h3>
                            <p class="text-sm text-amber-800">
                                Unable to reach the game analytics service right
                                now. Displaying placeholder values until
                                connection is restored.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        :disabled="isRefreshing"
                        @click="refreshAnalytics"
                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg border border-amber-300 bg-white px-3.5 py-2 text-sm font-medium text-amber-900 shadow-sm transition hover:bg-amber-100/60 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <ArrowPathIcon
                            :class="[
                                'h-4 w-4 text-amber-600',
                                { 'animate-spin': isRefreshing },
                            ]"
                            aria-hidden="true"
                        />
                        <span>{{
                            isRefreshing ? "Retrying..." : "Retry Now"
                        }}</span>
                    </button>
                </div>

                <!-- 1. Top Row: 3 KPI Cards -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <KpiCard
                        title="Total Games Played"
                        :stat="resolvedAnalytics.kpis.total_games ?? 0"
                        description="Total trivia sessions started/completed"
                        :icon="TrophyIcon"
                        accent="emerald"
                    />
                    <KpiCard
                        title="Active Players"
                        :stat="resolvedAnalytics.kpis.total_players ?? 0"
                        description="Unique players active"
                        :icon="UsersIcon"
                        accent="sky"
                    />
                    <KpiCard
                        title="Average Score"
                        :stat="resolvedAnalytics.kpis.avg_score ?? 0"
                        description="Platform-wide average score per round"
                        :icon="AcademicCapIcon"
                        accent="emerald"
                    />
                </div>

                <!-- 2. Middle Row: 2 Side-by-Side Charts -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <ChartCard
                        title="Popular Categories"
                        subtitle="Which topics get played most"
                        :icon="TagIcon"
                        accent="sky"
                        :is-empty="!hasCategoriesData"
                        empty-title="No category activity yet"
                        empty-description="Topic play counts will appear once rounds are completed."
                    >
                        <Bar
                            :data="categoriesChartData"
                            :options="horizontalBarOptions"
                        />
                    </ChartCard>

                    <ChartCard
                        title="Question Difficulty Breakdown"
                        subtitle="Easy vs. Medium vs. Hard accuracy"
                        :icon="PuzzlePieceIcon"
                        accent="emerald"
                        :is-empty="!hasDifficultiesData"
                        empty-title="No difficulty data yet"
                        empty-description="Breakdown by easy, medium, and hard will show up here."
                    >
                        <Doughnut
                            :data="difficultiesChartData"
                            :options="doughnutOptions"
                        />
                    </ChartCard>
                </div>

                <!-- 3. Third Row: 1 Full-Width Area Chart -->
                <ChartCard
                    title="Games Played Over Time"
                    subtitle="Daily quiz completions and user growth trends"
                    :icon="PresentationChartLineIcon"
                    accent="emerald"
                    height-class="h-[350px]"
                    :is-empty="!hasTrendsData"
                    empty-title="No daily trend history yet"
                    empty-description="Daily session trends will populate as players complete games."
                >
                    <Line :data="trendsChartData" :options="areaLineOptions" />
                </ChartCard>

                <!-- 4. Bottom Row: Score Distribution (2/3) + Top Active Players Leaderboard (1/3) -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <ChartCard
                        class="lg:col-span-2"
                        title="Player Score Distribution"
                        subtitle="Score spread across completed trivia rounds"
                        :icon="ChartBarSquareIcon"
                        accent="sky"
                        :is-empty="!hasScoreDistributionData"
                        empty-title="No score distribution data yet"
                        empty-description="Score brackets will appear once enough rounds are recorded."
                    >
                        <Bar
                            :data="scoreDistributionChartData"
                            :options="verticalBarOptions"
                        />
                    </ChartCard>

                    <ChartCard
                        title="Top Active Players"
                        subtitle="Highest cumulative scores"
                        :icon="FireIcon"
                        accent="emerald"
                        :is-empty="topPlayers.length === 0"
                        empty-title="No player rankings yet"
                        empty-description="Scores will appear once players complete rounds."
                    >
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full divide-y divide-slate-200/80 text-left text-sm"
                            >
                                <thead>
                                    <tr
                                        class="text-xs font-medium uppercase tracking-wider text-slate-400"
                                    >
                                        <th class="pb-3 pr-2">Rank</th>
                                        <th class="pb-3 px-2">Player</th>
                                        <th class="pb-3 pl-2 text-right">
                                            Score
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="(player, idx) in topPlayers"
                                        :key="player.id ?? idx"
                                        class="transition-colors hover:bg-slate-50/80"
                                    >
                                        <td class="whitespace-nowrap py-3 pr-2">
                                            <span
                                                :class="[
                                                    'inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold tabular-nums',
                                                    rankBadgeClasses(idx),
                                                ]"
                                            >
                                                {{ idx + 1 }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-2">
                                            <p
                                                class="font-medium text-slate-900"
                                            >
                                                {{
                                                    player.name ??
                                                    player.username ??
                                                    "Anonymous"
                                                }}
                                            </p>
                                            <p
                                                v-if="
                                                    player.games_played !==
                                                    undefined
                                                "
                                                class="text-xs text-slate-500"
                                            >
                                                {{ player.games_played }}
                                                {{
                                                    player.games_played === 1
                                                        ? "game"
                                                        : "games"
                                                }}
                                            </p>
                                        </td>
                                        <td
                                            class="whitespace-nowrap py-3 pl-2 text-right font-semibold text-emerald-600 tabular-nums"
                                        >
                                            {{
                                                (
                                                    player.total_score ?? 0
                                                ).toLocaleString()
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </ChartCard>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
