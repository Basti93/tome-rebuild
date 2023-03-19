<template>
  <v-card>
    <v-layout>
      <v-app-bar title="Trainingsplanung"></v-app-bar>

      <v-navigation-drawer>
        <v-list>
          <v-list-item v-for="item in menuItems"
                       :key="item.title"
                       :to="item.link"
                       :title="item.title"></v-list-item>
        </v-list>
      </v-navigation-drawer>

      <v-main style="min-height: 300px;"><router-view></router-view></v-main>
    </v-layout>
  </v-card>
</template>
<script>

export default {
  data() {
    return {
      menuItems: [
        {icon: 'home', title: 'Home', link: '/home'},
      ]
    }
  }
}
</script>