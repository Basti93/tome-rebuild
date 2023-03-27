<template>
  <q-page class="full-height full-width row justify-center items-center">
    <div class="column">
      <div class="row">
        <h5 class="text-h5 q-my-md">Passwort zurücksetzen</h5>
      </div>
      <div class="row">
        <q-card square bordered class="q-pa-lg shadow-1">
          <q-form ref="form" @submit.prevent="sendForgotPassword">
            <q-card-section>
                <q-input
                    type="email"
                    :rules="[ val => val && val.length > 0 || 'E-Mail darf nicht leer sein']"
                    v-model="email"
                    label="E-Mail"
                ></q-input>
                <div v-if="loading">E-Mail für das Passwort Zurücksetzen wird verschickt...</div>
                <div v-if="error">Fehler, bitte erneut versuchen!</div>
                <div v-if="done">Passwort Zurücksetzen E-Mail erfolgreich versendet.</div>
            </q-card-section>
          <q-card-actions class="q-px-md">
            <q-btn type="submit" unelevated size="lg" class="full-width" label="E-Mail anfordern" :disabled="loading" color="light-blue-7"/>
          </q-card-actions>
          </q-form>
        </q-card>
      </div>
    </div>
  </q-page>
</template>
<script>
import {useMutation} from "@vue/apollo-composable";
import passwordForgotMutation from "../queries/forgotPassword.mutation.gql"
import { ref } from 'vue'

export default {
  setup () {
    const form = ref(null)
    const email = ref('')
    const done = ref(false);
    const { mutate: sendForgotPassword, onDone, error: error, loading: loading } = useMutation(passwordForgotMutation, () => ({
      variables: {
        email: email.value,
        url: import.meta.env.VITE_BASE_URL + "/#/reset-password?email=__EMAIL__&token=__TOKEN__"
      },
    }))
    onDone(() => {
      done.value = true;
      email.value = '';
      form.value.resetValidation();
      form.value.reset();
    })
    return {
      email,
      sendForgotPassword,
      loading,
      error,
      done,
      form
    }
  }
}
</script>