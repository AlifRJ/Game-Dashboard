<script setup lang="ts">
import { router, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable, {
    type PaginatedData,
    type TrashedFilter,
} from "@/Components/DataTable.vue";

export interface User {
    id: string; // Changed from number to string for UUID
    name: string;
    username: string;
    email: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface UserFilters {
    search?: string;
    trashed?: TrashedFilter;
}

const props = defineProps<{
    users: PaginatedData<User>;
    filters: UserFilters;
}>();

// Format long UUID for clean table layout (e.g., "e2a4...8f91")
const formatUuid = (uuid: string): string => {
    if (!uuid) return "";
    return `${uuid.substring(0, 8)}...${uuid.substring(uuid.length - 4)}`;
};

const softDeleteUser = (id: string): void => {
    if (confirm("Delete this user?")) {
        router.delete((window as any).route("user.destroy", id));
    }
};

const forceDeleteUser = (id: string): void => {
    if (confirm("Permanently delete this user?")) {
        router.delete((window as any).route("user.force-delete", id));
    }
};
</script>

<template>
    <Head title="User" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                User
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <DataTable
                        :data="users"
                        route-name="user.index"
                        :search="filters.search"
                        :trashed="filters.trashed"
                        search-placeholder="Search by UUID, name, or email..."
                    >
                        <template #head>
                            <th class="px-6 py-4">UUID</th>
                            <th class="px-6 py-4">User Details</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </template>

                        <template #body>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="hover:bg-gray-50/50"
                            >
                                <!-- Formatted UUID with full string in title tooltip -->
                                <td
                                    class="px-6 py-4 font-mono text-xs text-gray-500"
                                    :title="user.id"
                                >
                                    {{ formatUuid(user.id) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        {{ user.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        @{{ user.username }} • {{ user.email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="
                                            user.deleted_at
                                                ? 'text-rose-500'
                                                : 'text-emerald-500'
                                        "
                                        class="text-xs font-semibold"
                                    >
                                        {{
                                            user.deleted_at
                                                ? "Trashed"
                                                : "Active"
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        v-if="!user.deleted_at"
                                        @click="softDeleteUser(user.id)"
                                        class="text-rose-500 hover:underline"
                                    >
                                        Trash
                                    </button>
                                    <button
                                        v-else
                                        @click="forceDeleteUser(user.id)"
                                        class="text-rose-700 hover:underline"
                                    >
                                        Force Delete
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
