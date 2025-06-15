<template>
    <div>Index Counter {{ counter }}</div>
    <Link href="/hello">Show Page</Link>
    <div>msg: {{ message }}</div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
defineProps({ message: String });
import { ref } from "vue";
const counter = ref(0);
setInterval(() => {
    counter.value++;
}, 1000);
</script>
