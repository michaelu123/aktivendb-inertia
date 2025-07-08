<template>
    <v-dialog v-model="editWindow.shown">
        <v-card>
            <v-card-title>
                <span class="text-h5">Mitgliedschaft</span>
            </v-card-title>
            <p>sname {{ r.sname }}</p>
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
                            label="AG/Gruppe"
                            readonly
                        ></v-text-field>
                        <v-select
                            @keypress="keypr"
                            @focus="focus"
                            v-if="editTeamMemberNew"
                            v-model="mtForm.member_id"
                            :items="selMembers"
                            item-title="name"
                            item-value="id"
                            label="Person"
                            required
                            :rules="[(v) => v != -1 || 'Bitte Person wählen']"
                            :readonly="readonly"
                            :error="!!mtForm.errors.member_id"
                            :error-messages="mtForm.errors.member_id"
                        ></v-select>
                        <v-text-field
                            v-if="!editTeamMemberNew"
                            v-model="
                                editWindow.memberList.editedTeamMember.name
                            "
                            label="Person"
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
import { computed, reactive, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy";

const props = defineProps([
    "editWindow",
    "editedItem",
    "memberRoles",
    "allMembers",
    "store",
]);

const r = reactive({
    sname: "",
});

const page = usePage();

const readonly = computed(() => page.props.storeC.readonly2);

const mtForm = useForm({
    id: -1,
    member_role_id: -1,
    team_id: -1,
    member_id: -1,
    admin_comments: "",
});

const selMembers = computed(() => {
    if (r.sname == "") return props.allMembers;
    return props.allMembers.filter((m) =>
        m.name.toLowerCase().includes(r.sname)
    );
});

const editTeamMemberNew = computed(
    () => props.editWindow.memberList.editedTeamMemberIndex == -1
);

function keypr(x) {
    r.sname += x.key.toLowerCase();
    console.log("<<<", r.sname, ">>>");
}

function focus(_) {
    r.sname = "";
}

const invalidForm = computed(
    () => mtForm.member_role_id == -1 || mtForm.member_id == -1
);

function saveTM() {
    console.log("saveTM", mtForm);
    if (mtForm.id == -1) {
        mtForm.post(route("team.storetm"));
    } else {
        mtForm.put(route("team.updatetm"));
    }
}

function closeTM() {
    console.log("closeTM");
    router.get(
        route("team.show", {
            team: props.editedItem.id,
        })
    );
}
</script>

<!-- script>
/*
export default {
    name: "AddMemberToTeamDialog",
    props: [
        "editWindow",
        "editedItem",
        "memberRoles",
        "allMembers",
        "strictReadonly",
    ],
    computed: {
        editTeamMemberNew() {
            return (
                this.editWindow.memberList.editedTeamMemberIndex == -1
            );
        },
        invalidForm() {
            return (
                this.editWindow.memberList.editedTeamMember
                    .project_team_member.member_role_id == -1 ||
                this.editWindow.memberList.editedTeamMember
                    .project_team_member.member_id == -1
            );
        },
        selMembers() {
            if (this.sname == "") return this.allMembers;
            return this.allMembers.filter((m) =>
                m.name.toLowerCase().includes(this.sname)
            );
        },
    },
    data() {
        return {
            sname: "",
        };
    },
    methods: {
        saveTM() {
            this.$emit("saveTM"); // saveEditTeamMemberWindow
        },
        closeTM() {
            this.$emit("closeTM"); // closeEditTeamMemberWindow
        },
        keypr(x) {
            this.sname += x.key.toLowerCase();
        },
        // eslint-disable-next-line no-unused-vars
        focus(_) {
            this.sname = "";
        },
    },
};
*/
</script -->
