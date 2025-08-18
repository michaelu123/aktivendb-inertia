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
            :items="members"
            :search="r.search"
            v-model:page="r.pageno"
            @click:row="viewItem"
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
                <v-row class="ml-2">
                    <v-btn
                        color="primary"
                        variant="outlined"
                        class="my-2 mr-4"
                        v-bind="props"
                        v-if="isAdmin()"
                        @click.prevent="addMember"
                    >
                        <v-icon start>mdi-plus</v-icon> Mitglied Hinzufügen
                    </v-btn>
                    <v-switch
                        v-model="r.activeSwitch"
                        label="Nur Aktive"
                        class="mr-4"
                        color="green"
                        base-color="red"
                    >
                    </v-switch>
                    <v-switch
                        v-if="!isAdmin()"
                        v-model="r.agSwitch"
                        color="green"
                        base-color="red"
                        label="Nur AG/OG-Mitglieder"
                    >
                    </v-switch>
                </v-row>
                <v-spacer></v-spacer>

                <v-sheet color="grey-lighten-3" align="center">
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
                                    <v-list-item
                                        key="1"
                                        @click="prefer('ADFC')"
                                    >
                                        <v-list-item-title
                                            >ADFC-Email-Adresse</v-list-item-title
                                        >
                                    </v-list-item>
                                    <v-list-item
                                        key="2"
                                        @click="prefer('Privat')"
                                    >
                                        <v-list-item-title
                                            >Private
                                            Email-Adresse</v-list-item-title
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
                                <v-icon start>mdi-file-excel</v-icon>
                                Jetzt exportieren
                            </v-btn>
                        </v-row>
                    </v-container>
                </v-sheet>

                <v-text-field
                    v-model="r.search"
                    label="Suchen"
                    append-icon="mdi-magnify"
                    single-line
                    hide-details
                    class="my-2"
                ></v-text-field>
            </template>
            <template v-slot:item.action="{ item }">
                <v-icon
                    size="15px"
                    @click.stop="editItem(item)"
                    v-if="item.with_details"
                    >mdi-pencil</v-icon
                >
                <v-icon
                    size="15px"
                    @click.stop="deleteItem(item)"
                    v-if="isAdmin()"
                    >mdi-delete</v-icon
                >
                <v-icon
                    size="15px"
                    @click.stop="historyItem(item)"
                    v-if="mayReadHistory()"
                    >mdi-history</v-icon
                >
            </template>
            <template v-slot:item.active="{ item }">
                <CircleTF :value="item.active" />
            </template>
            <template v-slot:item.responded_to_questionaire="{ item }">
                <CircleTF :value="item.responded_to_questionaire" />
            </template>
            <template v-slot:item.dsgvo_signature="{ item }">
                <Circle012 :value="item.dsgvo_signature" />
            </template>
            <template v-slot:item.police_certificate="{ item }">
                <Circle012 :value="item.police_certificate" />
            </template>
        </v-data-table>
    </v-card>
</template>

<script setup>
import HistoryDialog from "@/Components/HistoryDialog.vue";
import Circle012 from "@/Components/Circle012.vue";
import CircleTF from "@/Components/CircleTF.vue";
import { makeSchema } from "@/utils";
import { reactive } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { route } from "ziggy";
import writeXlsxFile from "write-excel-file";

const props = defineProps({
    members: Array,
    teams: Array,
    users: Array,
    history: Array,
    storeC: Object,
    storeS: Object,
    retour: String,
});

const headers = [
    {
        title: "Actions",
        key: "action",
        sortable: false,
    },
    {
        title: "Aktiv",
        key: "active",
        filter: (value, _se, item) => {
            // could not get v-data-table.custom-filter to work
            if (r.agSwitch && !item.raw.with_details) return false;

            if (!r.activeSwitch) return true;

            return !!value;
        },
    },
    /*
        {
          text: "Name",
          value: "name",
        },
        */
    {
        title: "Nachname",
        key: "last_name",
    },
    {
        title: "Vorname",
        key: "first_name",
    },
    {
        title: "Geburtsjahr",
        key: "birthday",
    },
    // {
    //   title: "Geschlecht",
    //   key: "gender"
    // },
    {
        title: "E-Mail (Privat)",
        key: "email_private",
    },
    {
        title: "E-Mail (ADFC)",
        key: "email_adfc",
    },
    {
        title: "Telefon",
        key: "phone_primary",
    },
    // {
    //   title: "Letzter Kontakt",
    //   key: "latest_contact"
    // },
    // {
    //   title: "Status",
    //   key: "status"
    // },
    {
        title: "Letzte 1. Hilfe Schulung",
        key: "latest_first_aid_training",
    },
    {
        title: "Nächste 1. Hilfe Schulung",
        key: "next_first_aid_training",
    },
    {
        title: "Fragebogen ausgefüllt",
        key: "responded_to_questionaire",
    },
    {
        title: "Datum Fragebogen",
        key: "responded_to_questionaire_at",
    },
    {
        title: "DSGVO Unterschrift",
        key: "dsgvo_signature",
        // value: (item) => toAbgegeben(item.dsgvo_signature),
    },
    {
        title: "Erweitertes Führungszeugnis",
        key: "police_certificate",
        // value: (item) => toAbgegeben(item.police_certificate),
    },
    {
        title: "Datum Führungszeugnis",
        key: "polcert_date",
    },
];

const r = reactive({
    search: "",
    activeSwitch: true,
    agSwitch: true,
    excelFileName: "",
    preferredEmail: "Bevorzugte Email-Adresse",
    pageno: props.storeC.pageno ?? 1,
});

const alert = reactive({
    shown: false,
    text: "",
    type: "success",
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
        route("member.show", { member: item.id, readonly, pageno: r.pageno })
    );
}

function viewItem(ev, { item }) {
    showItem(item, true);
}

function editItem(item) {
    showItem(item, false);
}

function deleteItem(item) {
    if (confirm("Mitglied wirklich löschen?")) {
        router.delete(route("member.destroy", { member: item.id }));
    }
}

function addMember() {
    router.get(route("member.create"));
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

    let myMembers = [];
    for (let m of props.members) {
        if (!m.with_details) continue;
        try {
            m.ags = await getMemberTeamsFromApi(m.id);
            m.agAll = m.ags.join(",");
            myMembers.push(m);
        } catch (ex) {
            console.log("ex!!", ex);
        }
    }
    const schema = makeSchema(myMembers, r.preferredEmail);

    if (r.activeSwitch) {
        myMembers = myMembers.filter((m) => m.active == "1");
    }

    await writeXlsxFile(myMembers, {
        schema,
        fileName: r.excelFileName,
    });
    r.excelFileName = "";
}

function prefer(t) {
    r.preferredEmail = "Bevorzugt: " + t;
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

async function getMemberTeamsFromApi(id) {
    const resp = await fetch("/member/" + id + "/teams");
    const res = await resp.json();
    console.log("res", res);
    return res.teams;
}

function historyItem(item) {
    console.log("historyM", item.id);
    const hform = useForm({
        begin: "",
        end: "",
        id: item.id,
        m_or_t: "m",
    });
    hform.post(
        route("member.indexWithHistory", { member: item.id, pageno: r.pageno })
    );
}
</script>
