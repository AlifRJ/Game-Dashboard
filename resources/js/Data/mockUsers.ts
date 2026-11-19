import type {
    PaginatedData,
    PaginationLink,
    TrashedFilter,
} from "@/Components/DataTable.vue";
import type { User } from "@/Pages/User/Index.vue";

export const initialMockUsers: User[] = [
    {
        id: "e2a41c8b-3d90-4f1a-8b2e-9c4d7a1e8f91",
        name: "Budi Santoso",
        username: "budisantoso",
        email: "budi.santoso@example.com",
        created_at: "2026-01-12T08:30:00.000000Z",
        updated_at: "2026-02-01T14:15:00.000000Z",
        deleted_at: null,
    },
    {
        id: "7f9c2b14-6e3a-48d2-a1f0-5b8c3e9d4a12",
        name: "Siti Rahmawati",
        username: "sitirahma",
        email: "siti.rahmawati@example.com",
        created_at: "2026-01-15T10:12:00.000000Z",
        updated_at: "2026-01-15T10:12:00.000000Z",
        deleted_at: null,
    },
    {
        id: "3a8d9e21-1b4c-47f9-9e2a-6d1c8b7f3e45",
        name: "Andi Wijaya",
        username: "andiwijaya",
        email: "andi.wijaya@example.com",
        created_at: "2026-02-03T09:45:00.000000Z",
        updated_at: "2026-03-10T16:20:00.000000Z",
        deleted_at: "2026-03-10T16:20:00.000000Z",
    },
    {
        id: "9c1e5f72-8a2b-4c6d-b3e1-2f4a9d8c7b63",
        name: "Dewi Lestari",
        username: "dewilestari",
        email: "dewi.lestari@example.com",
        created_at: "2026-02-18T11:05:00.000000Z",
        updated_at: "2026-02-20T08:50:00.000000Z",
        deleted_at: null,
    },
    {
        id: "5d4b8a93-2c7e-41a8-8f6d-1e3b5c7a9d04",
        name: "Rizky Pratama",
        username: "rizkypratama",
        email: "rizky.pratama@example.com",
        created_at: "2026-03-01T13:40:00.000000Z",
        updated_at: "2026-04-05T09:10:00.000000Z",
        deleted_at: "2026-04-05T09:10:00.000000Z",
    },
    {
        id: "1b6e4d29-9f0a-4c3b-8e7d-4a2f6c8b1e59",
        name: "Putri Maharani",
        username: "putrimaharani",
        email: "putri.maharani@example.com",
        created_at: "2026-03-14T15:22:00.000000Z",
        updated_at: "2026-03-14T15:22:00.000000Z",
        deleted_at: null,
    },
    {
        id: "8f2a7c61-4d8e-49b0-a5c3-7e1d9b4f2a80",
        name: "Hendra Gunawan",
        username: "hendragunawan",
        email: "hendra.gunawan@example.com",
        created_at: "2026-04-02T07:55:00.000000Z",
        updated_at: "2026-04-02T07:55:00.000000Z",
        deleted_at: null,
    },
];

/**
 * Simulates Laravel's ->when($search)->withTrashed()->paginate($perPage)
 * Matches DataTable.vue's exact PaginatedData<User> interface.
 */
export function buildMockPaginatedUsers(
    allUsers: User[],
    options: {
        search?: string;
        trashed?: TrashedFilter;
        page?: number;
        perPage?: number;
    } = {},
): PaginatedData<User> {
    const search = (options.search ?? "").trim().toLowerCase();
    const trashed = options.trashed ?? "";
    const perPage = options.perPage ?? 5;

    // 1. Apply TrashedFilter ("", "with", "only")
    let filtered = allUsers.filter((user) => {
        if (trashed === "only") return user.deleted_at !== null;
        if (trashed === "with") return true;
        return user.deleted_at === null; // default "" -> Active Records
    });

    // 2. Apply Search (UUID, name, username, email)
    if (search) {
        filtered = filtered.filter(
            (user) =>
                user.id.toLowerCase().includes(search) ||
                user.name.toLowerCase().includes(search) ||
                user.username.toLowerCase().includes(search) ||
                user.email.toLowerCase().includes(search),
        );
    }

    // 3. Calculate Pagination
    const total = filtered.length;
    const last_page = Math.max(1, Math.ceil(total / perPage));
    const current_page = Math.min(Math.max(1, options.page ?? 1), last_page);
    const startIndex = (current_page - 1) * perPage;
    const data = filtered.slice(startIndex, startIndex + perPage);

    const from = total === 0 ? null : startIndex + 1;
    const to = total === 0 ? null : startIndex + data.length;

    // 4. Build PaginationLink[] (DataTable renders footer when links.length > 3)
    const links: PaginationLink[] = [
        {
            url: current_page > 1 ? `?page=${current_page - 1}` : null,
            label: "« Previous",
            active: false,
        },
        ...Array.from({ length: last_page }, (_, i) => {
            const pageNum = i + 1;
            return {
                url: `?page=${pageNum}`,
                label: String(pageNum),
                active: pageNum === current_page,
            };
        }),
        {
            url: current_page < last_page ? `?page=${current_page + 1}` : null,
            label: "Next »",
            active: false,
        },
    ];

    return {
        data,
        links,
        from,
        to,
        total,
        current_page,
        last_page,
    };
}
