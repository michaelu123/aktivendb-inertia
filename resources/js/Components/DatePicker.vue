<template>
    <v-row class="items-center max-w-full">
        <v-menu
            v-model="r.showDatePicker"
            :close-on-content-click="false"
            :offset="40"
            transition="scale-transition"
            min-width="290px"
            :disabled="disabled"
        >
            <template v-slot:activator="{ props }">
                <v-text-field
                    class="ml-4"
                    v-model="date_txt"
                    :label="label"
                    prepend-icon="mdi-calendar-edit"
                    readonly
                    v-bind="props"
                    :error="!!error"
                    :error-messages="error_txt"
                ></v-text-field>
            </template>
            <v-date-picker
                v-model="r.date_obj"
                v-on:update:model-value="dateEv"
                :max="maxDate"
                :min="minDate"
            ></v-date-picker>
        </v-menu>
        <v-btn
            color="primary"
            variant="outlined"
            class="mb-5 ml-2"
            :disabled="disabled"
            @click="date_txt = null"
        >
            <v-icon start>mdi-delete</v-icon> Datum löschen
        </v-btn>
    </v-row>
</template>

<script setup>
import { reactive } from "vue";
import { fromDate } from "@/utils";

const props = defineProps([
    "disabled",
    "label",
    "error",
    "error_txt",
    "maxDate",
    "minDate",
]);

const date_txt = defineModel("date_txt");

const r = reactive({
    date_obj: null,
    showDatePicker: false,
});

function dateEv() {
    r.showDatePicker = false;
    date_txt.value = fromDate(r.date_obj);
}
</script>
