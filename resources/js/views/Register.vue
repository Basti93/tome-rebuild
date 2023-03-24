<template>
  <v-sheet width="300" class="mx-auto">

    <v-form validate-on="submit" @submit.prevent="register">
      <v-text-field
          v-model="name"
          label="Name"
      ></v-text-field>
      <v-text-field
          v-model="email"
          label="E-Mail"
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
      <v-btn type="submit" block class="mt-2" :disabled="loadingRegister">Registrieren</v-btn>
      <div v-if="loadingRegister">Schicke Registrierung ab...</div>
      <div v-if="validationErrors">{{validationErrors}}</div>
      <div v-if="errorRegister">Fehler bei der Registrierung, bitte erneut versuchen!</div>
      <div v-if="registerDone">Registrierung erfolgreich. Bitte deine E-Mail bestätigen und dann muss ein Trainer dich noch freischalten!</div>
    </v-form>
  </v-sheet>
</template>
<script>
import {useMutation} from "@vue/apollo-composable";
import registerMutation from "../queries/register.mutation.gql"
import { ref } from 'vue'

export default {
  setup () {
    const name = ref('')
    const email = ref('')
    const password = ref('')
    const password_confirmation = ref('')
    const validationErrors = ref('')
    const registerDone = ref(false);
    const { mutate: register, onDone, error: errorRegister, onError, loading: loadingRegister } = useMutation(registerMutation, () => ({
      variables: {
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value,
        url: import.meta.env.VITE_BASE_URL + "/#/verify-email?id=__ID__&token=__HASH__&expires=__EXPIRES__&signature=__SIGNATURE__"
      },
    }))
    onDone(() => {
      registerDone.value = true;
      name.value = '';
      email.value = '';
      password.value = '';
      password_confirmation.value = '';
      validationErrors.value = '';
    })
    onError(error => {
      let { graphQLErrors } = error;
      if (graphQLErrors[0].extensions.category === "validation") {
        validationErrors.value = graphQLErrors[0].extensions.validation;
      }
    })
    return {
      name,
      email,
      password,
      password_confirmation,
      register,
      loadingRegister,
      errorRegister,
      registerDone,
      validationErrors
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