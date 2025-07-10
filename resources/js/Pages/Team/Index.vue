<template>
    <v-card>
        <v-card-title>
            <div>
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
        <v-data-table
            :headers="headers"
            :items="selTeams"
            :search="r.search"
            :loading="r.loading"
            loading-text="Wird geladen..."
            @click:row="viewItem"
            v-model:page="r.pageno"
        >
            <template v-slot:top>
                <v-btn
                    color="primary"
                    variant="outlined"
                    class="my-2 mr-4"
                    v-bind="props"
                    v-if="isAdmin()"
                    @click.prevent="addTeam"
                >
                    <v-icon start>mdi-plus</v-icon> AG/OG Hinzufügen
                </v-btn>
                <v-switch
                    v-model="r.myAgSwitch"
                    label="Nur AGs/OGs deren Leiter ich bin"
                    class="mr-4"
                >
                </v-switch>

                <v-spacer></v-spacer>

                <v-sheet color="grey-lighten-3" align="center">
                    <v-container v-if="isAdmin()">
                        <p class="text-caption">Als Excel-Datei exportieren:</p>
                        <v-row class="mt-2">
                            <v-text-field
                                type="text"
                                variant="outlined"
                                color="primary"
                                label="Bitte Dateinamen eingeben"
                                v-model="r.excelFileName"
                            ></v-text-field>
                            <v-progress-circular
                                class="mr-4"
                                v-if="r.loadingMembers"
                                indeterminate
                                color="primary"
                            ></v-progress-circular>
                            <v-spacer></v-spacer>
                            <v-btn
                                height="60"
                                color="primary"
                                type="submit"
                                variant="outlined"
                                @click.prevent="exportExcel"
                            >
                                <v-icon start>mdi-file-excel</v-icon>
                                Jetzt exportieren
                            </v-btn>
                            <v-spacer></v-spacer>
                        </v-row>
                    </v-container>
                </v-sheet>

                <v-text-field
                    v-model="r.search"
                    label="Suchen"
                    append-icon="mdi-magnify"
                    single-line
                    hide-details
                    class=""
                ></v-text-field>
            </template>
            <template v-slot:item.action="{ item }">
                <v-icon
                    size="small"
                    class="mr-2"
                    @click.stop="editItem(item)"
                    v-if="item.with_details"
                    >mdi-pencil
                </v-icon>
                <v-icon
                    size="small"
                    class="mr-2"
                    @click.stop="deleteItem(item)"
                    v-if="isAdmin()"
                    >mdi-delete
                </v-icon>
                <v-icon
                    size="small"
                    @click.stop="historyItem(item)"
                    v-if="mayReadHistory()"
                    >mdi-history</v-icon
                >
            </template>
            <template v-slot:item.needs_first_aid_training="{ item }">
                <v-checkbox-btn
                    :value="checkForTrue(item.needs_first_aid_training)"
                    disabled
                >
                </v-checkbox-btn>
            </template>
        </v-data-table>
    </v-card>
</template>

<script setup>
import { computed, reactive } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy";
import writeXlsxFile from "write-excel-file";

const props = defineProps({ teams: Array, storeC: Object, storeS: Object });

const headers = [
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
        key: "project_team_member.member_role_title",
    },
];

const r = reactive({
    search: "",
    activeSwitch: true,
    myAgSwitch: true,
    loading: false,
    excelFileName: "",
    loadingMembers: false,
    pageno: props.storeC.pageno ?? 1,
});

const alert = reactive({
    shown: false,
    text: "",
    type: "success",
});

const selTeams = computed(() => {
    if (r.myAgSwitch) {
        return props.teams.filter((t) => t.with_details);
    }
    return props.teams;
});

const page = usePage();

function isAdmin() {
    return !!page.props.user.is_admin;
}

function mayReadHistory() {
    return page.props.user.may_read_history;
}

function showItem(item, readonly) {
    router.get(
        route("team.show", { team: item.id, readonly, pageno: r.pageno })
    );
}

function viewItem(ev, { item }) {
    showItem(item, true);
}

function editItem(item) {
    showItem(item, false);
}

function deleteItem(item) {
    if (confirm("AG/OG wirklich löschen?")) {
        router.delete(route("team.destroy", { team: item.id }));
    }
}

function addTeam() {
    router.get(route("team.create"));
}

async function exportExcel() {
    if (r.excelFileName == "") {
        showAlert("error", "Bitte Dateinamen eingeben");
        return;
    }
    if (!r.excelFileName.endsWith(".xlsx")) {
        r.excelFileName = r.excelFileName + ".xlsx";
    }

    let myTeams = [];
    try {
        r.loadingMembers = true;
        for (let t of props.teams) {
            if (!t.with_details) continue;
            try {
                t.members = await getTeamMembersFromApi(m.id);
                if (r.activeSwitch) {
                    t.members = t.members.filter((m) => m.active == "1");
                }
                t.leaders = t.members
                    .filter((m) => m.team_member.member_role_title == "Vorsitz")
                    .map((m) => m.first_name + " " + m.last_name)
                    .join(", ");
                myTeams.push(t);
            } catch (ex) {
                console.log("ex!!", ex);
            }
        }
    } finally {
        r.loadingMembers = false;
    }
    const schema = makeSchema();

    await writeXlsxFile(myTeams, {
        schema,
        fileName: r.excelFileName,
    });
    r.excelFileName = "";
}

function makeSchema() {
    return [
        {
            column: "Name",
            type: String,
            value: (team) => team.name,
            width: 30,
        },
        {
            column: "Email",
            type: String,
            value: (team) => team.email,
            width: 60,
        },
        {
            column: "EHK benötigt",
            type: Boolean,
            value: (team) => team.needs_first_aid_training == "1",
            width: 15,
        },
        {
            column: "Beschreibung",
            type: String,
            value: (team) => team.description,
            width: 60,
        },
        {
            column: "Kommentar",
            type: String,
            value: (team) => team.comments,
            width: 60,
        },
        {
            column: "Leiter*innen",
            type: String,
            value: (team) => team.leaders,
            width: 60,
        },
    ];
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

async function getTeamMembersFromApi(id) {
    const resp = await fetch("/team/" + id + "/members");
    const res = await resp.json();
    console.log("res", res);
    return res.members;
}
</script>
