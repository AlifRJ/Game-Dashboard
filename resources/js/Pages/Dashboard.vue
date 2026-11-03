<script setup lang="ts">
import { computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!page.props.auth?.user);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    v-if="isAuthenticated"
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">
                        Hello "{{ user.name }} - {{ user.email }}"
                    </div>
                </div>
                <div
                    v-else
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">Hello Guest!</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
