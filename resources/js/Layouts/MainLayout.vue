<template>
    <header
        class="w-full bg-white border-b border-gray-200 dark:border-gray-700 dark:bg-gray-900"
    >
        <Navigation />
    </header>

    <main class="container w-full p-4 mx-auto">
        <div
            v-if="flashSuccess"
            class="p-2 mb-4 border border-green-200 rounded-md shadow-sm dark:border-green-800 bg-green-50 dark:bg-green-900"
        >
            {{ flashSuccess }}
        </div>
        <slot>Default</slot>
    </main>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import Navigation from "./Navigation.vue";

const page = usePage();
const flashSuccess = computed(() => page.props?.flash?.success);
const user = computed(() => page.props?.user);
</script>
