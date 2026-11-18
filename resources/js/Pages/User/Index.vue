<script setup lang="ts">
import { router, Head, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable, {
    type PaginatedData,
    type TrashedFilter,
} from "@/Components/DataTable.vue";
import {
    ExclamationCircleIcon,
    CheckCircleIcon,
    ArrowPathIcon,
    EyeIcon,
    PlusCircleIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowUturnLeftIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";

export interface User {
    id: string;
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
    error: String;
}>();

const isRefreshing = ref(false);

function refreshUsers() {
    router.reload({
        only: ["users"],
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

// Format long UUID for clean table layout (e.g., "e2a4...8f91")
const formatUuid = (uuid: string): string => {
    if (!uuid) return "";
    return `${uuid.substring(0, 8)}...${uuid.substring(uuid.length - 4)}`;
};

// --- Action Buttons Handler --- //
const showUser = (user: User): void => {
    router.get((window as any).route("user.show", user.id));
};

const addUser = (): void => {
    router.get((window as any).route("user.create"));
};

const editUser = (user: User): void => {
    router.get((window as any).route("user.edit", user.id));
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

// Handle message from action
const page = usePage();
const flashMessage = computed(() => page.props.flash?.message);
</script>

<template>
    <Head title="User" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    User
                </h2>

                <div>
                    <button
                        v-if="!error"
                        type="button"
                        @click="addUser"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-sky-300 hover:text-sky-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <PlusCircleIcon
                            :class="['h-4 w-4 text-sky-600']"
                            aria-hidden="true"
                        />
                        <span>Add User</span>
                    </button>
                    <button
                        type="button"
                        :disabled="isRefreshing"
                        @click="refreshUsers"
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
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Notification Banner -->
                    <div
                        v-if="error"
                        class="flex flex-col justify-between gap-4 rounded-xl border border-red-200/80 bg-red-50/70 p-4 shadow-sm sm:flex-row sm:items-center"
                    >
                        <div class="flex items-start gap-3 sm:items-center">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600 ring-1 ring-inset ring-red-500/20"
                            >
                                <ExclamationCircleIcon
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <h3
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Unable to Fetch Data
                                </h3>
                                <p class="text-sm text-slate-600">
                                    {{ error }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="flashMessage"
                        role="status"
                        class="flex flex-col justify-between gap-4 rounded-xl border border-emerald-200/80 bg-emerald-50/70 p-4 shadow-sm sm:flex-row sm:items-center"
                    >
                        <div class="flex items-start gap-3 sm:items-center">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 ring-1 ring-inset ring-emerald-500/20"
                            >
                                <CheckCircleIcon
                                    class="h-5 w-5"
                                    aria-hidden="true"
                                />
                            </div>
                            <div>
                                <h3
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    Success
                                </h3>
                                <p class="text-sm text-slate-600">
                                    {{ flashMessage }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <DataTable
                        :data="users"
                        route-name="user.index"
                        :search="filters.search"
                        :trashed="filters.trashed"
                        search-placeholder="Search by UUID, name, or email..."
                        class="mt-6"
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
                                        {{ user.username }} • {{ user.email }}
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
