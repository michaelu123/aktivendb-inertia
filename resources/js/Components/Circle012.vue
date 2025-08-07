<template>
    <v-avatar color="grey" size="24" v-if="value == 0">
        <v-icon size="small" class="text-white">
            mdi-checkbox-blank-circle
        </v-icon>
    </v-avatar>
    <v-avatar color="green" size="24" v-if="value == 1">
        <v-icon size="small" class="text-white">
            mdi-checkbox-marked-circle-outline
        </v-icon>
    </v-avatar>
    <v-avatar color="red" size="24" v-if="value == 2">
        <v-icon size="small" class="text-white">
            mdi-checkbox-blank-circle-outline
        </v-icon>
    </v-avatar>
</template>

<script setup>
const props = defineProps(["value"]);
</script>
