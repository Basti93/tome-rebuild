<template>
  <q-layout view="hHh lpR fFf">

    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn dense flat round icon="menu" @click="toggleLeftDrawer"/>
        <q-toolbar-title>
          <q-avatar>
            <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg">
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


import {computed, reactive, ref} from "vue";
import {useQuasar} from "quasar";
import apolloClient from "./apollo";
import meQuery from "./queries/me.query.gql";
import configsQuery from "./queries/config.query.gql";

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
        icon: 'manage_accounts',
        label: 'Mitglieder',
        separator: false,
        to: "/users",
        show: computed(() => showMenuItem(loggedIn, true, true))
      },
      {
        icon: 'groups',
        label: 'Gruppen',
        separator: false,
        to: "/groups",
        show: computed(() => showMenuItem(loggedIn, true, true))
      },
      {
        icon: 'pin_drop',
        label: 'Orte',
        separator: false,
        to: "/locations",
        show: computed(() => showMenuItem(loggedIn, true, true))
      },
      {
        icon: 'settings',
        label: 'App-Konfiguration',
        separator: false,
        to: "/configs",
        show: computed(() => showMenuItem(loggedIn, true, true))
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



    const updateLoginState = () => {
        loggedIn.value = localStorage.getItem('accessToken') !== null;
        if (loggedIn.value) {
            apolloClient.query({query: meQuery, fetchPolicy: "network-only"}).then(({data}) => {
                me.value = data.me;
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
    updateLoginState();
    fetchConfigs();

    function showMenuItem(isLoggedIn, authNeeded, showLoggedIn) {
      if (isLoggedIn.value && showLoggedIn) {
        return true;
      } else if (!isLoggedIn.value && !authNeeded) {
        return true;
      }
      return false;
    }

    return {
      leftDrawerOpen,
      menuList,
      appName,
      me,
      loggedIn,
      fetchConfigs,
      updateLoginState,
      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value
      }
    }
  }
}
</script>