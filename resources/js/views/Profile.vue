<template>
  <q-page>
      <div class="row justify-center q-ma-md items-lg-start">
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
        <q-card class="col-12 col-lg-3 q-mt-md q-ml-lg-sm">
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
                        label="Geburtsdatum"
                        type="date"
                        v-model="editMe.birthdate"
                        :rules="[val => val && val.length > 0 || 'Geburtsdatum darf nicht leer sein']"/>
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
        <q-card class="col-12 col-lg-6 q-mt-md q-ml-lg-sm">
              <q-card-section>
                  <div class="text-h6">Benachrichtigungen</div>
              </q-card-section>
              <q-card-section>
                  <q-markup-table flat wrap-cells>
                      <thead>
                      <tr>
                          <th class="text-left">Bezeichnung</th>
                          <th class="text-left">Push</th>
                          <th class="text-left">E-Mail</th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="notificationSetting in notificationSettings"
                          :key="notificationSetting.id">
                          <td>{{ $t("message.notificationSettings." + notificationSetting.name) }}</td>
                          <td>
                              <q-checkbox
                                      :val="notificationSetting.id"
                                      v-model="editMe.notificationSettingsPush"
                                      label="Push"
                              ></q-checkbox>
                          </td>
                          <td>
                              <q-checkbox
                                      :val="notificationSetting.id"
                                      label="E-Mail"
                                      v-model="editMe.notificationSettingsEmail"
                              ></q-checkbox>
                          </td>
                      </tr>
                      </tbody>
                  </q-markup-table>
              </q-card-section>
            <q-card-actions>
                <q-btn type="button" color="primary" block class="mt-2" :disabled="loading" @click="updateNotificationSettings">Speichern</q-btn>
            </q-card-actions>
          </q-card>
      </div>
  </q-page>

</template>
<script lang="ts">

import {computed, reactive, ref, watch} from "vue";
import updatepassword from "../queries/updatepassword.mutation.gql";
import {date, useQuasar} from "quasar";
import apolloClient from "../apollo";
import profileQuery from "../queries/profiledata.query.gql";
import notificationSettingsQuery from "../queries/notificationsetting.query.gql";
import meMutation from "../queries/me.mutation.gql";
import notificationMutation from "../queries/notificationsettings.mutation.gql";
import uploadProfileImageMutation from "../queries/uploadprofileimage.mutation.gql";
import {useQuery} from "@vue/apollo-composable";
import {useI18n} from "vue-i18n";

export default {
  emits: ["user-updated"],
  setup (props, { emit }) {
    const loading = ref(false);
    const $q = useQuasar();
    const me = ref({})
    const uploadProfileImageForm = ref(null);
    const editMe = ref({
      firstname: '',
      lastname: '',
      nickname: '',
      email: '',
      birthdate: '',
      phone: '',
      notificationSettingsEmail: [],
      notificationSettingsPush: [],
    })
    const profileImageUpload = ref(null);
    const passwordForm = ref('');
    const current_password = ref('');
    const password = ref('');
    const password_confirmation = ref('');
    const passwordValidationErrors = ref('');
    const profileValidationErrors = ref('');
    const {result: notificationSettingsResult} = useQuery(notificationSettingsQuery);
    const notificationSettings = computed(() => notificationSettingsResult.value?.notificationSettings ?? [])

    const fetchProfileData = () => {
        loading.value = true;
        apolloClient.query({query: profileQuery, fetchPolicy: "network-only"})
        .then(({data}) => {
            me.value = data.me;
            updateUserObjects(data.me);
            updateNotificationSettingsObjects(data.me);
        }).catch(({graphQLErrors}) => {
            if (graphQLErrors && graphQLErrors.some(e => e.message === 'Unauthenticated.')) {
                localStorage.removeItem('accessToken')
                window.location.href = '/#/login'
                $q.notify({
                    type: 'negative',
                    message: 'Fehler beim Laden des Benutzers'
                })
            }
        }).finally(() => {
            loading.value = false
        })
    }

    fetchProfileData();

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

      const updateNotificationSettingsObjects = (user) => {
          editMe.value.notificationSettingsEmail = user.notificationSettingsEmail.map(ns => ns.id);
          editMe.value.notificationSettingsPush = user.notificationSettingsPush.map(ns => ns.id);
      }

      const updateUserObjects = (user) => {
          editMe.value.id = user.id
          editMe.value.firstname = user.firstname
          editMe.value.lastname = user.lastname
          editMe.value.nickname = user.nickname
          editMe.value.email = user.email
          editMe.value.birthdate = date.formatDate(new Date(user.birthdate), 'YYYY-MM-DD'),
          editMe.value.phone = user.phone
          editMe.value.birthdate = date.formatDate(user.birthdate, 'YYYY-MM-DD')
          emit('user-updated');
      }

      const updateNotificationSettings = () => {
          loading.value = true;
          apolloClient.mutate({
              mutation: notificationMutation,
              variables: {
                  id: editMe.value.id,
                  notificationSettings: {
                      sync: notificationSettings.value.map(ns => ({
                          id: ns.id,
                          email: editMe.value.notificationSettingsEmail.includes(ns.id),
                          push: editMe.value.notificationSettingsPush.includes(ns.id),
                      })),
                  }
              },
          }).then(({data}) => {
              updateNotificationSettingsObjects(data.updateUserNotificationSettings);
              $q.notify({
                  type: 'positive',
                  message: 'Einstellungen erfolgreich aktualisiert'
              })
          }).catch(({graphQLErrors}) => {
              if (graphQLErrors[0].extensions.category === "validation") {
                  profileValidationErrors.value = graphQLErrors[0].extensions.validation;
              }
              $q.notify({
                  type: 'negative',
                  message: 'Fehler beim Aktualisieren der Einstellungen'
              })
          }).finally(() => {
              loading.value = false;
          })
      };

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
                  birthdate: date.formatDate(new Date(editMe.value.birthdate), 'YYYY-MM-DD'),
                  phone: editMe.value.phone,
              },
          }).then(({data}) => {
              me.value = data.updateMe;
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
          updateUserObjects(me.value);
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
              fetchProfileData()
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
      notificationSettings,
      profileImageUpload,
      uploadProfileImage,
      uploadProfileImageForm,
      updateNotificationSettings,
    }
  },
}
</script>