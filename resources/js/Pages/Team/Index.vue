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
            @click:row="viewItem"
            v-model:page="r.pageNo"
            v-model:items-per-page="r.itemsPerPage"
        >
            <template v-slot:top>
                <HistoryDialog
                    v-if="history && history.length > 0"
                    :teams="teams"
                    :members="members"
                    :users="users"
                    :history="history"
                    :retour="retour"
                />
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
                    v-if="!isAdmin()"
                    v-model="r.myAgSwitch"
                    color="green"
                    base-color="red"
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
import HistoryDialog from "@/Components/HistoryDialog.vue";
import { computed, reactive, onMounted } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy";
import writeXlsxFile from "write-excel-file";

const props = defineProps({
    members: Array,
    teams: Array,
    users: Array,
    history: Array,
    retour: String,
});

const headers = [
    {
        title: "Actions",
        key: "action",
        sortable: false,
    },
    {
        title: "Name",
        key: "name",
    },
    {
        title: "E-Mail",
        key: "email",
    },
];

const r = reactive({
    search: "",
    activeSwitch: true,
    myAgSwitch: true,
    excelFileName: "",
    pageNo: 1,
    itemsPerPage: 10,
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

onMounted(() => {
    const search = localStorage.getItem("searchT");
    if (search) r.search = search;
    const pageNo = localStorage.getItem("pageNoT");
    if (pageNo) r.pageNo = pageNo;
    const itemsPerPage = localStorage.getItem("itemsPerPageT");
    if (itemsPerPage) r.itemsPerPage = itemsPerPage;
});

function isAdmin() {
    return !!page.props.user.is_admin;
}

function mayReadHistory() {
    return page.props.user.may_read_history;
}

function showItem(item, readonly) {
    localStorage.setItem("readonlyT", readonly);
    rememberIndex();
    router.get(route("team.show", { team: item.id }));
}

function viewItem(ev, { item }) {
    showItem(item, true);
}

function editItem(item) {
    showItem(item, false);
}

function deleteItem(item) {
    if (confirm("AG/OG wirklich löschen?")) {
        rememberIndex();
        router.delete(route("team.destroy", { team: item.id }));
    }
}

function addTeam() {
    rememberIndex();
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
    }, 5000);
}

async function getTeamMembersFromApi(id) {
    const resp = await fetch("/team/" + id + "/members");
    const res = await resp.json();
    console.log("res", res);
    return res.members;
}

function historyItem(item) {
    console.log("historyT", item.id);
    const hform = useForm({
        begin: "",
        end: "",
        id: item.id,
        m_or_t: "t",
    });
    rememberIndex();
    hform.post(route("team.indexWithHistory", { team: item.id }));
}

function rememberIndex() {
    localStorage.setItem("searchT", r.search);
    localStorage.setItem("pageNoT", r.pageNo);
    localStorage.setItem("itemsPerPageT", r.itemsPerPage);
}
</script>
