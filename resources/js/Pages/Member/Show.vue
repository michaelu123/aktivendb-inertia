<template>
    <p>{{ $page }}</p>
    <v-btn
        color="primary"
        variant="outlined"
        class="mb-2 ml-2"
        v-bind="props"
        v-if="!readonly"
    >
        <v-icon start>mdi-plus</v-icon> Mitglied Hinzufügen
    </v-btn>
    <v-card>
        <v-card-title>
            <div>
                <span class="text-h5">Mitglied</span>
                <v-alert
                    :type="alert.type"
                    variant="outlined"
                    v-model="alert.shown"
                    closable
                    width="100%"
                >
                    {{ alert.text }}
                </v-alert>
            </div>
        </v-card-title>
        <v-card-text v-if="editWindow.loading">
            Wird geladen...
            <v-progress-circular
                indeterminate
                color="primary"
            ></v-progress-circular>
        </v-card-text>

        <v-card-text v-if="!editWindow.loading">
            <v-container>
                <v-row v-if="isAdmin()">
                    <v-btn
                        class="mr-5"
                        height="60"
                        type="submit"
                        variant="outlined"
                        color="red"
                        @click.prevent="signUp"
                    >
                        Als AktivenDB-Benutzer einrichten
                    </v-btn>
                    <v-text-field
                        :append-icon="r.showPwd ? 'mdi-eye' : 'mdi-eye-off'"
                        :type="r.showPwd ? 'text' : 'password'"
                        @click:append="r.showPwd = !r.showPwd"
                        variant="outlined"
                        label="Mit initialem Passwort..."
                        v-model="r.dbpasswd"
                        color="red"
                    ></v-text-field>
                </v-row>
                <v-form
                    ref="form"
                    v-model="editWindow.formValid"
                    lazy-validation
                >
                    <v-text-field
                        v-model="editedItem.last_name"
                        label="Nachname"
                        required
                        :readonly="readonly"
                        :error="!!editWindow.errors.last_name"
                        :error-messages="editWindow.errors.last_name"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.first_name"
                        label="Vorname"
                        required
                        :readonly="readonly"
                        :error="!!editWindow.errors.first_name"
                        :error-messages="editWindow.errors.first_name"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.birthday"
                        label="Geburtsjahr"
                        required
                        :readonly="readonly"
                        :error="!!editWindow.errors.birthday"
                        :error-messages="editWindow.errors.birthday"
                    ></v-text-field>

                    <v-text-field
                        v-model="editedItem.gender"
                        label="Geschlecht"
                        :rules="[
                            (v) =>
                                v == 'M' ||
                                v == 'W' ||
                                v == '' ||
                                'Bitte M oder W oder nichts angeben',
                        ]"
                        :readonly="readonly"
                        :error="!!editWindow.errors.gender"
                        :error-messages="editWindow.errors.gender"
                    ></v-text-field>

                    <v-text-field
                        v-model="editedItem.email_adfc"
                        label="E-mail (ADFC)"
                        :readonly="readonly"
                        :error="!!editWindow.errors.email_adfc"
                        :error-messages="editWindow.errors.email_adfc"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.email_private"
                        label="E-mail (Privat)"
                        required
                        :readonly="readonly"
                        :error="!!editWindow.errors.email_private"
                        :error-messages="editWindow.errors.email_private"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.phone_primary"
                        label="Telefon"
                        :readonly="readonly"
                        :error="!!editWindow.errors.phone_primary"
                        :error-messages="editWindow.errors.phone_primary"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.phone_secondary"
                        label="Telefon (alternativ)"
                        :readonly="readonly"
                        :error="!!editWindow.errors.phone_secondary"
                        :error-messages="editWindow.errors.phone_secondary"
                    ></v-text-field>
                    <v-menu
                        v-model="editWindow.showLatestContactDatePicker"
                        :close-on-content-click="false"
                        :offset="40"
                        transition="scale-transition"
                        min-width="290px"
                        :disabled="readonly"
                    >
                        <template v-slot:activator="{ props }">
                            <v-text-field
                                v-model="editedItem.latest_contact"
                                label="Letzter Kontakt"
                                prepend-icon="mdi-calendar-edit"
                                readonly
                                v-bind="props"
                                :error="!!editWindow.errors.latest_contact"
                                :error-messages="
                                    editWindow.errors.latest_contact
                                "
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="latest_contact_obj"
                            v-on:update:model-value="lastContactEv"
                            :max="today"
                        ></v-date-picker>
                    </v-menu>
                    <v-textarea
                        v-model="editedItem.status"
                        label="Status"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editWindow.errors.status"
                        :error-messages="editWindow.errors.status"
                    ></v-textarea>
                    <v-textarea
                        v-model="editedItem.address"
                        label="Postleitzahl"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editWindow.errors.address"
                        :error-messages="editWindow.errors.address"
                    ></v-textarea>
                    <v-text-field
                        v-model="editedItem.adfc_id"
                        label="Mitgliedsnummer"
                        type="number"
                        :readonly="readonly"
                        :error="!!editWindow.errors.adfc_id"
                        :error-messages="editWindow.errors.adfc_id"
                    ></v-text-field>
                    <v-textarea
                        v-model="editedItem.admin_comments"
                        label="Kommentar"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editWindow.errors.admin_comments"
                        :error-messages="editWindow.errors.admin_comments"
                    ></v-textarea>
                    <v-textarea
                        v-model="editedItem.interests"
                        label="Interessen"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editWindow.errors.interests"
                        :error-messages="editWindow.errors.interests"
                    ></v-textarea>
                    <v-menu
                        v-model="
                            editWindow.showLatestFirstAidTrainingDatePicker
                        "
                        :close-on-content-click="false"
                        :offset="40"
                        transition="scale-transition"
                        min-width="290px"
                        :disabled="readonly"
                    >
                        <template v-slot:activator="{ props }">
                            <v-text-field
                                v-model="editedItem.latest_first_aid_training"
                                label="Letzte 1. Hilfe Schulung"
                                prepend-icon="mdi-calendar-edit"
                                readonly
                                v-bind="props"
                                :error="
                                    !!editWindow.errors
                                        .latest_first_aid_training
                                "
                                :error-messages="
                                    editWindow.errors.latest_first_aid_training
                                "
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="latest_first_aid_training_obj"
                            v-on:update:model-value="lastEhkEv"
                            :max="today"
                        ></v-date-picker>
                    </v-menu>
                    <v-row>
                        <v-menu
                            v-model="
                                editWindow.showNextFirstAidTrainingDatePicker
                            "
                            :close-on-content-click="false"
                            :offset="40"
                            transition="scale-transition"
                            min-width="290px"
                            :disabled="readonly"
                        >
                            <template v-slot:activator="{ props }">
                                <v-text-field
                                    v-model="editedItem.next_first_aid_training"
                                    label="Nächste 1. Hilfe Schulung"
                                    prepend-icon="mdi-calendar-edit"
                                    readonly
                                    v-bind="props"
                                    :error="
                                        !!editWindow.errors
                                            .next_first_aid_training
                                    "
                                    :error-messages="
                                        editWindow.errors
                                            .next_first_aid_training
                                    "
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                v-model="next_first_aid_training_obj"
                                v-on:update:model-value="nextEhkEv"
                                :min="today"
                            ></v-date-picker>
                        </v-menu>
                        <v-btn
                            color="primary"
                            variant="outlined"
                            class="mb-2 ml-2"
                            :disabled="readonly"
                            @click="editedItem.next_first_aid_training = null"
                        >
                            <v-icon start>mdi-delete</v-icon> Datum löschen
                        </v-btn>
                    </v-row>
                    <v-menu
                        v-model="editWindow.showQuestResponseDatePicker"
                        :close-on-content-click="false"
                        :offset="40"
                        transition="scale-transition"
                        min-width="290px"
                        :disabled="noAdminOrReadOnly"
                    >
                        <template v-slot:activator="{ props }">
                            <v-text-field
                                v-model="
                                    editedItem.responded_to_questionaire_at
                                "
                                label="Datum Fragebogen"
                                prepend-icon="mdi-calendar-edit"
                                readonly
                                v-bind="props"
                                :error="
                                    !!editWindow.errors
                                        .responded_to_questionaire_at
                                "
                                :error-messages="
                                    editWindow.errors
                                        .responded_to_questionaire_at
                                "
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="responded_to_questionaire_at_obj"
                            v-on:update:model-value="respToQuEv"
                            :max="today"
                        ></v-date-picker>
                    </v-menu>
                    <v-select
                        clearable
                        v-model="editedItem.dsgvo_signature"
                        :readonly="readonly"
                        :items="abgegeben"
                        item-title="title"
                        item-value="value"
                        label="DSGVO"
                        required
                    ></v-select>
                    <v-select
                        clearable
                        v-model="editedItem.police_certificate"
                        :readonly="readonly"
                        :items="abgegeben"
                        item-title="title"
                        item-value="value"
                        label="Pol.Zeugnis"
                        required
                    ></v-select>
                    <v-row>
                        <v-switch
                            class="mr-5"
                            v-model="editedItem.active"
                            label="Aktiv"
                            :disabled="readonly"
                            :value-comparator="checkForTrue"
                            :error="!!editWindow.errors.active"
                            :error-messages="editWindow.errors.active"
                        ></v-switch>
                        <v-switch
                            class="ml-5"
                            v-model="
                                editedItem.registered_for_first_aid_training
                            "
                            label="Registriert für Erste-Hilfe-Kurs"
                            :disabled="readonly"
                            :value-comparator="checkForTrue"
                            @update:model-value="registerFirstAid"
                            :error="!!editWindow.errors.active"
                            :error-messages="editWindow.errors.active"
                        ></v-switch>
                        <v-switch
                            class="ml-5"
                            v-model="editedItem.responded_to_questionaire"
                            label="Fragebogen ausgefüllt"
                            :disabled="noAdminOrReadOnly"
                            :value-comparator="checkForTrue"
                            @update:model-value="setResponded"
                            :error="!!editWindow.errors.active"
                            :error-messages="editWindow.errors.active"
                        ></v-switch>
                    </v-row>
                </v-form>
                <template v-if="editedItem.id > 0">
                    <v-data-table
                        :headers="editWindow.teamList.headers"
                        :items="projectTeams"
                        :search="r.searchEditWindow"
                        @click:row="viewProjectTeamMemberItem"
                    >
                        <template v-slot:top>
                            <!-- <AddTeamToMemberDialog
                                :editWindow="editWindow"
                                :editedItem="editedItem"
                                :memberRoles="memberRoles"
                                :allProjectTeams="allProjectTeams"
                                :readonly="readonly"
                                @closeTM="closeEditProjectTeamMemberWindow"
                                @saveTM="saveEditProjectTeamMemberWindow"
                            ></AddTeamToMemberDialog>
                            <v-spacer></v-spacer> -->
                            <v-text-field
                                v-model="r.searchEditWindow"
                                label="Suchen"
                                append-icon="mdi-magnify"
                                single-line
                                hide-details
                            ></v-text-field>
                        </template>
                        <template v-slot:item.action="{ item }">
                            <v-icon
                                size="small"
                                class="mr-2"
                                @click.stop="editProjectTeamMemberItem(item)"
                                v-if="!readonly"
                                >mdi-pencil
                            </v-icon>
                            <v-icon
                                size="small"
                                @click.stop="deleteProjectTeamMemberItem(item)"
                                v-if="!readonly"
                                >mdi-delete
                            </v-icon>
                        </template>
                    </v-data-table>
                </template>
            </v-container>
        </v-card-text>

        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="text" @click="closeEW">Abbrechen</v-btn>
            <v-btn
                variant="text"
                @click="saveEW"
                :loading="editWindow.saveInProgress"
                v-if="!readonly"
                >Speichern</v-btn
            >
        </v-card-actions>
    </v-card>
