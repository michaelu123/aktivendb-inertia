<template>
    <header
        class="w-full bg-white border-b border-gray-200 dark:border-gray-700 dark:bg-gray-900"
    >
        <Navigation />
    </header>

    <main class="">
        <div
            v-if="flashSuccess"
            class="flex items-center justify-center h-10 mt-4 bg-green-200 border border-green-200 rounded-md shadow-sm dark:border-green-50 dark:bg-green-900"
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
