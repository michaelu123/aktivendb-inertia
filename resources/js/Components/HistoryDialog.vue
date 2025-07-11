<template>
    <v-dialog v-model="r.historyShown">
        <v-card>
            <v-card-title>
                History
                <v-spacer></v-spacer>
                <v-btn @click.prevent="r.alsText = !r.alsText">{{
                    r.alsText ? "Als Tabelle" : "Als Text"
                }}</v-btn>
            </v-card-title>
            <p>retour2 {{ props.retour }}</p>
            <v-data-table
                v-if="!r.alsText"
                v-model:expanded="r.expanded"
                :headers="headers"
                :items="r.historyArray"
                :search="r.search"
                item-value="key"
            >
                <template v-slot:top>
                    <v-text-field
                        v-model="r.search"
                        label="Suchen"
                        append-icon="mdi-magnify"
                        single-line
                        hide-details
                        class="ml-2"
                    ></v-text-field>
                </template>
                <template v-slot:expanded-row="{ columns, item }">
                    <tr>
                        <td :colspan="columns.length">
                            <table>
                                <tr>
                                    <th>Feld</th>
                                    <th>Alter Wert</th>
                                    <th>Neuer Wert</th>
                                </tr>
                                <tr
                                    v-for="change in item.changes"
                                    :key="change.lineNo"
                                >
                                    <td>{{ change.propName }}</td>
                                    <td>{{ change.propOld }}</td>
                                    <td>{{ change.propNew }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </template>
                <!--
        https://stackoverflow.com/questions/77784334/is-there-way-to-hide-the-expand-button-in-v-data-table-with-a-condition
        -->
                <template
                    #item.data-table-expand="{
                        item,
                        internalItem,
                        toggleExpand,
                        isExpanded,
                    }"
                >
                    <v-btn
                        height="30"
                        variant="text"
                        density="comfortable"
                        v-if="item.changes"
                        @click="toggleExpand(internalItem)"
                        class="v-data-table__expand-icon"
                        :class="{
                            'v-data-table__expand-icon--active': isExpanded,
                        }"
                    >
                        <v-icon v-if="isExpanded(internalItem)" size="small"
                            >mdi-chevron-down</v-icon
                        >
                        <v-icon v-if="!isExpanded(internalItem)" size="small"
                            >mdi-chevron-up</v-icon
                        >
                    </v-btn>
                </template>
            </v-data-table>

            <v-card-text v-if="r.alsText">
                <div v-for="line in r.historyTxt" :key="line.lineNo">
                    <p class="mb-0" v-if="line.indent == 0">{{ line.msg }}</p>
                    <p class="mb-0 ml-10" v-if="line.indent != 0">
                        {{ line.msg }}
                    </p>
                </div>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn variant="text" @click="close">Schließen</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style>
table {
    table-layout: auto;
    width: 100%;
}
th {
    text-align: left;
}
</style>

<script setup>
import { onMounted, reactive, watch } from "vue";
import { router } from "@inertiajs/vue3";

import { parse } from "@/utils.js";

let emails = {};
let memberNames = {};
let teamNames = {
    // deleted records not fetched from API call
    22: "AG Radfahrschule",
    29: "AG Leitungen",
    30: "Test AG",
    31: "Test",
    32: "Test",
    33: "Test ML",
    34: "AG Tagestouren",
    36: "test",
    37: "Test",
    38: "Test",
    44: "Test",
    63: "TEST2",
    64: "OG Ismaning Test",
};
let lineNo = 0;

const props = defineProps({
    members: Array,
    teams: Array,
    users: Array,
    history: Array,
    retour: String,
});

const r = reactive({
    alsText: false,
    historyTxt: [],
    historyArray: [],
    search: "",
    expanded: [],
    historyShown: false,
});

const headers = [
    {
        title: "Wann",
        key: "when",
        sortable: true,
        filterable: true,
        // width: "100px",
    },
    {
        title: "Wer",
        key: "who",
        sortable: true,
        filterable: true,
    },
    {
        title: "Was",
        key: "what",
        sortable: true,
        filterable: true,
    },
    {
        title: "Mitglied",
        key: "whom",
        sortable: true,
        filterable: true,
    },
    {
        title: "Gliederung",
        key: "where",
        sortable: true,
        filterable: true,
    },
    {
        title: "Änderungen",
        key: "data-table-expand",
    },
];

