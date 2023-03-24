<template>
  <v-sheet width="300" class="mx-auto">
    <h2>Passwort zurücksetzen</h2>
    <v-form validate-on="submit" @submit.prevent="sendForgotPassword">
      <v-text-field
          v-model="email"
          label="E-Mail"
      ></v-text-field>
      <v-btn type="submit" block class="mt-2" :disabled="loading">E-Mail zum zurücksetzen senden</v-btn>
      <div v-if="loading">Schicke Registrierung ab...</div>
      <div v-if="error">Fehler bei der Registrierung, bitte erneut versuchen!</div>
      <div v-if="Done">Registrierung erfolgreich. Bitte deine E-Mail bestätigen und dann muss ein Trainer dich noch freischalten!</div>
    </v-form>
  </v-sheet>
</template>
<script>
import {useMutation} from "@vue/apollo-composable";
import passwordForgotMutation from "../queries/forgotPassword.mutation.gql"
import { ref } from 'vue'

export default {
  setup () {
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
    })
    return {
      email,
      sendForgotPassword,
      loading,
      error,
      done
    }
  },
  data() {
    return {
    }
  },
  computed: {
  },
  methods: {
  }
}
</script>