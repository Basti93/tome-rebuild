<template>
  <v-sheet width="300" class="mx-auto">
    <h2>Login</h2>
    <v-form validate-on="submit" @submit.prevent="handleLogin">
      <v-text-field
          v-model="email"
          label="E-Mail"
      ></v-text-field>
      <v-text-field
          v-model="password"
          type="password"
          label="Passwort"
      ></v-text-field>
      <v-btn type="submit" block class="mt-2">Login</v-btn>
      <v-alert type="error" v-show="loginError">Fehler beim Login!</v-alert>
      <v-alert type="info" v-show="loggingIn">Login...</v-alert>
    </v-form>
  </v-sheet>
</template>
<script>
import {mapState, mapActions} from "vuex";

export default {
  data() {
    return {
      email: '',
      password: ''
    }
  },
  created() {
    if (this.loggedIn) {
      this.$router.push("/home");
    }
  },
  computed: {
    ...mapState('auth', [
      'loginError',
      'loggingIn',
      'accessToken'
    ]),
    loggedIn() {
      return this.accessToken;
    }
  },
  methods: {
    ...mapActions('auth', [
      'doLogin'
    ]),
    handleLogin() {
      this.doLogin({email: this.email, password: this.password})
    }
  }
}
</script>