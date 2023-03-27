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
                <div>{{loginError}}</div>
                <div v-show="loggingIn">Login...</div>
            </q-card-section>
            <q-card-actions class="q-px-md">
              <q-btn type="submit" unelevated size="lg" class="full-width" label="Login" color="light-blue-7"/>
            </q-card-actions>
            <q-card-section class="text-center q-pa-none">
              <p><router-link to="/register" class="text-grey-6">Noch nicht registriert? Account erstellen</router-link></p>
              <p><router-link to="/forgotPassword" class="text-grey-6">Passwort vergessen? Passwort zurücksetzen</router-link></p>
            </q-card-section>
          </q-form>
        </q-card>
      </div>
    </div>
  </q-page>
</template>
<script>
import {useStore} from 'vuex'
import {useRouter} from 'vue-router'
import {computed, ref} from "vue"

export default {
  setup() {
    const email = ref('')
    const password = ref('')
    const store = useStore()
    const router = useRouter()
    const loginError = computed(() => store.state.auth.loginError);
    const loggingIn = computed(() => store.state.auth.loggingIn);
    const accessToken = computed(() => store.state.auth.accessToken);

    if (accessToken) {
      router.push("/home");
    }

    function handleLogin() {
      store.dispatch("auth/doLogin", {email: email.value, password: password.value});
    }




    return {
      email,
      password,
      loggingIn,
      loginError,
      accessToken,
      handleLogin
    }
  }
}
</script>
<style>
.q-card {
  width: 360px;
}
</style>
