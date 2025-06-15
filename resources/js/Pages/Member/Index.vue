<template>
    <v-data-table :headers="headers" :items="members"> </v-data-table>
</template>

<script setup>
import { toAbgegeben } from "@/utils";

defineProps({ members: Array });
let activeSwitch = false;
let agSwitch = false;

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
            if (agSwitch && !item.raw.with_details) return false;

            if (!activeSwitch) return true;

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
        key: "phone",
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
        title: "Registriert für Schulung",
        key: "registered_for_first_aid_training",
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
        value: (item) => toAbgegeben(item.dsgvo_signature),
    },
    {
        title: "Poliz. Führungszeugnis",
        key: "police_certificate",
        value: (item) => toAbgegeben(item.police_certificate),
    },
];
</script>
