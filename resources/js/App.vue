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

        <q-btn flat :label="me.nickname ? me.nickname : me.firstname" v-if="me">

          <q-avatar size="26px">
            <img src="https://cdn.quasar.dev/img/boy-avatar.png">
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
      <router-view/>
    </q-page-container>

  </q-layout>
</template>
<script>


import {useStore} from "vuex";
import {computed, reactive, ref} from "vue";

export default {
  setup() {
    const leftDrawerOpen = ref(false)
    const appName = import.meta.env.VITE_APP_NAME
    const store = useStore();
    const me = computed(() => store.state.auth.user)
    const loggedIn = computed(() => store.state.auth.accessToken && store.state.auth.accessToken.length > 0);
    const menuList = reactive([
      {
        icon: 'home',
        label: 'Home',
        separator: false,
        to: "/home",
        show: computed(() => showMenuItem(loggedIn, true, true))
      },
      {
        icon: 'profile',
        label: 'Profil',
        separator: false,
        to: "/profile",
        show: computed(() => showMenuItem(loggedIn, true, true))
      },
      {
        icon: 'users',
        label: 'Benutzer',
        separator: false,
        to: "/users",
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

    store.dispatch('auth/fetchAccessToken');
    store.dispatch('auth/getMe');

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
      toggleLeftDrawer() {
        leftDrawerOpen.value = !leftDrawerOpen.value
      }
    }
  }
}
</script>