</template>

<script setup>
import { computed, reactive } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { checkForTrue } from "@/utils";

const props = defineProps({
    member: Object,
    readonly: Boolean,
    projectTeams: Array,
    // without errors, flash,user console shows warnings
    errors: Object,
    flash: Object,
    user: Object,
});

const editedItem = props.member;

const alert = reactive({
    shown: false,
    text: "",
    type: "success",
});

const r = reactive({
    searchEditWindow: "",
    dbpasswd: "",
    showPwd: false,
});

const editWindow = reactive({
    loading: false,
    shown: false,
    formValid: true,
    saveInProgress: false,
    errors: {},
    showLatestContactDatePicker: false,
    showLatestFirstAidTrainingDatePicker: false,
    showNextFirstAidTrainingDatePicker: false,
    showQuestResponseDatePicker: false,
    teamList: {
        headers: [
            {
                title: "Actions",
                key: "action",
                sortable: false,
            },
            {
                title: "AG",
                key: "name",
            },
            {
                title: "Funktion",
                key: "project_team_member.member_role_title",
            },
        ],
        editProjectTeamMemberWindow: {
            shown: false,
            readonly: false,
            formValid: true,
            saveInProgress: false,
            errors: {},
        },
        editedProjectTeamMemberIndex: -1,
        editedProjectTeamMember: {
            project_team_member: {
                admin_comments: "",
                id: -1,
                member_id: -1,
                member_role_id: -1,
                member_role_title: "",
                project_team_id: -1,
            },
            name: "",
        },
        defaultProjectTeamMember: {
            project_team_member: {
                admin_comments: "",
                id: -1,
                member_id: -1,
                member_role_id: -1,
                member_role_title: "",
                project_team_id: -1,
            },
            name: "",
        },
    },
});

