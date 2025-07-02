<template>
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
                        :error="!!editedItem.errors.last_name"
                        :error-messages="editedItem.errors.last_name"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.first_name"
                        label="Vorname"
                        required
                        :readonly="readonly"
                        :error="!!editedItem.errors.first_name"
                        :error-messages="editedItem.errors.first_name"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.birthday"
                        label="Geburtsjahr"
                        required
                        :readonly="readonly"
                        :error="!!editedItem.errors.birthday"
                        :error-messages="editedItem.errors.birthday"
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
                        :error="!!editedItem.errors.gender"
                        :error-messages="editedItem.errors.gender"
                    ></v-text-field>

                    <v-text-field
                        v-model="editedItem.email_adfc"
                        label="E-mail (ADFC)"
                        :readonly="readonly"
                        :error="!!editedItem.errors.email_adfc"
                        :error-messages="editedItem.errors.email_adfc"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.email_private"
                        label="E-mail (Privat)"
                        required
                        :readonly="readonly"
                        :error="!!editedItem.errors.email_private"
                        :error-messages="editedItem.errors.email_private"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.phone_primary"
                        label="Telefon"
                        :readonly="readonly"
                        :error="!!editedItem.errors.phone_primary"
                        :error-messages="editedItem.errors.phone_primary"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.phone_secondary"
                        label="Telefon (alternativ)"
                        :readonly="readonly"
                        :error="!!editedItem.errors.phone_secondary"
                        :error-messages="editedItem.errors.phone_secondary"
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
                                :error="!!editedItem.errors.latest_contact"
                                :error-messages="
                                    editedItem.errors.latest_contact
                                "
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="r.latest_contact_obj"
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
                        :error="!!editedItem.errors.status"
                        :error-messages="editedItem.errors.status"
                    ></v-textarea>
                    <v-textarea
                        v-model="editedItem.address"
                        label="Postleitzahl"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editedItem.errors.address"
                        :error-messages="editedItem.errors.address"
                    ></v-textarea>
                    <v-text-field
                        v-model="editedItem.adfc_id"
                        label="Mitgliedsnummer"
                        type="number"
                        :readonly="readonly"
                        :error="!!editedItem.errors.adfc_id"
                        :error-messages="editedItem.errors.adfc_id"
                    ></v-text-field>
                    <v-textarea
                        v-model="editedItem.admin_comments"
                        label="Kommentar"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editedItem.errors.admin_comments"
                        :error-messages="editedItem.errors.admin_comments"
                    ></v-textarea>
                    <v-textarea
                        v-model="editedItem.interests"
                        label="Interessen"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editedItem.errors.interests"
                        :error-messages="editedItem.errors.interests"
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
                                    !!editedItem.errors
                                        .latest_first_aid_training
                                "
                                :error-messages="
                                    editedItem.errors.latest_first_aid_training
                                "
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="r.latest_first_aid_training_obj"
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
                                        !!editedItem.errors
                                            .next_first_aid_training
                                    "
                                    :error-messages="
                                        editedItem.errors
                                            .next_first_aid_training
                                    "
                                ></v-text-field>
                            </template>
                            <v-date-picker
                                v-model="r.next_first_aid_training_obj"
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
                                    !!editedItem.errors
                                        .responded_to_questionaire_at
                                "
                                :error-messages="
                                    editedItem.errors
                                        .responded_to_questionaire_at
                                "
                            ></v-text-field>
                        </template>
                        <v-date-picker
                            v-model="r.responded_to_questionaire_at_obj"
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
                            :error="!!editedItem.errors.active"
                            :error-messages="editedItem.errors.active"
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
                            :error="!!editedItem.errors.active"
                            :error-messages="editedItem.errors.active"
                        ></v-switch>
                        <v-switch
                            class="ml-5"
                            v-model="editedItem.responded_to_questionaire"
                            label="Fragebogen ausgefüllt"
                            :disabled="noAdminOrReadOnly"
                            :value-comparator="checkForTrue"
                            @update:model-value="setResponded"
                            :error="!!editedItem.errors.active"
                            :error-messages="editedItem.errors.active"
                        ></v-switch>
                    </v-row>
                </v-form>
                <v-btn
                    color="primary"
                    variant="outlined"
                    class="mb-2"
                    v-bind="props"
                    v-if="!readonly"
                    @click.prevent="addTeamToMember"
                >
                    <v-icon start>mdi-plus</v-icon> Mitglied zu AG Hinzufügen
                </v-btn>

                <template v-if="editedItem.id > 0">
                    <v-data-table
                        :headers="editWindow.teamList.headers"
                        :items="member.project_teams"
                        :search="r.searchEditWindow"
                        @click:row="viewProjectTeamMemberItem"
                    >
                        <template v-slot:top>
                            <!-- <AddTeamToMemberDialog /> -->
                            <AddTeamToMemberDialog
                                v-if="teamToMemberDialogShown"
                                :editWindow="editWindow"
                                :editedItem="editedItem"
                                :memberRoles="memberRoles"
                                :allProjectTeams="allProjectTeams"
                                :readonly="readonly"
                            />
                            <!-- <AddTeamToMemberDialog
                                :editWindow="editWindow"
                                :editedItem="editedItem"
                                :memberRoles="memberRoles"
                                :allProjectTeams="allProjectTeams"
                                :readonly="readonly"
                                @closeTM="closeEditProjectTeamMemberWindow"
                                @saveTM="saveEditProjectTeamMemberWindow"
                            /> -->
                            <v-spacer></v-spacer>
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
import { computed, onMounted, reactive } from "vue";
import { usePage, router, useForm } from "@inertiajs/vue3";
import { checkForTrue, fromDate } from "@/utils";
import AddTeamToMemberDialog from "@/Components/AddTeamToMemberDialog.vue";
import { route } from "ziggy";

