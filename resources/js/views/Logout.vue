<template>
  Logout
</template>
<script>

import router from "../router";
import {mapActions} from "vuex";

export default {
  data() {
    return {}
  },
  methods: {
    ...mapActions('auth', [
      'doLogout'
    ]),
  },
  created () {
    this.doLogout();
    router.push("/home")
  }
}
</script>