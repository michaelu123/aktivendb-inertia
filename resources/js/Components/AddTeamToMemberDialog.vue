<template>
    <v-dialog v-model="editWindow.shown">
        <template v-slot:activator="{ props }">
            <v-btn
                color="primary"
                variant="outlined"
                class="mb-2"
                v-bind="props"
                v-if="!readonly"
            >
                <v-icon start>mdi-plus</v-icon> Mitglied zu AG Hinzufügen
            </v-btn>
        </template>
        <v-card>
            <v-card-title>
                <span class="text-h5">Mitgliedschaft</span>
            </v-card-title>

            <v-card-text>
                <v-container>
                    <v-form
                        ref="form"
                        v-model="
                            editWindow.teamList.editProjectTeamMemberWindow
                                .formValid
                        "
                        lazy-validation
                    >
                        <v-select
                            v-model="
                                editWindow.teamList.editedProjectTeamMember
                                    .project_team_member.member_role_id
                            "
                            :items="memberRoles"
                            item-title="title"
                            item-value="id"
                            label="Rolle"
                            required
                            :rules="[(v) => v != -1 || 'Bitte Rolle wählen']"
                            :readonly="readonlyT"
                            :error="
                                !!editWindow.teamList
                                    .editProjectTeamMemberWindow.errors
                                    .member_role_id
                            "
                            :error-messages="
                                editWindow.teamList.editProjectTeamMemberWindow
                                    .errors.member_role_id
                            "
                        ></v-select>
                        <v-text-field
                            v-model="editedItem.name"
                            label="Person"
                            readonly
                        ></v-text-field>
                        <v-select
                            v-if="editProjectTeamMemberNew"
                            v-model="
                                editWindow.teamList.editedProjectTeamMember
                                    .project_team_member.project_team_id
                            "
                            :items="allProjectTeams"
                            item-title="name"
                            item-value="id"
                            label="AG/Gruppe"
                            required
                            :rules="[
                                (v) => v != -1 || 'Bitte AG/Gruppe wählen',
                            ]"
                            :readonly="readonlyT"
                            :error="
                                !!editWindow.teamList
                                    .editProjectTeamMemberWindow.errors
                                    .project_team_id
                            "
                            :error-messages="
                                editWindow.teamList.editProjectTeamMemberWindow
                                    .errors.project_team_id
                            "
                        ></v-select>
                        <v-text-field
                            v-if="!editProjectTeamMemberNew"
                            v-model="
                                editWindow.teamList.editedProjectTeamMember.name
                            "
                            label="AG/Gruppe"
                            readonly
                        ></v-text-field>
                        <v-textarea
                            v-model="
                                editWindow.teamList.editedProjectTeamMember
                                    .project_team_member.admin_comments
                            "
                            label="Kommentar"
                            rows="3"
                            auto-grow
                            :readonly="readonlyT"
                            :error="
                                !!editWindow.teamList
                                    .editProjectTeamMemberWindow.errors
                                    .admin_comments
                            "
                            :error-messages="
                                editWindow.teamList.editProjectTeamMemberWindow
                                    .errors.admin_comments
                            "
                        ></v-textarea>
                    </v-form>
                </v-container>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn variant="text" @click="closeTM">Abbrechen</v-btn>
                <v-btn
                    variant="text"
                    @click="saveTM"
                    :loading="
                        editWindow.teamList.editProjectTeamMemberWindow
                            .saveInProgress
                    "
                    v-if="!readonlyT"
                    :disabled="invalidForm"
                    >Speichern</v-btn
                >
            </v-card-actions>
        </v-card>
    </v-dialog>
    <p>props3 {{ props }}</p>
    <p>editProjectTeamMemberNew {{ editProjectTeamMemberNew }}</p>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import { route } from "ziggy";

const props = defineProps([
    "editWindow",
    "editedItem",
    "memberRoles",
    "allProjectTeams",
    "readonly",
    "readonlyT",
]);

// onMounted(() => console.log("atmd", JSON.stringify(props)));

const editProjectTeamMemberNew = computed(
    () => props.editWindow.teamList.editedProjectTeamMemberIndex == -1
);

const invalidForm = computed(
    () =>
        props.editWindow.teamList.editedProjectTeamMember.project_team_member
            .member_role_id == -1 ||
        props.editWindow.teamList.editedProjectTeamMember.project_team_member
            .project_team_id == -1
);

function saveTM() {}
function closeTM() {
    router.get(
        route("member.show", {
            member: props.editedItem.id,
            readonly: props.readonly,
        })
    );
}
</script>
