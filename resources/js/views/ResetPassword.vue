<template>
  <v-sheet width="300" class="mx-auto">
    <h2>Passwort ändern</h2>
    <v-form v-if="!done" validate-on="submit" @submit.prevent="resetPassword">
      <v-text-field
          v-model="password"
          type="password"
          label="Passwort"
      ></v-text-field>
      <v-text-field
          v-model="password_confirmation"
          type="password"
          label="Passwort bestätigen"
      ></v-text-field>
      <v-btn type="submit" block class="mt-2" :disabled="loading">Passwort ändern</v-btn>
      <div v-if="loading">Passwort wird geändert...</div>
      <div v-if="validationErrors">{{validationErrors}}</div>
      <div v-if="error">Fehler beim abschicken, bitte erneut versuchen!</div>
    </v-form>
    <div v-if="done">Passwort erfolgreich geändert!</div>
  </v-sheet>
</template>
<script>

import {ref} from "vue";
import {useMutation} from "@vue/apollo-composable";
import resetPasswordMutation from "../queries/resetpassword.mutation.gql";
import { useRoute } from 'vue-router'

export default {

  setup () {
    const done = ref(false);
    const password = ref('');
    const password_confirmation = ref('');
    const validationErrors = ref('');
    const route = useRoute();

    const { mutate: resetPassword, loading, error, onError, onDone } = useMutation(resetPasswordMutation, () => ({
      variables: {
        password: password.value,
        password_confirmation: password_confirmation.value,
        token: route.query.token,
        email: route.query.email,
      },
    }))

    onDone(() => {
      done.value = true;
    })

    onError(error => {
      let { graphQLErrors } = error;
      if (graphQLErrors[0].extensions.category === "validation") {
        validationErrors.value = graphQLErrors[0].extensions.validation;
      }
    })

    return {
      resetPassword,
      loading,
      error,
      password_confirmation,
      password,
      validationErrors,
      done
    }
  },
  created() {

  }
}
</script>