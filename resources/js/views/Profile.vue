<template>
  <q-page class="q-pa-m">
    <h3>Profil</h3>
    <div class="q-pa-md" style="max-width: 400px">
    <h4>Passwort ändern</h4>
    <q-form @submit.prevent="changePassword">
      <q-input
          type="password"
          v-model="current_password"
          label="Aktuelles Passwort"
          :rules="[ val => val && val.length > 0 || 'Bitte aktuelles Passwort eingeben']"
      ></q-input>
      <q-input
          type="password"
          v-model="password"
          label="Neues Passwort"
          lazy-rules
          :rules="[ val => val && val.length >= 8 || 'Passwort muss mindestens 8 Zeichen haben']"
      ></q-input>
      <q-input
          type="password"
          v-model="password_confirmation"
          label="Passwort bestätigen"
          lazy-rules
          :rules="[ val => val && val === password || 'Passwort muss identisch sein']"
      ></q-input>
      <q-btn type="submit" block class="mt-2" :disabled="loading">Passwort ändern</q-btn>
      <div v-if="loading">Passwort wird geändert...</div>
      <div v-if="validationErrors">{{validationErrors}}</div>
      <div v-if="error">Fehler beim abschicken, bitte erneut versuchen!</div>
    </q-form>
    <div v-if="done">Passwort erfolgreich geändert!</div>
    </div>
  </q-page>

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