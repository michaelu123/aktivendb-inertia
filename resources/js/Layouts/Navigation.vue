<template>
    <v-toolbar>
        <v-toolbar-title class="mr-2 text-h5">
            <span class="font-weight-light"
                >Aktivenverwaltung ADFC München</span
            >
        </v-toolbar-title>
        <v-toolbar-items>
            <v-btn variant="text" :to="route('index.index')"> Home </v-btn>
            <v-btn
                variant="text"
                :to="route('member.index')"
                v-if="is_logged_in"
            >
                Aktive
            </v-btn>
            <v-btn variant="text" to="/project_teams" v-if="is_logged_in">
                AG's/OG's
            </v-btn>
            <v-btn variant="text" to="/history" v-if="may_read_history">
                History</v-btn
            >
        </v-toolbar-items>
        <div class="flex-grow-1"></div>
        <v-toolbar-items>
            <v-btn variant="text" to="/login" v-if="!is_logged_in">
                <span class="font-weight-light">Login</span>
            </v-btn>
            <v-btn variant="text" to="/logout" v-if="is_logged_in">
                <span class="font-weight-light">Logout</span>
            </v-btn>
        </v-toolbar-items>
    </v-toolbar>
</template>

<script>
export default {
    name: "Navigation",
    computed: {
        is_logged_in() {
            return true; // this.$store.state.is_logged_in;
        },
        may_read_history() {
            return true; // this.$store.state.may_read_history;
        },
    },
};
</script>

<style scoped></style>
