<template>
  Logout
</template>
<script >

import { useStore } from "vuex";
import { useRouter } from "vue-router";
import {onMounted} from "vue";


export default {
  setup() {
    const store = useStore();
    const router = useRouter();
    onMounted(() => {
      store.dispatch('auth/doLogout');
      router.push("/home")
    })


  }
}
</script>