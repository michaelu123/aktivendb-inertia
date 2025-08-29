<template>
    <v-toolbar>
        <v-toolbar-title class="mr-2 text-h5">
            <span class="font-weight-light"
                >Aktivenverwaltung ADFC München</span
            >
        </v-toolbar-title>
        <v-toolbar-items>
            <v-btn variant="text" :to="route('home')"> Home </v-btn>
            <v-btn
                variant="text"
                :to="route('member.index')"
                @click="clearRemIndexM"
                v-if="is_logged_in"
            >
                Aktive
            </v-btn>
            <v-btn
                variant="text"
                :to="route('team.index')"
                @click="clearRemIndexT"
                v-if="is_logged_in"
            >
                AG's/OG's
            </v-btn>
            <v-btn
                variant="text"
                :to="route('history.show')"
                v-if="may_read_history"
            >
                History</v-btn
            >
        </v-toolbar-items>
        <div class="flex-grow-1"></div>
        <v-toolbar-items>
            <v-btn variant="text" to="/login" v-if="!is_logged_in">
                <span class="font-weight-light">Login</span>
            </v-btn>
            <v-btn variant="text" :to="route('logout')" v-if="is_logged_in">
                <span class="font-weight-light">Logout</span>
            </v-btn>
        </v-toolbar-items>
    </v-toolbar>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const is_logged_in = computed(() => {
    return !!page.props?.user;
});
const may_read_history = computed(() => {
    return page.props?.user?.may_read_history;
});

function clearRemIndexM() {
    localStorage.removeItem("searchM");
    localStorage.removeItem("pageNoM");
    localStorage.removeItem("itemsPerPageM");
    localStorage.removeItem("readonlyM");
}

function clearRemIndexT() {
    localStorage.removeItem("searchT");
    localStorage.removeItem("pageNoT");
    localStorage.removeItem("itemsPerPageT");
    localStorage.removeItem("readonlyT");
}
</script>
