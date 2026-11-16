import { computed, toValue, type MaybeRefOrGetter } from "vue";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement,
    Filler,
    type ChartOptions,
    type TooltipItem,
} from "chart.js";

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement,
    Filler,
);

export interface CategoryItem {
    category: string;
    games_played: number;
    avg_score: number;
}

export interface DifficultyItem {
    difficulty: string;
    total_games: number;
    avg_score: number;
}

export interface DailyTrendItem {
    date: string;
    games_played: number;
    avg_score: number;
}

export interface ScoreDistributionItem {
    range?: string;
    bucket?: string;
    label?: string;
    count?: number;
    players?: number;
    games?: number;
}

export interface TopPlayerItem {
    id?: number | string;
    name?: string;
    username?: string;
    total_score?: number;
    games_played?: number;
    win_streak?: number;
}

export interface AnalyticsData {
    kpis: {
        total_games: number;
        total_players: number;
        avg_score: number;
    };
    categories: CategoryItem[];
    difficulties: DifficultyItem[];
    daily_trends: DailyTrendItem[];
    score_distribution?: ScoreDistributionItem[];
    top_players?: TopPlayerItem[];
}

const capitalize = (str: string) =>
    str ? str.charAt(0).toUpperCase() + str.slice(1) : "";

const difficultyColorMap: Record<string, string> = {
    easy: "#10b981", // emerald-500
    medium: "#0ea5e9", // sky-500
    hard: "#f59e0b", // amber-500
};

function formatAvgScoreTooltip(context: TooltipItem<any>): string {
    const dataset = context.dataset as { avgScores?: number[] };
    const avgScore = dataset.avgScores?.[context.dataIndex];

    return avgScore !== undefined ? `Avg Score: ${avgScore}` : "";
}

export function useAnalyticsCharts(
    analyticsData: MaybeRefOrGetter<AnalyticsData | null | undefined>,
) {
    // 1. Popular Categories (Horizontal Bar)
    const categoriesChartData = computed(() => {
        const items = toValue(analyticsData)?.categories ?? [];

        return {
            labels: items.map((item) => capitalize(item.category)),
            datasets: [
                {
                    label: "Games Played",
                    data: items.map((item) => item.games_played),
                    avgScores: items.map((item) => item.avg_score),
                    backgroundColor: "#0ea5e9", // sky-500
                    borderRadius: 6,
                    barThickness: 20,
                },
            ],
        };
    });

    // 2. Question Difficulty Breakdown (Doughnut)
    const difficultiesChartData = computed(() => {
        const items = toValue(analyticsData)?.difficulties ?? [];

        return {
            labels: items.map((item) => capitalize(item.difficulty)),
            datasets: [
                {
                    label: "Total Games",
                    data: items.map((item) => item.total_games),
                    avgScores: items.map((item) => item.avg_score),
                    backgroundColor: items.map(
                        (item) =>
                            difficultyColorMap[item.difficulty.toLowerCase()] ??
                            "#0ea5e9",
                    ),
                    borderWidth: 0,
                    hoverOffset: 6,
                },
            ],
        };
    });

    // 3. Games Played Over Time (Area Line)
    const trendsChartData = computed(() => {
        const items = toValue(analyticsData)?.daily_trends ?? [];

        return {
            labels: items.map((item) => item.date),
            datasets: [
                {
                    label: "Games Played",
                    data: items.map((item) => item.games_played),
                    avgScores: items.map((item) => item.avg_score),
                    borderColor: "#10b981", // emerald-500
                    backgroundColor: "rgba(16, 185, 129, 0.12)",
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: "#10b981",
                },
            ],
        };
    });

    // 4. Player Score Distribution (Vertical Histogram Bar)
    const scoreDistributionChartData = computed(() => {
        const items = toValue(analyticsData)?.score_distribution ?? [];

        return {
            labels: items.map(
                (item) => item.range ?? item.bucket ?? item.label ?? "N/A",
            ),
            datasets: [
                {
                    label: "Sessions",
                    data: items.map(
                        (item) => item.count ?? item.games ?? item.players ?? 0,
                    ),
                    backgroundColor: "#0ea5e9", // sky-500
                    hoverBackgroundColor: "#0284c7", // sky-600
                    borderRadius: 6,
                    maxBarThickness: 48,
                },
            ],
        };
    });

    const horizontalBarOptions: ChartOptions<"bar"> = {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: { afterLabel: formatAvgScoreTooltip },
            },
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: { color: "#64748b", precision: 0 },
                grid: { color: "rgba(148, 163, 184, 0.15)" },
            },
            y: {
                grid: { display: false },
                ticks: { color: "#64748b" },
            },
        },
    };

    const verticalBarOptions: ChartOptions<"bar"> = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: "#64748b" },
            },
            y: {
                beginAtZero: true,
                ticks: { color: "#64748b", precision: 0 },
                grid: { color: "rgba(148, 163, 184, 0.15)" },
            },
        },
    };

    const doughnutOptions: ChartOptions<"doughnut"> = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "68%",
        plugins: {
            legend: {
                position: "bottom",
                labels: { padding: 20, usePointStyle: true, color: "#64748b" },
            },
            tooltip: {
                callbacks: { afterLabel: formatAvgScoreTooltip },
            },
        },
    };

    const areaLineOptions: ChartOptions<"line"> = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: "index", intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: { afterLabel: formatAvgScoreTooltip },
            },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: "#64748b" },
            },
            y: {
                beginAtZero: true,
                ticks: { color: "#64748b", precision: 0 },
                grid: { color: "rgba(148, 163, 184, 0.15)" },
            },
        },
    };

    const hasCategoriesData = computed(
        () => (toValue(analyticsData)?.categories?.length ?? 0) > 0,
    );

    const hasDifficultiesData = computed(
        () => (toValue(analyticsData)?.difficulties?.length ?? 0) > 0,
    );

    const hasTrendsData = computed(
        () => (toValue(analyticsData)?.daily_trends?.length ?? 0) > 0,
    );

    const hasScoreDistributionData = computed(
        () => (toValue(analyticsData)?.score_distribution?.length ?? 0) > 0,
    );

    return {
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
    };
}
