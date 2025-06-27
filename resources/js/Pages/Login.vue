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

<!--script>

import { usePage } from "@inertiajs/vue3";
export default {
    name: "Login",
    data: function () {
        return {
            infoError: false,
            email: "",
            password: "",
            token: "",
            error: "",
            chgPasswd: false,
            showPwd1: false,
            showPwd2: false,
            showPwd3: false,
            showPwd4: false,
            newPasswd1: "",
            newPasswd2: "",
            loading: false,
            alert: {
                shown: false,
                text: "",
                type: "success",
            },
        };
    },
    mounted: function () {
    },
    methods: {
        async login(pushIfSucc) {
            // this.infoError = false;
            // this.token = "";
            // this.error = "";
            // var me = this;
            // this.loading = true;
            // sessionStorage.setItem("email", "");
            // try {
            //     var response = await me.$http.post("/auth/login", {
            //         email: this.email,
            //         password: this.password,
            //     });
            //     var loginData = response.data;
            //     console.log("logindata", loginData);
            //     sessionStorage.setItem("token", loginData.token);
            //     sessionStorage.setItem("readonly", loginData.user.readonly);
            //     sessionStorage.setItem("is_admin", loginData.user.is_admin);
            //     sessionStorage.setItem(
            //         "may_read_history",
            //         loginData.user.may_read_history
            //     );
            //     sessionStorage.setItem("email", this.email);
            //     me.$store.commit("logged_in", loginData.user);
            //     if (pushIfSucc) {
            //         me.$router.push("/");
            //     }
            // } catch (error) {
            //     me.loading = false;
            //     if (error.response) {
            //         var errorData = error.response.data;
            //         me.infoError = true;
            //         me.error = errorData.error;
            //     } else if (error.request) {
            //         console.log("error6", error.request);
            //     } else {
            //         console.log("error7", error);
            //     }
            // }
        },
        logout() {
            // this.infoError = false;
            // var me = this;
            // this.loading = true;
            // this.$http
            //     .post("/auth/logout", {
            //         token: sessionStorage.getItem("token"),
            //     })
            //     // eslint-disable-next-line no-unused-vars
            //     .then(function (response) {
            //         // var data = response.data;
            //         sessionStorage.removeItem("token");
            //         sessionStorage.removeItem("readonly");
            //         sessionStorage.removeItem("is_admin");
            //         sessionStorage.removeItem("may_read_history");
            //         sessionStorage.removeItem("email");
            //         me.$store.commit("logged_out");
            //         me.$router.push("/");
            //     })
            //     .catch(function (error) {
            //         me.loading = false;
            //         if (error.response) {
            //             var data = error.response.data;
            //             me.infoError = true;
            //             if (data) {
            //                 me.error = data.error;
            //             }
            //         } else if (error.request) {
            //             console.log("error8", error.request);
            //         } else {
            //             console.log("error9", error);
            //         }
            //     });
        },
        async changePasswd() {
            // var me = this;
            // if (
            //     me.password == "" ||
            //     me.newPasswd1 == "" ||
            //     me.newPasswd2 == ""
            // ) {
            //     me.showAlert("error", "Bitte Passwörter angeben");
            //     return;
            // }
            // if (me.newPasswd1 != me.newPasswd2) {
            //     me.showAlert("error", "Passwörter stimmen nicht überein");
            //     return;
            // }
            // if (this.email == "") {
            //     me.showAlert("error", "Bitte Email-Adresse angeben");
            //     return;
            // }
            // await this.login(false);
            // var newPasswd = {
            //     password_old: this.password,
            //     password_new: this.newPasswd1,
            //     password_conf: this.newPasswd2,
            // };
            // try {
            //     await me.$http.post(
            //         "/auth/change_password?token=" +
            //             sessionStorage.getItem("token"),
            //         newPasswd
            //     );
            //     me.showAlert("success", "Passwort geändert");
            //     me.loading = false;
            //     me.chgPasswd = false;
            //     me.password = me.newPasswd1;
            // } catch (error) {
            //     me.showAlert("error", "Fehler");
            // }
        },
        showAlert(type, text) {
            this.alert.shown = true;
            this.alert.type = type;
            this.alert.text = text;

            setTimeout(() => {
                this.alert.shown = false;
                this.loading = false;
            }, 5000);
        },
    },
};

Klaus Petri yyyyKP $2y$12$s/BdPcm54Gl46PnEoQgtt.O7mtsp.d3c6DnUTPCTI1ldhN9P7ueDS
</script-->
