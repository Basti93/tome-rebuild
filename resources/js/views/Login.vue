<template>
  <q-page class="full-height full-width row justify-center items-center">
    <div class="column">
      <div class="row">
        <h5 class="text-h5 q-my-md">Login</h5>
      </div>
      <div class="row">
        <q-card square bordered class="q-pa-lg shadow-1">
          <q-form @submit.prevent="handleLogin" class="q-ma-m">
            <q-card-section>
                <q-input
                    type="text"
                    v-model="email"
                    :rules="[ val => val && val.length > 0 || 'E-Mail darf nicht leer sein']"
                    label="E-Mail"/>
                <q-input
                    v-model="password"
                    :rules="[ val => val && val.length > 0 || 'Passwort darf nicht leer sein']"
                    type="password"
                    label="Passwort"/>
                <div class="text-red" v-if="validationErrors">{{validationErrors}}</div>
            </q-card-section>
            <q-card-actions class="q-px-md">
              <q-btn type="submit" unelevated size="lg" class="full-width" label="Login" color="light-blue-7"/>
            </q-card-actions>
            <q-card-section class="text-center q-pa-none">
              <p><router-link to="/register" class="text-grey-6">Noch nicht registriert? Account erstellen</router-link></p>
              <p><router-link to="/forgot-password" class="text-grey-6">Passwort vergessen? Passwort zurücksetzen</router-link></p>
            </q-card-section>
          </q-form>
        </q-card>
      </div>
    </div>
  </q-page>
</template>
<script>
import {useRoute, useRouter} from 'vue-router'
import {computed, ref} from "vue"
import apolloClient from "../apollo";
import loginMutation from "../queries/login.mutation.gql";
import {useQuasar} from "quasar";

export default {
  emits: ["login"],
  setup(props, { emit }) {
    const email = ref('')
    const password = ref('')
    const router = useRouter()
    const route = useRoute()
    const validationErrors = ref('')
    const $q = useQuasar();
    const accessToken = computed(() => localStorage.getItem('accessToken'))

      if (accessToken) {
      router.push("/home");
    }

    function handleLogin() {
        validationErrors.value = '';
        apolloClient.mutate({
            mutation: loginMutation,
            variables: {email: email.value, password: password.value},
        }).then(({data}) => {
            localStorage.setItem('accessToken', data.login.token);
            $q.notify({
                message: 'Erfolgreich eingeloggt!',
                color: 'positive'})
            emit('login');
            router.push(route.query.redirect || "/home")
        }).catch(({ graphQLErrors }) => {
            validationErrors.value = graphQLErrors[0].message;
            $q.notify({
                message: 'Fehler beim Login!',
                color: 'negative'})
            localStorage.removeItem('accessToken');
        });
    }

    return {
      email,
      password,
      accessToken,
      handleLogin,
      validationErrors,
    }
  }
}
</script>
<style>
.q-card {
  width: 360px;
}
</style>
