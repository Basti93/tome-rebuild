<template>
  <v-sheet width="300" class="mx-auto">
  <h2>Profile</h2>
  <h3>Passwort ändern</h3>
  <v-form validate-on="submit" @submit.prevent="changePassword">
    <v-text-field
        v-model="current_password"
        type="password"
        label="Aktuelles Passwort"
    ></v-text-field>
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
import updatepassword from "../queries/updatepassword.mutation.gql";

export default {
  setup () {
    const done = ref(false);
    const current_password = ref('');
    const password = ref('');
    const password_confirmation = ref('');
    const validationErrors = ref('');

    const { mutate: changePassword, loading, error, onError, onDone } = useMutation(updatepassword, () => ({
      variables: {
        current_password: current_password.value,
        password: password.value,
        password_confirmation: password_confirmation.value,
      },
    }))

    onDone(() => {
      done.value = true;
      validationErrors.value = '';
      current_password.value = '';
      password_confirmation.value = '';
      password.value = '';
    })

    onError(error => {
      done.value = false;
      let { graphQLErrors } = error;
      if (graphQLErrors[0].extensions.category === "validation") {
        validationErrors.value = graphQLErrors[0].extensions.validation;
      }
    })

    return {
      changePassword,
      loading,
      error,
      current_password,
      password_confirmation,
      password,
      validationErrors,
      done
    }
  },
  data() {
    return {
      firstname: String
    }
  },
  created() {
    this.firstname="test";
  }
}
</script>