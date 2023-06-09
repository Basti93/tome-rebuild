<template>
  <q-layout view="hHh Lpr fFf">

    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn dense flat round icon="menu" @click="toggleLeftDrawer"/>
        <q-toolbar-title>
          <q-avatar>
            <img src="icon.png" style="filter: brightness(0) invert(1);">
          </q-avatar>
          {{ appName }}
        </q-toolbar-title>

        <q-btn v-show="loggedIn" :label="me.nickname ? me.nickname : me.firstname" flat>
          <q-avatar size="26px">
            <img v-if="me.imageUrl" :src="me.imageUrl">
            <img v-else src="../img/boy-avatar.png">
          </q-avatar>
          <q-tooltip>Profil</q-tooltip>
        </q-btn>
      </q-toolbar>
    </q-header>

    <q-drawer show-if-above v-model="leftDrawerOpen" side="left" bordered>
      <q-scroll-area class="fit">
        <q-list>

          <template v-for="(menuItem, index) in menuList" :key="index">
            <q-item v-if="menuItem.show" clickable :to="menuItem.to" v-ripple>
              <q-item-section avatar>
                <q-icon :name="menuItem.icon"/>
              </q-item-section>
              <q-item-section>
                {{ menuItem.label }}
              </q-item-section>
            </q-item>
            <q-separator :key="'sep' + index" v-if="menuItem.separator"/>
          </template>

        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <router-view
              @login="updateLoginState"
              @logout="updateLoginState"
              @userUpdated="updateLoginState"
              @configChanged="fetchConfigs"
      />

    </q-page-container>

  </q-layout>
</template>
<script>
import {computed, onMounted, reactive, ref} from "vue";
import {useQuasar} from "quasar";
import apolloClient from "./apollo";
import meQuery from "./queries/me.query.gql";
import configsQuery from "./queries/config.query.gql";
import * as PusherPushNotifications from "@pusher/push-notifications-web";
import {ApolloBeamTokenProvider} from "./pusher/ApolloBeamTokenProvider";

export default {
  setup() {
    const $q = useQuasar()
    const leftDrawerOpen = ref(false)
    const appName = ref(import.meta.env.VITE_APP_NAME)
    const me = ref({})
    const loggedIn = ref(false)
    const menuList = reactive([
      {
        icon: 'home',
        label: 'Home',
        separator: false,
        to: "/home",
        show: computed(() => showMenuItem(loggedIn, true, true))
      },
      {
        icon: 'account_circle',
        label: 'Profil',
        separator: false,
        to: "/profile",
        show: computed(() => showMenuItem(loggedIn, true, true))
      },
      {
        icon: 'list',
        label: 'Trainings',
        separator: false,
        to: "/trainings",
        show: computed(() => showMenuItem(loggedIn, true, true) && hasPermission(me.value, 'edit-training'))
      },
      {
        icon: 'manage_accounts',
        label: 'Mitglieder',
        separator: false,
        to: "/users",
        show: computed(() => showMenuItem(loggedIn, true, true) && hasPermission(me.value, 'edit-user'))
      },
      {
        icon: 'groups',
        label: 'Gruppen',
        separator: false,
        to: "/groups",
        show: computed(() => showMenuItem(loggedIn, true, true) && hasPermission(me.value, 'edit-group'))
      },
      {
        icon: 'pin_drop',
        label: 'Orte',
        separator: false,
        to: "/locations",
        show: computed(() => showMenuItem(loggedIn, true, true)  && hasPermission(me.value, 'edit-location'))
      },
      {
        icon: 'settings',
        label: 'App-Konfiguration',
        separator: false,
        to: "/configs",
        show: computed(() => showMenuItem(loggedIn, true, true)  && hasPermission(me.value, 'edit-config'))
      },
      {
        icon: 'log',
        label: 'Aktivitäts Log',
        separator: false,
        to: "/activity-log",
        show: computed(() => showMenuItem(loggedIn, true, true)  && hasPermission(me.value, 'view-activity-log'))
      },
      {
        icon: 'login',
        label: 'Login',
        separator: false,
        to: "/login",
        show: computed(() => showMenuItem(loggedIn, false, false))
      },
      {
        icon: 'register',
        label: 'Registrieren',
        separator: false,
        to: "/register",
        show: computed(() => showMenuItem(loggedIn, false, false))
      },
      {
        icon: 'forgot',
        label: 'Passwort vergessen',
        separator: false,
        to: "/forgot-password",
        show: computed(() => showMenuItem(loggedIn, false, false))
      },
      {
        icon: 'logout',
        label: 'Logout',
        separator: false,
        to: "/logout",
        show: computed(() => showMenuItem(loggedIn, true, true))
      },
    ])

    $q.dark.set('auto')



    const hasPermission = (user, permission) => {
      if (user.roles) {
          for (const role of user.roles) {
              if (role.permissions) {
                  for (const p of role.permissions) {
                      if (p.name === permission) {
                          return true;
                      }
                  }
              }
          }
      }
      return false;
    }

    const updateLoginState = () => {
        loggedIn.value = localStorage.getItem('accessToken') !== null;
        if (loggedIn.value) {
            apolloClient.query({query: meQuery, fetchPolicy: "network-only"})
            .then(({data}) => {
                me.value = data.me;
                fetchConfigs();
                registerNotificationsClient();
            }).catch(() => {
                $q.notify({
                    type: 'negative',
                    message: 'Fehler beim Laden des Benutzers'
                })
            })
        } else {
            me.value = {};
        }
    }

      function removeAppItemsFromLocalStorage() {
          Object.keys(localStorage).forEach(function(key){
              if (key.startsWith('TOME_')) {
                  localStorage.removeItem(key);
              }
          })
      }

      const fetchConfigs = () => {
        apolloClient.query({query: configsQuery})
        .then(({data}) => {
            data.configs.forEach(config => {
                if (config.key === 'APP_NAME') {
                    appName.value = config.value;
                }
                removeAppItemsFromLocalStorage();
                localStorage.setItem('TOME_' + config.key, config.value);
            })
        })
    }
    const registerNotificationsClient = () =>{
        const beamsClient = new PusherPushNotifications.Client({
            instanceId: import.meta.env.VITE_PUSHER_INSTANCE_ID,
        });
        beamsClient.getUserId()
            .then(id => {
                if(id !== me.value.id) {
                    console.log('Unregistering old Beams user id:', id);
                    return beamsClient.stop();
                }
            })
        beamsClient.start()
            .then(() => beamsClient.setUserId(me.value.id, new ApolloBeamTokenProvider()))
            .then(() => {
                beamsClient.getUserId().then(id => {
                    console.log('Successfully registered with Beams. My Beams user id:', id);
                });

            })
            .catch(console.error);

    }

    function showMenuItem(isLoggedIn, authNeeded, showLoggedIn) {
      if (isLoggedIn.value && showLoggedIn) {
        return true;
      } else if (!isLoggedIn.value && !authNeeded) {
        return true;
      }
      return false;
    }

    onMounted(() => {
      updateLoginState();
    })

    return {
      leftDrawerOpen,
      menuList,
      appName,
      me,
      loggedIn,
      fetchConfigs,
      updateLoginState,
      hasPermission,
      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value
      }
    }
  }
}
</script>