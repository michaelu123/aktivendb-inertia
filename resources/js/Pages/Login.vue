<template>
    <v-container class="fill-height" fluid>
        <v-card class="mx-auto elevation-12" min-width="420px">
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
            <v-card-text>
                <v-form @submit.prevent="login(true)">
                    <div class="mb-4 text-center">
                        <h3 class="font-weight-regular">Aktivenverwaltung</h3>
                        <h6 class="mb-3 font-weight-regular">Log-in</h6>
                    </div>

                    <div class="alert alert-warning" v-if="r.infoError">
                        {{ error }}
                    </div>

                    <v-text-field
                        v-model="form.email"
                        label="E-Mail"
                        autocomplete="email"
                        required
                        :error-messages="form.errors.email"
                    ></v-text-field>
                    <v-text-field
                        v-if="!r.chgPasswd"
                        :append-icon="r.showPwd1 ? 'mdi-eye' : 'mdi-eye-off'"
                        :type="r.showPwd1 ? 'text' : 'password'"
                        @click:append="r.showPwd1 = !r.showPwd1"
                        autocomplete="current-password"
                        v-model="form.password"
                        label="Passwort"
                        required
                        :error-messages="form.errors.password"
                    ></v-text-field>
                    <v-text-field
                        v-if="r.chgPasswd"
                        :append-icon="r.showPwd2 ? 'mdi-eye' : 'mdi-eye-off'"
                        :type="r.showPwd2 ? 'text' : 'password'"
                        @click:append="r.showPwd2 = !r.showPwd2"
                        autocomplete="password"
                        v-model="form.password"
                        label="Altes Passwort"
                        required
                    ></v-text-field>
                    <v-text-field
                        v-if="r.chgPasswd"
                        :append-icon="r.showPwd3 ? 'mdi-eye' : 'mdi-eye-off'"
                        :type="r.showPwd3 ? 'text' : 'password'"
                        @click:append="r.showPwd3 = !r.showPwd3"
                        autocomplete="new-password"
                        v-model="r.newPasswd1"
                        label="Neues Passwort"
                        required
                    ></v-text-field>
                    <v-text-field
                        v-if="r.chgPasswd"
                        :append-icon="r.showPwd4 ? 'mdi-eye' : 'mdi-eye-off'"
                        :type="r.showPwd4 ? 'text' : 'password'"
                        @click:append="r.showPwd4 = !r.showPwd4"
                        autocomplete="new-password"
                        v-model="r.newPasswd2"
                        label="Neues Passwort wiederholen"
                        required
                    ></v-text-field>

                    <v-btn
                        class="my-5"
                        type="submit"
                        block
                        variant="outlined"
                        color="primary"
                        :loading="r.loading"
                        :disabled="r.loading"
                    >
                        Login
                    </v-btn>

                    <v-btn
                        class="my-5"
                        @click.prevent="r.chgPasswd = !r.chgPasswd"
                        type="submit"
                        block
                        variant="outlined"
                        color="primary"
                        :loading="r.loading"
                        :disabled="r.loading"
                    >
                        {{
                            r.chgPasswd
                                ? "Passwort nicht ändern"
                                : "Passwort ändern"
                        }}
                    </v-btn>

                    <v-btn
                        class="my-5"
                        v-if="r.chgPasswd"
                        @click.prevent="changePasswd"
                        type="submit"
                        block
                        variant="outlined"
                        color="primary"
                        :loading="r.loading"
                        :disabled="r.loading"
                    >
                        Passwort jetzt ändern
                    </v-btn>
                </v-form>
            </v-card-text>
        </v-card>
    </v-container>
</template>

<script setup>
import { usePage, useForm } from "@inertiajs/vue3";
import { onMounted, reactive } from "vue";

const form = useForm({
    email: "",
    password: "",
    newpwd: "",
});

const r = reactive({
    infoError: false,
    chgPasswd: false,
    showPwd1: false,
    showPwd2: false,
    showPwd3: false,
    showPwd4: false,
    newPasswd1: "",
    newPasswd2: "",
    loading: false,
});

const alert = reactive({
    shown: false,
    text: "",
    type: "success",
});

onMounted(() => {
    r.password = r.newPasswd1 = r.newPasswd2 = "";
    alert.shown = false;
    const page = usePage();
    if (page.url == "/logout") {
        logout();
    }
});

function login() {
    alert.shown = false;
    if (form.email == "") {
        showAlert("error", "Bitte Email-Adresse angeben");
        return;
    }
    if (form.password == "") {
        showAlert("error", "Bitte Passwort angeben");
        return;
    }
    r.loading = true;
    form.post(route("login"));
    r.loading = false;
}

function changePasswd() {
    alert.shown = false;
    if (form.email == "") {
        showAlert("error", "Bitte Email-Adresse angeben");
        return;
    }
    if (form.password == "" || r.newPasswd1 == "" || r.newPasswd2 == "") {
        showAlert("error", "Bitte Passwörter angeben");
        return;
    }
    if (r.newPasswd1 != r.newPasswd2) {
        showAlert("error", "Passwörter stimmen nicht überein");
        return;
    }
    form.newpwd = r.newPasswd1;
    r.loading = true;
    form.put(route("login.chgpwd"));
    r.loading = false;
    r.chgPasswd = false;
    form.password = r.newPasswd1;
}

function logout() {
    r.loading = true;
    form.delete(route("logout"));
    r.loading = false;
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
</script>
