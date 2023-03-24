<template>
  <v-card>
    <v-layout>
      <v-app-bar :title="appName">

      </v-app-bar>

      <v-navigation-drawer>
        <v-list>
          <v-list-item v-if="accessToken" to="/home">Home</v-list-item>
          <v-list-item v-if="accessToken" to="/users">Users</v-list-item>
          <v-list-item v-if="accessToken" to="/profile">Profile</v-list-item>
          <v-list-item v-if="!accessToken" to="/login">Login</v-list-item>
          <v-list-item v-if="!accessToken" to="/register">Registrieren</v-list-item>
          <v-list-item v-if="!accessToken" to="/forgot-password">Passwort vergessen</v-list-item>
          <v-list-item v-if="accessToken" to="/logout">Logout</v-list-item>
        </v-list>
      </v-navigation-drawer>

      <v-main style="min-height: 300px;"><router-view></router-view></v-main>
    </v-layout>
  </v-card>
</template>
<script>


import {mapActions, mapState} from "vuex";

export default {
  data() {
    return {
      appName: import.meta.env.VITE_APP_NAME
    }
  },
  computed: {
    ...mapState('auth', ['accessToken'])
  },
  methods: {
    ...mapActions('auth', [
      'fetchAccessToken',
        'getMe'
    ]),
  },
  created() {
    this.fetchAccessToken();
    this.getMe();
  }
}
</script>