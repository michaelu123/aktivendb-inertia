<template>
    <v-card>
        <v-card-title>
            <div>
                <span class="text-h5">AG/OG</span>
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

        <v-sheet color="grey-lighten-3" align="center" v-if="editedItem.id > 0">
            <v-container>
                <p class="text-caption">Als Excel-Datei exportieren:</p>
                <v-row class="mt-2">
                    <v-text-field
                        type="text"
                        variant="outlined"
                        color="primary"
                        label="Bitte Dateinamen eingeben"
                        v-model="r.excelFileName"
                    ></v-text-field>
                    <v-menu offset_y>
                        <template v-slot:activator="{ props }">
                            <v-btn
                                variant="outlined"
                                color="primary"
                                height="60"
                                class="mx-4"
                                v-bind="props"
                            >
                                {{ r.preferredEmail }}
                            </v-btn>
                        </template>
                        <v-list>
                            <v-list-item key="1" @click="prefer('ADFC')">
                                <v-list-item-title
                                    >ADFC-Email-Adresse</v-list-item-title
                                >
                            </v-list-item>
                            <v-list-item key="2" @click="prefer('Privat')">
                                <v-list-item-title
                                    >Private Email-Adresse</v-list-item-title
                                >
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    <v-btn
                        height="60"
                        color="primary"
                        type="submit"
                        variant="outlined"
                        @click.prevent="exportExcel"
                    >
                        <v-icon start>mdi-file-excel</v-icon> Jetzt exportieren
                    </v-btn>
                </v-row>
            </v-container>
        </v-sheet>

        <v-card-text>
            <v-container>
                <v-form
                    ref="form"
                    v-model="editWindow.formValid"
                    lazy-validation
                >
                    <v-text-field
                        v-model="editedItem.name"
                        label="Name"
                        required
                        :readonly="readonly"
                        :error="!!editedItem.errors.name"
                        :error-messages="editedItem.errors.name"
                    ></v-text-field>
                    <v-text-field
                        v-model="editedItem.email"
                        label="E-mail"
                        :readonly="readonly"
                        :error="!!editedItem.errors.email"
                        :error-messages="editedItem.errors.email"
                    ></v-text-field>
                    <v-textarea
                        v-model="editedItem.description"
                        label="Beschreibung"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editedItem.errors.description"
                        :error-messages="editedItem.errors.description"
                    ></v-textarea>
                    <v-textarea
                        v-model="editedItem.comments"
                        label="Kommentar"
                        rows="2"
                        auto-grow
                        :readonly="readonly"
                        :error="!!editedItem.errors.comments"
                        :error-messages="editedItem.errors.comments"
                    ></v-textarea>
                    <v-switch
                        v-model="editedItem.needs_first_aid_training"
                        color="green"
                        :base-color="
                            editedItem.needs_first_aid_training
                                ? 'green'
                                : 'red'
                        "
                        label="1. Hilfe Schulung"
                        :disabled="readonly"
                        :value-comparator="checkForTrue"
                        :error="!!editedItem.errors.needs_first_aid_training"
                        :error-messages="
                            editedItem.errors.needs_first_aid_training
                        "
                    ></v-switch>
                </v-form>
                <v-card-title>
                    <span class="text-h5">Liste der Mitglieder</span>
                </v-card-title>
                <v-row class="items-center mt-4">
                    <v-switch
                        class="ml-2"
                        v-model="r.activeSwitch"
                        label="Nur Aktive auflisten"
                        color="green"
                        base-color="red"
                    >
                    </v-switch>
                    <v-spacer />
                    <v-btn
                        color="primary"
                        variant="outlined"
                        class="mb-2"
                        v-bind="props"
                        v-if="!readonly && editedItem.id > 0"
                        @click.prevent="addMemberToTeam"
                    >
                        <v-icon start>mdi-plus</v-icon> Mitglied zu AG/OG
                        hinzufügen
                    </v-btn>
                </v-row>
                <template v-if="editedItem.id > 0">
                    <v-data-table
                        :headers="editWindow.memberList.headers"
                        :items="
                            r.activeSwitch
                                ? team.members.filter((m) => !!m.active)
                                : team.members
                        "
                        :search="r.searchEditWindow"
                        @click:row="viewTeamMemberItem"
                    >
                        <template v-slot:top>
                            <AddMemberToTeamDialog
                                v-if="memberToTeamDialogShown"
                                :editWindow="editWindow"
                                :editedItem="editedItem"
                                :memberRoles="memberRoles"
                                :allMembers="allMembers"
                            ></AddMemberToTeamDialog>

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
                                @click.stop="editTeamMemberItem(item)"
                                v-if="!readonly"
                            >
                                mdi-pencil
                            </v-icon>
                            <v-icon
                                size="small"
                                @click.stop="deleteTeamMemberItem(item)"
                                v-if="!readonly"
                            >
                                mdi-delete
                            </v-icon>
                        </template>
                    </v-data-table>
                </template>
            </v-container>
        </v-card-text>

        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn variant="text" @click="closeEW">Abbrechen</v-btn>
            <v-btn variant="text" @click="saveEW" v-if="!readonly"
                >Speichern</v-btn
            >
        </v-card-actions>
    </v-card>
