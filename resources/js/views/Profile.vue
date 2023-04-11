<template>
  <q-page>
      <div class="row q-ma-ma justify-center">
        <q-card class="col-12 col-lg-3 q-mt-md">
                  <q-card-section>
                      <q-avatar class="q-ma-md-lg q-ma-sm" size="150px">
                          <img v-if="me.imageUrl" :src="me.imageUrl">
                          <img v-else src="../../img/boy-avatar.png">
                      </q-avatar>
                      <div class="text-h5">{{ me.firstname }} {{ me.lastname }}</div>
                      <div class="text-h6">{{ me.nickname }}</div>
                  </q-card-section>
                  <q-form ref="uploadProfileImageForm" @submit.prevent="uploadProfileImage">
                    <q-card-section>

                      <q-file
                        filled
                        bottom-slots
                        v-model="profileImageUpload"
                        label="Neues Profilbild hochladen"
                        :hint="profileImageUpload ? profileImageUpload.name : 'Bitte wählen Sie ein Bild aus'"
                        max-file-size="5242880"
                        accept="image/*"
                        max-files="1"
                        counter>
                          <template v-slot:prepend>
                              <q-icon name="cloud_upload" @click.stop.prevent />
                          </template>
                          <template v-slot:append>
                              <q-icon name="close" @click.stop.prevent="profileImageUpload = null" class="cursor-pointer" />
                          </template>
                          <template v-slot:hint>
                              Maximal 5 MB
                          </template>
                        </q-file>
                    </q-card-section>
                    <q-card-actions>
                        <q-btn type="submit" :disable="!profileImageUpload || profileImageUpload.length === 0" color="primary" label="Speichern" />
                    </q-card-actions>
                  </q-form>
              </q-card>
        <q-card class="col-12 col-lg-4 q-mt-md q-ml-lg-sm">
                  <q-form @submit.prevent="updateMe" @reset="resetMe">
                  <q-card-section>
                      <q-input
                        type="text"
                        v-model="editMe.firstname"
                        label="Vorname*"
                        :rules="[val => val && val.length > 0 || 'Vorname darf nicht leer sein']"/>
                      <q-input
                        type="text"
                        v-model="editMe.lastname"
                        label="Nachname*"
                        :rules="[val => val && val.length > 0 || 'Nachname darf nicht leer sein']"/>
                      <q-input
                        type="text"
                        v-model="editMe.nickname"
                        label="Spitzname" />
                      <q-input
                        type="email"
                        v-model="editMe.email"
                        label="E-Mail*"
                        :rules="[val => val && val.length > 0 || 'E-Mail darf nicht leer sein']"/>
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
                                        mask="DD.MM.YYYY">
                                    </q-date>
                                  </q-popup-proxy>
                              </q-icon>
                          </template>
                      </q-input>
                      <q-input
                        type="text"
                        v-model="editMe.phone"
                        hint="Bei Kindern bitte die Nummer der Eltern für Notfälle"
                        label="Telefonnummer*" />
                      <p class="text-red" v-if="profileValidationErrors">{{profileValidationErrors}}</p>
                  </q-card-section>
                  <q-card-actions>
                      <q-btn type="submit" color="primary" block class="mt-2" :disabled="loading">Speichern</q-btn>
                      <q-btn type="reset" block class="mt-2" :disabled="loading">Zurücksetzen</q-btn>
                  </q-card-actions>
                  </q-form>
              </q-card>
        <q-card class="col-12 col-lg-3 q-mt-md q-ml-lg-sm">
                <q-card-section>
                  <div class="text-h6">Passwort ändern</div>
                </q-card-section>
                <q-card-section>
                    <q-form ref="passwordForm" @submit.prevent="changePassword">
                        <q-input
                                type="password"
                                v-model="current_password"
                                label="Aktuelles Passwort"
                                lazy-rules='ondemand'
                                :rules="[ val => val && val.length > 0 || 'Bitte aktuelles Passwort eingeben']"
                        ></q-input>
                        <q-input
                                type="password"
                                v-model="password"
                                label="Neues Passwort"
                                lazy-rules='ondemand'
                                :rules="[ val => val && val.length >= 8 || 'Passwort muss mindestens 8 Zeichen haben']"
                        ></q-input>
                        <q-input
                                type="password"
                                v-model="password_confirmation"
                                label="Passwort bestätigen"
                                lazy-rules='ondemand'
                                :rules="[ val => val && val === password || 'Passwort muss identisch sein']"
                        ></q-input>
                        <q-btn type="submit" color="primary" class="mt-2" :disabled="loading">Passwort ändern</q-btn>
                        <div v-if="passwordValidationErrors">{{passwordValidationErrors}}</div>
                    </q-form>
                </q-card-section>
              </q-card>
      </div>
  </q-page>

</template>
<script lang="ts">

