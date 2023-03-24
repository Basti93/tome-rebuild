<template>
  <h1>Users</h1>
  <v-list>
    <v-list-item v-for="user in users" :key="user.id">{{user.name}}</v-list-item>
  </v-list>
</template>
<script>
export default {
  data() {
    return {
      users: Array
    }
  },
}
</script>