let historyTxt = [];
let historyArray = [];

function teamName(id) {
    let tn = teamNames[id];
    if (!tn) tn = "TeamId " + id;
    return tn;
}

function memberName(id) {
    let mn = memberNames[id];
    if (!mn) mn = "MemberId " + id;
    return mn;
}

watch(props, xxx);
onMounted(xxx);

function xxx() {
    console.log("1watch");
    for (const t of props.teams) {
        teamNames[t.id.toString()] = t.name;
    }
    for (const m of props.members) {
        memberNames[m.id.toString()] = m.name;
    }

    for (let user of props.users) {
        emails[user.id] = user.email;
    }

    lineNo = 0;
    historyTxt = [];
    historyArray = [];
    for (const event of props.history) {
        let email = emails[event.user_id] ?? "??";
        explainHistory(event, email);
        lineNo++;
    }
    r.historyTxt = historyTxt;
    r.historyArray = historyArray;
    r.historyShown = true;
    console.log("2watch");
}

function explainHistory(event, email) {
    event = parse(event);
    if (event.reference_table == "members") {
        explainMember(event, email);
    } else if (event.reference_table == "project_team_member") {
        explainTeamMember(event, email);
    } else if (event.reference_table == "project_teams") {
        explainTeam(event, email);
    } else {
        console.log("reference_table???", event.reference_table);
    }
}

function explainMember(member, email) {
    let upd = [];
    let historyObj = {};
    let msg = email;
    historyObj["who"] = email.replace("@adfc-muenchen.de", "");
    historyObj["key"] = lineNo;
    let name = member.record_new["name"];
    if (name == null) name = "";
    name = name.trim();
    if (name == "") {
        name =
            member.record_new.last_name + ", " + member.record_new.first_name;
        name = name.trim();
    }
    historyObj["whom"] = name;
    if (Array.isArray(member.record_old)) {
        msg = member.record_new.created_at + " " + msg;
        msg += " adds " + name;
        historyObj["what"] = "fügt hinzu";
        historyObj["when"] = member.record_new.created_at;
    } else if (
        member.record_old.deleted_at != null &&
        member.record_new.deleted_at != null
    ) {
        msg = member.record_new.deleted_at + " " + msg;
        msg += " deletes " + name;
        let changesArray = [];
        historyObj["changes"] = changesArray;
        historyObj["what"] = "löscht";
        historyObj["when"] = member.record_new.deleted_at;
        for (const propName in member.record_old) {
            if (propName.endsWith("_at")) continue;
            if (propName == "id") continue;
            let propOld = member.record_old[propName];
            upd.push(propName + ":" + propOld);
            changesArray.push({
                propName,
                propNew: "",
                propOld,
                lineNo,
            });
            lineNo++;
        }
    } else {
        let changesArray = [];
        for (const propName in member.record_new) {
            if (propName.endsWith("_at")) continue; //normally only different TZ
            let propNew = member.record_new[propName];
            let propOld = member.record_old[propName];
            if (propNew != propOld) {
                upd.push(propName + ":" + propOld + "=>" + propNew);
                changesArray.push({
                    propName,
                    propOld,
                    propNew,
                    lineNo,
                });
                lineNo++;
            }
        }
        if (upd.length == 0) return;
        historyObj["what"] = "ändert";
        historyObj["changes"] = changesArray;
        msg =
            member.record_new.updated_at +
            " " +
            msg +
            " updates " +
            name +
            ": ";
        historyObj["when"] = member.record_new.updated_at;
    }
    historyTxt.push({ indent: 0, msg, lineNo });
    for (let u of upd) {
        lineNo++;
        historyTxt.push({ indent: 1, msg: u, lineNo });
    }
    historyArray.push(historyObj);
}