const props = defineProps({
    member: Object,
    teamToMemberDialogShown: Boolean,
    teamIndex: -1,
    allProjectTeams: Array,
    memberRoles: Array,
    store: Object,
    // without errors,flash,user console shows warnings!?
    errors: Object,
    flash: Object,
    user: Object,
});

let readonly = computed(() => props.store.readonly1);

const editedItem = useForm({
    id: props.member.id ?? -1,
    name: props.member.name ?? "",
    first_name: props.member.first_name ?? "",
    last_name: props.member.last_name ?? "",
    birthday: props.member.birthday ?? "",
    email_adfc: props.member.email_adfc ?? "",
    email_private: props.member.email_private ?? "",
    phone_primary: props.member.phone_primary ?? "",
    phone_secondary: props.member.phone_secondary ?? "",
    address: props.member.address ?? "",
    adfc_id: props.member.adfc_id ?? "",
    admin_comments: props.member.admin_comments ?? "",
    reference: props.member.reference ?? "",
    latest_first_aid_training: props.member.latest_first_aid_training,
    next_first_aid_training: props.member.next_first_aid_training,
    gender: props.member.gender ?? "",
    interests: props.member.interests ?? "",
    latest_contact: props.member.latest_contact,
    active: props.member.active ?? true,
    registered_for_first_aid_training:
        props.member.registered_for_first_aid_training ?? false,
    responded_to_questionaire: props.member.responded_to_questionaire ?? false,
    responded_to_questionaire_at: props.member.responded_to_questionaire_at,
    sgvo_signature: props.member.dsgvo_signature,
    police_certificate: props.member.police_certificate,
    project_teams: props.member.project_teams ?? [],
});

const alert = reactive({
    shown: false,
    text: "",
    type: "success",
});

