<template>
  Logout
</template>
<script >

import { useRouter } from "vue-router";
import {onMounted} from "vue";
import apolloClient from "../apollo";
import logoutMutation from "../queries/logout.mutation.gql";
import {useQuasar} from "quasar";


export default {
  emits: ["logout"],
  setup(props, { emit }) {
    const $q = useQuasar();
    const router = useRouter();
    onMounted(() => {
        apolloClient.mutate({
            mutation: logoutMutation
        }).then(() => {
            localStorage.removeItem('accessToken');
            apolloClient.cache.reset()
            $q.notify({
                message: 'Erfolgreich ausgeloggt!',
                color: 'positive'})
            emit('logout');
            router.push("/login")
        }).catch(() => {
            $q.notify({
                message: 'Fehler beim Logout!',
                color: 'negative'})
        })
    })


  }
}
</script>