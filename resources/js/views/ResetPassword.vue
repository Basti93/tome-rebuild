<template>
  <q-page class="full-height full-width row justify-center items-center">
    <div class="column">
      <div class="row">
        <h5 class="text-h5 q-my-md">Passwort ändern</h5>
      </div>
      <div class="row">
        <q-card square bordered class="q-pa-lg shadow-1">
          <q-card-section>
            <q-form validate-on="submit" @submit.prevent>
              <q-input
                  v-model="password"
                  type="password"
                  label="Passwort"
                  :rules="[ val => val && val.length >= 8 || 'Passwort muss mindestens 8 Zeichen haben']"
              ></q-input>
              <q-input
                  v-model="password_confirmation"
                  type="password"
                  label="Passwort bestätigen"
                  :rules="[ val => val && val === password || 'Passwort muss identisch sein']"
              ></q-input>
              <div v-if="loading">Passwort wird geändert...</div>
              <div v-if="validationErrors">{{validationErrors}}</div>
              <div v-if="error">Fehler beim abschicken, bitte erneut versuchen!</div>
              <div v-if="done">Passwort erfolgreich geändert!</div>
            </q-form>
          </q-card-section>
          <q-card-actions class="q-px-md">
            <q-btn @click="resetPassword" unelevated size="lg" class="full-width" label="Passwort ändern" :disabled="loading" color="light-blue-7"/>
          </q-card-actions>
        </q-card>
      </div>
    </div>
  </q-page>
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