function explainTeamMember(tm, email) {
    const projTeamId = tm.record_new.project_team_id;
    if (!projTeamId || +projTeamId <= 0) return;
    const team_name = teamName(projTeamId);
    if (team_name.toLowerCase().startsWith("test")) return;
    const member_name = memberName(tm.record_new.member_id);
    let upd = [];
    let historyObj = {};
    let msg = email;
    historyObj["who"] = email;
    historyObj["whom"] = member_name;
    historyObj["where"] = team_name;
    historyObj["key"] = lineNo;
    if (Array.isArray(tm.record_old)) {
        msg = tm.record_new.created_at + " " + msg;
        msg +=
            " adds " +
            member_name +
            " to " +
            team_name +
            " with role " +
            tm.record_new.member_role_id;
        historyObj["what"] = "fügt hinzu";
        historyObj["with"] = "role " + tm.record_new.member_role_id;
        historyObj["when"] = tm.record_new.created_at;
    } else if (
        tm.record_old.deleted_at != null &&
        tm.record_new.deleted_at != null
    ) {
        msg = tm.record_new.deleted_at + " " + msg;
        msg += " deletes " + member_name + " from " + team_name;
        historyObj["what"] = "löscht";
        historyObj["when"] = tm.record_new.deleted_at;
    } else {
        let changesArray = [];
        for (const propName in tm.record_new) {
            if (propName.endsWith("_at")) continue;
            let propNew = tm.record_new[propName];
            let propOld = tm.record_old[propName];
            if (propNew != propOld) {
                upd.push(propName + ":" + propOld + "=>" + propNew);
                changesArray.push({
                    propName,
                    propOld,
                    propNew,
                    lineNo,
                });
                lineNo++;
            }
        }
        if (upd.length == 0) return;
        historyObj["what"] = "ändert";
        historyObj["changes"] = changesArray;
        msg =
            tm.record_new.updated_at +
            " " +
            msg +
            " updates " +
            member_name +
            " for " +
            team_name +
            ": ";
        historyObj["when"] = tm.record_new.updated_at;
    }
    r.historyTxt.push({ indent: 0, msg, lineNo });
    for (let u of upd) {
        lineNo++;
        r.historyTxt.push({ indent: 1, msg: u, lineNo });
    }
    r.historyArray.push(historyObj);
}

function explainTeam(team, email) {
    let upd = [];
    let historyObj = {};
    let msg = email;
    let team_name = team.record_new.name;
    historyObj["who"] = email;
    historyObj["whom"] = team_name;
    historyObj["key"] = lineNo;
    if (Array.isArray(team.record_old)) {
        msg = team.record_new.created_at + " " + msg;
        msg += " adds " + team_name;
        historyObj["what"] = "fügt hinzu";
        historyObj["when"] = team.record_new.created_at;
    } else if (
        team.record_old.deleted_at != null &&
        team.record_new.deleted_at != null
    ) {
        msg = team.record_new.deleted_at + " " + msg;
        msg += " deletes " + team_name;
        historyObj["what"] = "löscht";
        historyObj["when"] = team.record_new.deleted_at;
    } else {
        let changesArray = [];
        for (const propName in team.record_new) {
            if (propName.endsWith("_at")) continue;
            let propNew = team.record_new[propName];
            let propOld = team.record_old[propName];
            if (propNew != propOld) {
                upd.push(propName + ":" + propOld + "=>" + propNew);
                changesArray.push({
                    propName,
                    propOld,
                    propNew,
                    lineNo,
                });
                lineNo++;
            }
        }
        if (upd.length == 0) return;
        historyObj["what"] = "ändert";
        historyObj["changes"] = changesArray;
        msg = team.record_new.updated_at + " " + msg;
        msg += " updates " + team_name + ": ";
        historyObj["when"] = team.record_new.updated_at;
    }
    historyTxt.push({ indent: 0, msg, lineNo });
    for (let u of upd) {
        lineNo++;
        historyTxt.push({ indent: 1, msg: u, lineNo });
    }
    historyArray.push(historyObj);
}

function close() {
    r.historyShown = false;
    router.get(route(props.retour ?? "history.show"));
}
</script>