import {computed, ref} from "vue";
import updatepassword from "../queries/updatepassword.mutation.gql";
import {date, useQuasar} from "quasar";
import apolloClient from "../apollo";
import meQuery from "../queries/me.query.gql";
import meMutation from "../queries/me.mutation.gql";
import uploadProfileImageMutation from "../queries/uploadprofileimage.mutation.gql";

export default {
  emits: ["user-updated"],
  setup (props, { emit }) {
    const loading = ref(false);
    const $q = useQuasar();
    const me = ref({})
    const uploadProfileImageForm = ref(null);
    const editMe = ref({})
    const profileImageUpload = ref(null);
    const formattedBirthdate = computed({
        get() {
            return date.formatDate(editMe.value.birthdate, 'DD.MM.YYYY');
        },
        set(value) {
            editMe.value.birthdate = date.formatDate(date.extractDate(value, 'DD.MM.YYYY'),'YYYY-MM-DD');
        }
    });
    const passwordForm = ref('');
    const current_password = ref('');
    const password = ref('');
    const password_confirmation = ref('');
    const passwordValidationErrors = ref('');
    const profileValidationErrors = ref('');

      const fetchMe = () => {
        loading.value = true;
        apolloClient.query({query: meQuery, fetchPolicy: "network-only"})
        .then(({data}) => {
            updateUserObjects(data.me);
        }).catch(() => {
            localStorage.removeItem('accessToken');
            $q.notify({
                type: 'negative',
                message: 'Fehler beim Laden des Benutzers'
            })
        })
        loading.value = false;
    }

    fetchMe();

    const changePassword = () => {
        loading.value = true;
        apolloClient.mutate({mutation: updatepassword, variables: {
                current_password: current_password.value,
                password: password.value,
                password_confirmation: password_confirmation.value,
        }}).then(() => {
            $q.notify({
                type: 'positive',
                message: 'Passwort erfolgreich geändert'
            })
            password_confirmation.value = '';
            password.value = '';
            current_password.value = '';
            passwordForm.value.resetValidation();
        }).catch(({ graphQLErrors }) => {
            $q.notify({
                type: 'negative',
                message: 'Fehler beim Ändern des Passworts'
            })
            if (graphQLErrors[0].extensions.category === "validation") {
                passwordValidationErrors.value = graphQLErrors[0].extensions.validation;
            }
        }).finally(() => {
            loading.value = false;
        })
      }

      const updateUserObjects = (user) => {
          me.value = user;
          editMe.value = {...me.value};
          editMe.value.birthdate = date.formatDate(editMe.value.birthdate, 'YYYY-MM-DD')
          emit('user-updated');
      }

      const updateMe = () => {
          loading.value = true;
          profileValidationErrors.value = '';
          apolloClient.mutate({
              mutation: meMutation,
              variables: {
                  id: editMe.value.id,
                  firstname: editMe.value.firstname,
                  lastname: editMe.value.lastname,
                  nickname: editMe.value.nickname,
                  email: editMe.value.email,
                  birthdate: editMe.value.birthdate,
                  phone: editMe.value.phone,
              },
          }).then(({data}) => {
              updateUserObjects(data.updateMe);
              $q.notify({
                  type: 'positive',
                  message: 'Profil erfolgreich aktualisiert'
              })
          }).catch(({graphQLErrors}) => {
              if (graphQLErrors[0].extensions.category === "validation") {
                  profileValidationErrors.value = graphQLErrors[0].extensions.validation;
              }
              $q.notify({
                  type: 'negative',
                  message: 'Fehler beim Aktualisieren des Profils'
              })
          }).finally(() => {
              loading.value = false;
          })
      }
      const resetMe = () => {
          editMe.value = {...me.value};
      }

      const uploadProfileImage = () => {
          loading.value = true;
          apolloClient.mutate({
              mutation: uploadProfileImageMutation,
              variables: {
                  id: me.value.id,
                  file: profileImageUpload.value,
              },
          }).then(() => {
              $q.notify({
                  type: 'positive',
                  message: 'Profilbild erfolgreich aktualisiert'
              })
              profileImageUpload.value = null;
              fetchMe()
          }).catch(({graphQLErrors}) => {
              if (graphQLErrors[0].extensions.category === "validation") {
                  profileValidationErrors.value = graphQLErrors[0].extensions.validation;
              }
              $q.notify({
                  type: 'negative',
                  message: 'Fehler beim Aktualisieren des Profilbildes'
              })
          }).finally(() => {
              loading.value = false;
          })
      }

    return {
      changePassword,
      updateMe,
      resetMe,
      loading,
      current_password,
      password_confirmation,
      password,
      passwordValidationErrors,
      profileValidationErrors,
      me,
      editMe,
      date,
      passwordForm,
      formattedBirthdate,
      profileImageUpload,
      uploadProfileImage,
      uploadProfileImageForm,
    }
  },
}
</script>