<template>
  <q-page class="full-height full-width row justify-center items-center">
    <div class="column">
      <div class="row">
        <h5 class="text-h5 q-my-md">Registrieren</h5>
      </div>
      <div class="row">
        <q-card square bordered class="q-pa-lg shadow-1" style="min-width: 400px">
          <q-form ref="registerForm" @submit.prevent="register">
            <q-card-section>
              <q-input
                  type="text"
                  v-model="firstname"
                  label="Vorname*"
                  :rules="[ val => val && val.length > 0 || 'Vorname darf nicht leer sein']"
              ></q-input>
              <q-input
                  type="text"
                  v-model="lastname"
                  label="Nachname*"
                  :rules="[ val => val && val.length > 0 || 'Nachname darf nicht leer sein']"
              ></q-input>
              <q-input
                  type="text"
                  v-model="nickname"
                  label="Spitzname"
              ></q-input>
                <q-input
                        label="Geburtsdatum*"
                        v-model="formattedBirthdate"
                        :rules="[val => val && val.length > 0 || 'Geburtsdatum darf nicht leer sein']">
                    <template v-slot:append>
                        <q-icon name="event" class="cursor-pointer">
                            <q-popup-proxy
                                    ref="proxyBirthdate"
                                    transition-show="scale"
                                    transition-hide="scale">
                                <q-date
                                        minimal
                                        v-model="formattedBirthdate"
                                        @update:model-value="$refs.proxyBirthdate.hide()"
                                        mask="DD.MM.YYYY"
                                        >
                                </q-date>
                            </q-popup-proxy>
                        </q-icon>
                    </template>
                </q-input>
              <q-input
                  type="tel"
                  v-model="phone"
                  hint="Bei Kindern bitte die Nummer der Eltern für Notfälle"
                  label="Telefonummer"
              ></q-input>
              <q-input
                  type="email"
                  v-model="email"
                  label="E-Mail*"
                  :rules="[ val => val && val.length > 0 || 'E-Mail darf nicht leer sein']"
              ></q-input>
              <q-input
                  v-model="password"
                  type="password"
                  label="Passwort*"
                  :rules="[ val => val && val.length >= 8 || 'Passwort muss mindestens 8 Zeichen haben']"
              ></q-input>
              <q-input
                  v-model="password_confirmation"
                  type="password"
                  label="Passwort bestätigen*"
                  :rules="[ val => val && val === password || 'Passwort muss identisch sein']"
              ></q-input>
              <div v-if="loadingRegister">Schicke Registrierung ab...</div>
              <div v-if="validationErrors">{{ validationErrors }}</div>
              <div v-if="registerDone">Registrierung erfolgreich. Bitte deine E-Mail bestätigen und dann muss ein
                Trainer dich noch freischalten!
              </div>
            </q-card-section>
            <q-card-actions class="q-px-md">
              <q-btn type="submit" unelevated size="lg" class="full-width" label="Registrieren"
                     :disabled="loadingRegister" color="light-blue-7"/>
            </q-card-actions>
          </q-form>
        </q-card>
      </div>
    </div>
  </q-page>
</template>
<script lang="ts">
import {useMutation} from "@vue/apollo-composable";
import registerMutation from "../queries/register.mutation.gql"
import {computed, ref} from 'vue'
import {date} from "quasar";

export default {
  setup() {
    const registerForm = ref(null)
    const firstname = ref('')
    const lastname = ref('')
    const nickname = ref('')
    const birthdate = ref(date.formatDate(date.buildDate({year: 2000, month: 1, day: 1}), 'YYYY-DD-MM'));
    const phone = ref('')
    const email = ref('')
    const password = ref('')
    const password_confirmation = ref('')
    const validationErrors = ref('')
    const registerDone = ref(false);
    const formattedBirthdate = computed({
        get() {
            return date.formatDate(birthdate.value, 'DD.MM.YYYY');
        },
        set(value) {
            birthdate.value = date.formatDate(date.extractDate(value, 'DD.MM.YYYY'),'YYYY-MM-DD');
        }
    });
    const {
      mutate: register,
      onDone,
      error: errorRegister,
      onError,
      loading: loadingRegister
    } = useMutation(registerMutation, () => ({
      variables: {
        firstname: firstname.value,
        lastname: lastname.value,
        nickname: nickname.value,
        phone: phone.value,
        birthdate: birthdate.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value,
        url: import.meta.env.VITE_BASE_URL + "/#/verify-email?id=__ID__&token=__HASH__&expires=__EXPIRES__&signature=__SIGNATURE__"
      },
    }))
    onDone(() => {
      registerDone.value = true;
      firstname.value = '';
      lastname.value = '';
      nickname.value = '';
      phone.value = '';
      birthdate.value = '';
      email.value = '';
      password.value = '';
      password_confirmation.value = '';
      registerForm.value.resetValidation();
      registerForm.value.reset();
      validationErrors.value = '';
    })
    onError(errors => {
      let {graphQLErrors} = errors;
      if (graphQLErrors[0].extensions.category === "validation") {
        validationErrors.value = graphQLErrors[0].extensions.validation;
      }
    })
    return {
      firstname,
      lastname,
      phone,
      nickname,
      formattedBirthdate,
      email,
      password,
      password_confirmation,
      register,
      loadingRegister,
      errorRegister,
      registerDone,
      validationErrors,
      registerForm
    }
  }
}
</script>