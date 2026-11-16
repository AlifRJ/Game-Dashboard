<script setup lang="ts">
import { ref } from "vue";
import { router, Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    ArrowLeftIcon,
    PencilSquareIcon,
    TrashIcon,
    ArrowUturnLeftIcon,
    XCircleIcon,
    ClipboardDocumentIcon,
    CheckIcon,
} from "@heroicons/vue/24/outline";

export interface User {
    id: string;
    name: string;
    username: string;
    email: string;
    email_verified_at?: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

const props = defineProps<{
    user: User;
}>();

const copied = ref(false);

const copyUuid = async (): Promise<void> => {
    await navigator.clipboard.writeText(props.user.id);
    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2000);
};

const formatDate = (dateString: string | null | undefined): string => {
    if (!dateString) return "—";
    return new Intl.DateTimeFormat("en-US", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(dateString));
};

const goBack = (): void => {
    router.get((window as any).route("user.index"));
};

const editUser = (): void => {
    router.get((window as any).route("user.edit", props.user.id));
};

const softDeleteUser = (): void => {
    if (confirm("Delete this user?")) {
        router.delete((window as any).route("user.destroy", props.user.id), {
            preserveScroll: true,
        });
    }
};

const restoreUser = (): void => {
    if (confirm("Restore this user?")) {
        router.patch(
            (window as any).route("user.restore", props.user.id),
            {},
            {
                preserveScroll: true,
            },
        );
    }
};

const forceDeleteUser = (): void => {
    if (confirm("Permanently delete this user?")) {
        router.delete(
            (window as any).route("user.forcedelete", props.user.id),
            {
                preserveScroll: true,
            },
        );
    }
};
</script>

<template>
    <Head :title="`User - ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="goBack"
                        title="Back to Users"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-600 shadow-sm transition hover:border-sky-300 hover:text-sky-700"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <div>
                        <h2
                            class="text-xl font-semibold leading-tight text-gray-800"
                        >
                            User Details
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <template v-if="!user.deleted_at">
                        <button
                            type="button"
                            @click="editUser"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-amber-300 hover:text-amber-700"
                        >
                            <PencilSquareIcon
                                class="h-4 w-4 text-yellow-500"
                                aria-hidden="true"
                            />
                            <span>Edit</span>
                        </button>

                        <button
                            type="button"
                            @click="softDeleteUser"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-rose-300 hover:text-rose-700"
                        >
                            <TrashIcon
                                class="h-4 w-4 text-rose-500"
                                aria-hidden="true"
                            />
                            <span>Move to Trash</span>
                        </button>
                    </template>

                    <template v-else>
                        <button
                            type="button"
                            @click="restoreUser"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700"
                        >
                            <ArrowUturnLeftIcon
                                class="h-4 w-4 text-blue-500"
                                aria-hidden="true"
                            />
                            <span>Restore</span>
                        </button>

                        <button
                            type="button"
                            @click="forceDeleteUser"
                            class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-2 text-sm font-medium text-rose-700 shadow-sm transition hover:bg-rose-100"
                        >
                            <XCircleIcon
                                class="h-4 w-4 text-rose-600"
                                aria-hidden="true"
                            />
                            <span>Permanently Delete</span>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg border border-gray-100"
                >
                    <!-- Profile Summary Header -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 px-6 py-5"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-100 text-lg font-semibold text-sky-700"
                            >
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ user.name }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ user.username }} • {{ user.email }}
                                </p>
                            </div>
                        </div>

                        <span
                            :class="
                                user.deleted_at
                                    ? 'bg-rose-50 text-rose-600 ring-rose-500/20'
                                    : 'bg-emerald-50 text-emerald-600 ring-emerald-500/20'
                            "
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset"
                        >
                            {{ user.deleted_at ? "Trashed" : "Active" }}
                        </span>
                    </div>

                    <!-- User Information Grid -->
                    <dl class="divide-y divide-gray-100">
                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                UUID
                            </dt>
                            <dd
                                class="mt-1 flex items-center gap-2 font-mono text-sm text-gray-900 sm:col-span-2 sm:mt-0"
                            >
                                <span class="break-all">{{ user.id }}</span>
                                <button
                                    type="button"
                                    @click="copyUuid"
                                    title="Copy UUID"
                                    class="rounded p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                >
                                    <CheckIcon
                                        v-if="copied"
                                        class="h-4 w-4 text-emerald-500"
                                    />
                                    <ClipboardDocumentIcon
                                        v-else
                                        class="h-4 w-4"
                                    />
                                </button>
                            </dd>
                        </div>

                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                Full Name
                            </dt>
                            <dd
                                class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"
                            >
                                {{ user.name }}
                            </dd>
                        </div>

                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                Username
                            </dt>
                            <dd
                                class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"
                            >
                                {{ user.username }}
                            </dd>
                        </div>

                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                Email Address
                            </dt>
                            <dd
                                class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"
                            >
                                {{ user.email }}
                            </dd>
                        </div>

                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                Created At
                            </dt>
                            <dd
                                class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"
                            >
                                {{ formatDate(user.created_at) }}
                            </dd>
                        </div>

                        <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500">
                                Last Updated
                            </dt>
                            <dd
                                class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0"
                            >
                                {{ formatDate(user.updated_at) }}
                            </dd>
                        </div>

                        <div
                            v-if="user.deleted_at"
                            class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 bg-rose-50/30"
                        >
                            <dt class="text-sm font-medium text-rose-600">
                                Deleted At
                            </dt>
                            <dd
                                class="mt-1 text-sm text-rose-700 sm:col-span-2 sm:mt-0"
                            >
                                {{ formatDate(user.deleted_at) }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