</template>

<script setup>
import { computed, onMounted, reactive } from "vue";
import { usePage, router, useForm } from "@inertiajs/vue3";
import { checkForTrue, fromDate, toAbgegeben, makeSchema } from "@/utils";

import AddMemberToTeamDialog from "@/Components/AddMemberToTeamDialog.vue";
import { route } from "ziggy";

import writeXlsxFile from "write-excel-file";

const props = defineProps({
    team: Object,
    memberToTeamDialogShown: Boolean,
    memberIndex: -1,
    allMembers: Array,
    memberRoles: Array,
    storeC: Object,
    storeS: Object,
});

let readonly = computed(() => props.storeC.readonly1);

const editedItem = useForm({
    id: props.team.id ?? -1,
    name: props.team.name ?? "",
    email: props.team.email ?? "",
    needs_first_aid_training: props.team.needs_first_aid_training ?? false,
    description: props.team.description ?? "",
    comments: props.team.comments ?? "",
    members: props.team.members ?? [],
});

const alert = reactive({
    shown: false,
    text: "",
    type: "success",
});

const r = reactive({
    searchEditWindow: "",
    excelFileName: "",
    preferredEmail: "Bevorzugte Email-Adresse",
});

const editWindow = reactive({
    shown: false,
    formValid: true,
    errors: {},
    memberList: {
        headers: [
            {
                title: "Actions",
                key: "action",
                sortable: false,
            },
            {
                title: "Aktive(r)",
                key: "name",
            },
            {
                title: "Funktion",
                key: "team_member.member_role_title",
            },
        ],
        editedTeamMemberIndex: -1,
        editedTeamMember: {
            team_member: {
                admin_comments: "",
                id: -1,
                member_id: -1,
                member_role_id: -1,
                member_role_title: "",
                team_id: -1,
            },
            name: "",
        },
    },
});

const page = usePage();

onMounted(() => {
    editWindow.shown = !!props.memberToTeamDialogShown;
    editWindow.memberList.editedTeamMemberIndex = props.memberIndex;

    if (props.memberIndex && props.memberIndex >= 0) {
        Object.assign(
            editWindow.memberList.editedTeamMember,
            props.team.members[props.memberIndex]
        );
    } else {
        editWindow.memberList.editedTeamMember = {
            team_member: {
                id: -1,
                member_id: -1,
                team_id: props.team.id,
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
        };
        if (
            props.allMembers &&
            props.allMembers[props.allMembers.length - 1].id != -1
        ) {
            props.allMembers.unshift({
                name: "bitte wählen",
                id: -1,
                props: { disabled: true },
            });
        }
        if (
            props.memberRoles &&
            props.memberRoles[props.memberRoles.length - 1].id != -1
        ) {
            props.memberRoles.unshift({
                title: "bitte wählen",
                id: -1,
                props: { disabled: true },
            });
        }
    }
});

function showTeamMemberItem(item, readonly) {
    const memberIndex = props.team.members.indexOf(item);
    router.get(
        route("team.showWithDialog", {
            team: props.team.id,
            readonly,
            memberIndex,
        })
    );
}

function viewTeamMemberItem(ev, { item }) {
    showTeamMemberItem(item, true);
}

function editTeamMemberItem(item) {
    showTeamMemberItem(item, false);
}

function addMemberToTeam() {
    showTeamMemberItem({}, false);
}

function deleteTeamMemberItem(item) {
    if (confirm("Wirklich löschen?")) {
        router.delete(route("team.destroytm", { id: item.team_member.id }));
    }
}

function closeEW() {
    router.get(route("team.index"));
}

function saveEW() {
    editedItem.name = editedItem.name.trim();
    // console.log("saveEW", JSON.stringify(editedItem));
    if (editedItem.id == -1) {
        // router.post(route("member.store", editedItem));
        editedItem.post(route("team.store"));
    } else {
        // router.put(route("member.update", editedItem));
        editedItem.put(route("team.update", { team: editedItem.id }));
    }
}

function showAlert(type, text) {
    alert.shown = true;
    alert.type = type;
    alert.text = text;

    setTimeout(() => {
        alert.shown = false;
    }, 5000);
}

function isAdmin() {
    return !!page.props.user.is_admin;
}

async function exportExcel() {
    if (r.excelFileName == "") {
        showAlert("error", "Bitte Dateinamen eingeben");
        return;
    }
    if (!r.excelFileName.endsWith(".xlsx")) {
        r.excelFileName = r.excelFileName + ".xlsx";
    }
    if (r.preferredEmail == "Bevorzugte Email-Adresse") {
        showAlert("error", "Bitte Email-Präferenz eingeben");
        return;
    }

    const schema = makeSchema(me, null);

    await writeXlsxFile(members, {
        schema,
        fileName: r.excelFileName,
    });
    r.excelFileName = "";
}

function prefer(t) {
    r.preferredEmail = "Bevorzugt: " + t;
}
</script>
