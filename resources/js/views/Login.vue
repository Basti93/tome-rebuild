<template>
  <v-sheet width="300" class="mx-auto">

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
      <v-alert type="info" v-show="loginLoading">Login...</v-alert>
    </v-form>
  </v-sheet>
</template>
<script>
import {ref} from 'vue'
import {useMutation} from '@vue/apollo-composable'
import gql from "graphql-tag";

export default {
  data() {
    return {
      email: '',
      password: ''
    }
  },
  setup() {
    const email = ref('')
    const password = ref('')
    const {mutate: login, onDone, loading: loginLoading, error: loginError} = useMutation(gql(`mutation login($email: String!, $password: String!) {
          login(email: $email, password: $password) {
            token
          }
        }
      `),() => ({
          variables: {
            email: email.value,
            password: password.value,
          },
        }))
      onDone(result => {
        if (result.data) {
          localStorage.setItem("token", result.data.login.token)
        }
      })
    return {
      email,
      password,
      login,
      loginError,
      loginLoading
    }
  },
  methods: {
    handleLogin() {
      this.login();

    }
  }
}
</script>