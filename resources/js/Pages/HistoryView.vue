<template>
    <v-container v-if="mayReadHistory()">
        <h1 class="text-2xl">History</h1>
        <v-row class="mt-4 align-center">
            <v-menu
                v-model="r.showBeginDatePicker"
                :close-on-content-click="false"
                :offset="40"
                transition="scale-transition"
                min-width="290px"
            >
                <template v-slot:activator="{ props }">
                    <v-text-field
                        v-model="beginDateTxt"
                        label="Beginn"
                        prepend-icon="mdi-calendar-edit"
                        readonly
                        v-bind="props"
                        :error="!!hform.errors.begin"
                        :error-messages="hform.errors.begin"
                    ></v-text-field>
                </template>
                <v-date-picker
                    v-model="r.beginDateObj"
                    v-on:update:model-value="r.showBeginDatePicker = false"
                    :max="today"
                ></v-date-picker>
            </v-menu>
            <v-spacer />
            <v-menu
                v-model="r.showEndDatePicker"
                :close-on-content-click="false"
                :offset="40"
                transition="scale-transition"
                min-width="290px"
            >
                <template v-slot:activator="{ props }">
                    <v-text-field
                        v-model="endDateTxt"
                        label="Ende"
                        prepend-icon="mdi-calendar-edit"
                        readonly
                        v-bind="props"
                        :error="!!hform.errors.end"
                        :error-messages="hform.errors.end"
                    ></v-text-field>
                </template>
                <v-date-picker
                    v-model="r.endDateObj"
                    v-on:update:model-value="r.showEndDatePicker = false"
                    :max="today"
                ></v-date-picker>
            </v-menu>
            <v-spacer />
            <v-btn
                :readonly="!(beginDateTxt && endDateTxt)"
                @click.prevent="showRange"
                >Zeige</v-btn
            >
        </v-row>
        <h2 class="my-8 text-2xl">Oder (für gelöschte Mitglieder):</h2>
        <v-spacer />
        <v-row class="align-center">
            <v-text-field
                v-model="r.userId"
                label="UserId"
                prepend-icon="mdi-account"
                type="number"
            ></v-text-field>
            <v-spacer />
            <v-btn :readonly="!r.userId" @click.prevent="showId">Zeige</v-btn>
        </v-row>
        <HistoryDialog
            v-if="history && history.length > 0"
            :teams="teams"
            :members="members"
            :users="users"
            :history="history"
        />
    </v-container>
    <v-container v-else>
        <h1>History only available as admin with special privilege</h1>
    </v-container>
</template>

<script setup>
import HistoryDialog from "@/Components/HistoryDialog.vue";
import { computed, reactive } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy";

import { fromDate } from "../utils.js";

const props = defineProps({
    members: Array,
    teams: Array,
    users: Array,
    history: Array,
});

const r = reactive({
    showBeginDatePicker: false,
    showEndDatePicker: false,
    beginDateObj: null,
    endDateObj: new Date(),
    userId: "",
});

const hform = useForm({
    begin: "",
    end: "",
    id: -1,
    m_or_t: "m",
});
const page = usePage();

function mayReadHistory() {
    return page.props.user.may_read_history;
}

const today = computed(() => new Date()); // .toISOString().substring(0, 10)
const beginDateTxt = computed(() => fromDate(r.beginDateObj));
const endDateTxt = computed(() => fromDate(r.endDateObj));

function showRange() {
    console.log("showRange", JSON.stringify(hform));
    hform.begin = beginDateTxt;
    hform.end = endDateTxt;
    hform.id = -1;
    hform.m_or_t = "";
    hform.post(route("history.showWithHistory"));
    console.log("showRange", JSON.stringify(hform));
}

function showId() {
    hform.begin = "";
    hform.end = "";
    hform.id = r.userId;
    hform.m_or_t = "m";
    hform.post(route("history.showWithHistory"));
}
</script>