const abgegeben = [
    { title: "irrelevant", value: 0 },
    { title: "abgegeben", value: 1 },
    { title: "noch nicht abgegeben", value: 2 },
];

const page = usePage();

async function signUp() {
    if (r.dbpasswd == "") {
        showAlert("error", "Bitte Passwort angeben");
        return;
    }
    var email = editedItem.email_adfc;
    if (email == null || email == "") {
        email = editedItem.email_private;
    }
    if (email == null || email == "") {
        showAlert("error", "Keine email-Adresse verfügbar");
        return;
    }

    var newUser = {
        member_id: editedItem.id,
        email: email,
        password: r.dbpasswd,
    };

    try {
        await $http.post(
            "/api/user?token=" + sessionStorage.getItem("token"),
            newUser
        );
        closeEditWindow();
        showAlert("success", "Neuer Benutzer wurde gespeichert");
        r.dbpasswd = "";
    } catch (error) {
        try {
            await $http.put(
                "/api/user/" +
                    editedItem.user.id +
                    "?token=" +
                    sessionStorage.getItem("token"),
                newUser
            );
            showAlert("success", "Benutzer-Passwort wurde geändert");
        } catch (error) {
            closeEditWindow();
            handleRequestError(error, editWindow);
        }
    }
}

const noAdminOrReadOnly = computed(() => {
    return !isAdmin() || props.readonly;
});

