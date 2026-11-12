<script setup lang="ts">
import { router, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable, {
    type PaginatedData,
    type TrashedFilter,
} from "@/Components/DataTable.vue";
import {
    EyeIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowUturnLeftIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";

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

const showUser = (user: User): void => {
    router.get((window as any).route("user.show", user.id));
};

const editUser = (user: User): void => {
    router.patch((window as any).route("user.edit", user.id));
};

const softDeleteUser = (user: User): void => {
    if (confirm("Delete this user?")) {
        router.delete((window as any).route("user.destroy", user.id), {
            preserveScroll: true,
        });
    }
};

const restoreUser = (user: User): void => {
    if (confirm("restore this user?")) {
        router.patch((window as any).route("user.restore", user.id), {
            preserveScroll: true,
        });
    }
};

const forceDeleteUser = (user: User): void => {
    if (confirm("Permanently delete this user?")) {
        router.delete((window as any).route("user.forcedelete", user.id), {
            preserveScroll: true,
        });
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
                                    <div
                                        class="inline-flex items-center justify-end gap-2"
                                    >
                                        <button
                                            type="button"
                                            @click="showUser(user)"
                                            title="View User"
                                            class="p-1 rounded hover:bg-gray-100 text-green-500 hover:text-green-600"
                                        >
                                            <EyeIcon class="w-5 h-5" />
                                        </button>

                                        <button
                                            v-if="!user.deleted_at"
                                            type="button"
                                            @click="editUser(user)"
                                            title="Edit User"
                                            aria-label="Edit User"
                                            class="p-1 rounded hover:bg-gray-100 text-yellow-500 hover:text-yellow-600"
                                        >
                                            <PencilSquareIcon class="w-5 h-5" />
                                        </button>

                                        <button
                                            v-if="!user.deleted_at"
                                            type="button"
                                            @click="softDeleteUser(user)"
                                            title="Move to Trash"
                                            aria-label="Move to Trash"
                                            class="p-1 rounded hover:bg-gray-100 text-rose-500 hover:text-rose-600"
                                        >
                                            <TrashIcon class="w-5 h-5" />
                                        </button>

                                        <template v-else>
                                            <button
                                                type="button"
                                                @click="restoreUser(user)"
                                                title="Restore User"
                                                aria-label="Restore User"
                                                class="p-1 rounded hover:bg-gray-100 text-blue-500 hover:text-blue-600"
                                            >
                                                <ArrowUturnLeftIcon
                                                    class="w-5 h-5"
                                                />
                                            </button>

                                            <button
                                                type="button"
                                                @click="forceDeleteUser(user)"
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
