<template>
    <v-dialog v-model="editWindow.shown">
        <v-card>
            <v-card-title>
                <span class="text-h5">Mitgliedschaft</span>
            </v-card-title>
            <v-card-text>
                <v-container>
                    <v-form
                        ref="form"
                        v-model="editWindow.formValid"
                        lazy-validation
                    >
                        <v-select
                            v-model="mtForm.member_role_id"
                            :items="memberRoles"
                            item-title="title"
                            item-value="id"
                            label="Rolle"
                            required
                            :rules="[(v) => v != -1 || 'Bitte Rolle wählen']"
                            :readonly="readonly"
                            :error="!!mtForm.member_role_id"
                            :error-messages="mtForm.errors.member_role_id"
                        ></v-select>
                        <v-text-field
                            v-model="editedItem.name"
                            label="Person"
                            readonly
                        ></v-text-field>

                        <v-select
                            v-if="mtForm.id == -1"
                            v-model="mtForm.team_id"
                            :items="allTeams"
                            item-title="name"
                            item-value="id"
                            label="AG/Gruppe"
                            required
                            :rules="[
                                (v) => v != -1 || 'Bitte AG/Gruppe wählen',
                            ]"
                            :readonly="readonly"
                            :error="!!mtForm.errors.team_id"
                            :error-messages="mtForm.errors.team_id"
                        ></v-select>
                        <v-text-field
                            v-if="mtForm.id != -1"
                            v-model="editWindow.teamList.editedTeamMember.name"
                            label="AG/Gruppe"
                            readonly
                        ></v-text-field>
                        <v-textarea
                            v-model="mtForm.admin_comments"
                            label="Kommentar"
                            rows="3"
                            auto-grow
                            :readonly="readonly"
                            :error="!!mtForm.errors.admin_comments"
                            :error-messages="mtForm.errors.admin_comments"
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
                    :loading="editWindow.saveInProgress"
                    v-if="!readonly"
                    :disabled="invalidForm"
                    >Speichern</v-btn
                >
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { computed, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy";

const props = defineProps([
    "editWindow",
    "editedItem",
    "memberRoles",
    "allTeams",
    "store",
]);
const page = usePage();
const readonly = computed(() => page.props.storeC.readonly2);

const mtForm = useForm({
    id: -1,
    member_role_id: -1,
    team_id: -1,
    member_id: -1,
    admin_comments: "",
});

watch(props.editWindow.teamList, () => {
    mtForm.member_role_id =
        props.editWindow.teamList.editedTeamMember.team_member.member_role_id ??
        -1;
    mtForm.member_id =
        props.editWindow.teamList.editedTeamMember.team_member.member_id ?? -1;
    mtForm.team_id =
        props.editWindow.teamList.editedTeamMember.team_member
            .project_team_id ?? -1; // ??
    mtForm.admin_comments =
        props.editWindow.teamList.editedTeamMember.team_member.admin_comments ??
        "";
    mtForm.id = props.editWindow.teamList.editedTeamMember.team_member.id;
    // console.log("watchTM", JSON.stringify(mtForm));
});

const invalidForm = computed(
    () => mtForm.member_role_id == -1 || mtForm.team_id == -1
);

function saveTM() {
    // console.log("saveTM", JSON.stringify(mtForm));
    if (mtForm.id == -1) {
        mtForm.post(route("member.storetm"));
    } else {
        mtForm.put(route("member.updatetm"));
    }
}

function closeTM() {
    router.get(
        route("member.show", {
            member: props.editedItem.id,
        })
    );
}
</script>