function isAdmin() {
    return !!page.props.user.is_admin;
}

// function nextTraining() {
//     editWindow.showNextFirstAidTrainingDatePicker = false;
//     editedItem.registered_for_first_aid_training = true;
// }

function registerFirstAid(e) {
    if (!e) {
        editedItem.next_first_aid_training = null;
    }
}
// function respondedToQuestionaire() {
//     editWindow.showQuestResponseDatePicker = false;
//     editedItem.responded_to_questionaire = true;
// }

function setResponded(e) {
    if (!e) {
        editedItem.responded_to_questionaire_at = null;
    }
}

function showProjectTeamMemberItem(item) {
    editWindow.teamList.editedProjectTeamMemberIndex =
        projectTeams.indexOf(item);
    alerteditWindow.teamList.editProjectTeamMemberWindow.loading = true;

    alerteditWindow.teamList.editedProjectTeamMember = Object.assign(
        alertprojectTeams[
            alerteditWindow.teamList.editedProjectTeamMemberIndex
        ],
        item
    );

    alerteditWindow.teamList.editProjectTeamMemberWindow.shown = true;
}

function viewProjectTeamMemberItem(ev, { item }) {
    showProjectTeamMemberItem(item);
    editWindow.teamList.editProjectTeamMemberWindow.readonly = true;
}

function editProjectTeamMemberItem(item) {
    showProjectTeamMemberItem(item);
    editWindow.teamList.editProjectTeamMemberWindow.readonly = readonly;
}

function closeEW() {
    console.log("closeEW");
    router.get(
        route("member.index", {
            preserveState: true,
            preserveScroll: true,
        })
    );
}
function saveEW() {
    console.log("saveEW");
}

function showAlert(type, text) {
    alert.shown = true;
    alert.type = type;
    alert.text = text;

    setTimeout(() => {
        alert.shown = false;
        r.loading = false;
    }, 5000);
}
</script>