const r = reactive({
    searchEditWindow: "",
    dbpasswd: "",
    showPwd: false,
    latest_contact_obj: null,
    latest_first_aid_training_obj: null,
    next_first_aid_training_obj: null,
    responded_to_questionaire_at_obj: null,
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
            formValid: true,
            saveInProgress: false,
            errors: {},
        },
        editedProjectTeamMemberIndex: -1,
        editedProjectTeamMember: {
            project_team_member: {
                admin_comments: "",
                id: -1,
                member_id: props.member.id,
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
                member_id: props.member.id,
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

onMounted(() => {
    editWindow.shown = !!props.teamToMemberDialogShown;
    editWindow.teamList.editedProjectTeamMemberIndex = props.teamIndex;

    if (props.teamIndex && props.teamIndex >= 0) {
        Object.assign(
            editWindow.teamList.editedProjectTeamMember,
            props.member.project_teams[props.teamIndex]
        );
        editWindow.teamList.editProjectTeamMemberWindow.shown = true;
    } else {
        editWindow.teamList.editedProjectTeamMember = {
            project_team_member: {
                id: -1,
                member_id: props.member.id,
                project_team_id: -1,
                member_role_id: -1,
                admin_comments: "",
                member_role_title: "",
                member_role: {
                    id: -1,
                    title: "",
                    description: "",
                    reference: "",
                },
            },
            name: "",
            id: -1,
            email: "",
            description: "",
            comments: "",
            reference: "",
        };
        if (
            props.allProjectTeams &&
            props.allProjectTeams[props.allProjectTeams.length - 1].id != -1
        ) {
            props.allProjectTeams.push({
                name: "bitte wählen",
                id: -1,
                props: { disabled: true },
            });
        }
        if (
            props.memberRoles &&
            props.memberRoles[props.memberRoles.length - 1].id != -1
        ) {
            props.memberRoles.push({
                title: "bitte wählen",
                id: -1,
                props: { disabled: true },
            });
        }
    }
});

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

const today = computed(() => {
    return new Date().toISOString().substring(0, 10);
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

function showProjectTeamMemberItem(item, readonly) {
    const teamIndex = props.member.project_teams.indexOf(item);
    router.get(
        route("member.showWithDialog", {
            member: props.member.id,
            readonly,
            teamIndex,
        })
    );

    // editWindow.teamList.editedProjectTeamMemberIndex =
    //     props.member.project_teams.indexOf(item);
    // editWindow.teamList.editProjectTeamMemberWindow.loading = true;

    // editWindow.teamList.editedProjectTeamMember = Object.assign(
    //     props.member.project_teams[
    //         editWindow.teamList.editedProjectTeamMemberIndex
    //     ],
    //     item
    // );

    // editWindow.teamList.editProjectTeamMemberWindow.shown = true;
}

function viewProjectTeamMemberItem(ev, { item }) {
    showProjectTeamMemberItem(item, true);
}

function editProjectTeamMemberItem(item) {
    showProjectTeamMemberItem(item, false);
}

function addTeamToMember() {
    showProjectTeamMemberItem({}, false);
}

function deleteProjectTeamMemberItem(item) {
    // var index = editedItem.project_teams.indexOf(item);
    // if (confirm("Are you sure you want to delete this item?")) {
    //     var projectTeamMember =
    //         editedItem.project_teams[index].project_team_member.id;
    //     $http
    //         .delete(
    //             "/api/project-team-member/" +
    //                 projectTeamMember +
    //                 "?token=" +
    //                 sessionStorage.getItem("token")
    //         )
    //         .then(function () {
    //             var tmpEditedItem = editedItem;
    //             tmpEditedItem.project_teams.splice(index, 1);
    //             editedItem = Object.assign({}, tmpEditedItem);
    //             showAlert("success", "Gelöscht");
    //         })
    //         .catch(function (error) {
    //             handleRequestError(error);
    //         });
    // }
}

function saveEditProjectTeamMemberWindow() {
    console.log("saveEditProjectTeamMemberWindow");

    // var tm = editWindow.teamList.editedProjectTeamMember;
    // var tmx = editWindow.teamList.editedProjectTeamMemberIndex;
    // editWindow.teamList.editProjectTeamMemberWindow.saveInProgress = true;
    // if (tmx > -1) {
    //     var projectTeamMemberId = tm.project_team_member.id;
    //     $http
    //         .put(
    //             "/api/project-team-member/" +
    //                 projectTeamMemberId +
    //                 "?token=" +
    //                 sessionStorage.getItem("token"),
    //             tm.project_team_member
    //         )
    //         .then(function (response) {
    //             Object.assign(
    //                 projectTeams[tmx].project_team_member,
    //                 response.data
    //             );
    //             closeEditProjectTeamMemberWindow();
    //         })
    //         .catch(function (error) {
    //             handleRequestError(
    //                 error,
    //                 editWindow.teamList.editProjectTeamMemberWindow
    //             );
    //         });
    // } else {
    //     tm.project_team_member.member_id = editedItem.id;
    //     $http
    //         .post(
    //             "/api/project-team-member?token=" +
    //                 sessionStorage.getItem("token"),
    //             tm.project_team_member
    //         )
    //         .then(function (response) {
    //             var projectTeamNewId = response.data.project_team_id;
    //             var projectTeamNew = allProjectTeams.find(
    //                 (projectTeam) => projectTeam.id === projectTeamNewId
    //             );
    //             projectTeamNew.project_team_member = response.data;
    //             projectTeams.push(projectTeamNew);
    //             closeEditProjectTeamMemberWindow();
    //         })
    //         .catch(function (error) {
    //             handleRequestError(
    //                 error,
    //                 editWindow.teamList.editProjectTeamMemberWindow
    //             );
    //         });
    // }
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
    editedItem.last_name = editedItem.last_name.trim();
    editedItem.first_name = editedItem.first_name.trim();
    editedItem.name = editedItem.last_name + ", " + editedItem.first_name;
    console.log("editedItem", JSON.stringify(editedItem));
    if (editedItem.id == -1) {
        // router.post(route("member.store", editedItem));
        editedItem.post(route("member.store"));
    } else {
        // router.put(route("member.update", editedItem));
        editedItem.put(route("member.update"), { member: editedItem.id });
    }
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

function closeEditProjectTeamMemberWindow() {
    console.log("closeEditProjectTeamMemberWindow");
    editWindow.shown = false;
}

function lastContactEv() {
    editWindow.showLatestContactDatePicker = false;
    editedItem.latest_contact = fromDate(r.latest_contact_obj);
}
function lastEhkEv() {
    editWindow.showLatestFirstAidTrainingDatePicker = false;
    editedItem.latest_first_aid_training = fromDate(
        r.latest_first_aid_training_obj
    );
}
function nextEhkEv() {
    editWindow.showNextFirstAidTrainingDatePicker = false;
    editedItem.next_first_aid_training = fromDate(
        r.next_first_aid_training_obj
    );
}
function respToQuEv() {
    editWindow.showQuestResponseDatePicker = false;
    editedItem.responded_to_questionaire_at = fromDate(
        r.responded_to_questionaire_at_obj
    );
}
</script>
