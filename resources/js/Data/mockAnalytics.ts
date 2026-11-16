import type { AnalyticsData } from "../Composables/useAnalyticsCharts";

export const mockAnalyticsData: AnalyticsData = {
    kpis: {
        total_games: 1428,
        total_players: 384,
        avg_score: 134,
    },
    categories: [
        { category: "science", games_played: 412, avg_score: 142 },
        { category: "history", games_played: 328, avg_score: 129 },
        { category: "geography", games_played: 295, avg_score: 138 },
        { category: "sports", games_played: 214, avg_score: 121 },
        { category: "music", games_played: 179, avg_score: 135 },
    ],
    difficulties: [
        { difficulty: "easy", total_games: 540, avg_score: 162 },
        { difficulty: "medium", total_games: 618, avg_score: 128 },
        { difficulty: "hard", total_games: 270, avg_score: 94 },
    ],
    daily_trends: [
        { date: "2026-11-10", games_played: 165, avg_score: 128 },
        { date: "2026-11-11", games_played: 192, avg_score: 131 },
        { date: "2026-11-12", games_played: 184, avg_score: 135 },
        { date: "2026-11-13", games_played: 221, avg_score: 130 },
        { date: "2026-11-14", games_played: 248, avg_score: 139 },
        { date: "2026-11-15", games_played: 210, avg_score: 136 },
        { date: "2026-11-16", games_played: 208, avg_score: 141 },
    ],
    score_distribution: [
        { range: "0–40", count: 64 },
        { range: "41–80", count: 198 },
        { range: "81–120", count: 462 },
        { range: "121–160", count: 489 },
        { range: "161–200", count: 215 },
    ],
    top_players: [
        { id: 1, name: "Alya Putri", games_played: 48, total_score: 7920 },
        { id: 2, name: "Rizky Pratama", games_played: 42, total_score: 6840 },
        { id: 3, name: "Nadia Kusuma", games_played: 39, total_score: 6210 },
        { id: 4, name: "Dimas Saputra", games_played: 35, total_score: 5490 },
        { id: 5, name: "Tariq Rahman", games_played: 31, total_score: 4980 },
    ],
};
