<template>
    <v-avatar color="green" size="24" v-if="checkForTrue(value)">
        <v-icon size="small" class="text-white">
            mdi-checkbox-marked-circle-outline
        </v-icon>
    </v-avatar>
    <v-avatar color="red" size="24" v-if="checkForFalse(value)">
        <v-icon size="small" class="text-white">
            mdi-checkbox-blank-circle-outline
        </v-icon>
    </v-avatar>
</template>

<script setup>
const props = defineProps(["value"]);
import { checkForTrue, checkForFalse, makeSchema } from "@/utils";
</script>
