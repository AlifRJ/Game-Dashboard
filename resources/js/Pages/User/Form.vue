<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
});

const isEditing = computed(() => Boolean(props.user?.id));

const form = useForm({
    name: props.user?.name ?? "",
    username: props.user?.username ?? "",
    email: props.user?.email ?? "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    if (isEditing.value) {
        form.patch(route("users.update", props.user.id), {
            preserveScroll: true,
            onSuccess: () => form.reset("password", "password_confirmation"),
        });
    } else {
        form.post(route("users.store"), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    }
};
</script>

<template>
    <Head title="Form" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Form
            </h2>
        </template>

        <div
            class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-200"
        >
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ isEditing ? "Edit User" : "Create New User" }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{
                        isEditing
                            ? "Update account details below."
                            : "Fill in the information to register a new user."
                    }}
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- Name -->
                <div>
                    <label
                        for="name"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Full Name
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autocomplete="name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.name }"
                    />
                    <p
                        v-if="form.errors.name"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Username -->
                <div>
                    <label
                        for="username"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Username
                    </label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        required
                        autocomplete="username"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.username }"
                    />
                    <p
                        v-if="form.errors.username"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.username }}
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Email Address
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.email }"
                    />
                    <p
                        v-if="form.errors.email"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <label
                        for="password"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Password
                        <span
                            v-if="isEditing"
                            class="text-xs font-normal text-gray-500"
                        >
                            (Leave blank to keep current password)
                        </span>
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :required="!isEditing"
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        :class="{ 'border-red-500': form.errors.password }"
                    />
                    <p
                        v-if="form.errors.password"
                        class="mt-1 text-sm text-red-600"
                    >
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label
                        for="password_confirmation"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Confirm Password
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        :required="!isEditing || Boolean(form.password)"
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>

                <!-- Actions -->
                <div
                    class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100"
                >
                    <Link
                        :href="route('user.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>{{
                            isEditing ? "Update User" : "Create User"
                        }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
