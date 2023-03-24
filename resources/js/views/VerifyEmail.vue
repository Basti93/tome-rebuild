<template>
  <div v-if="loading">E-Mail wird verifiziert...</div>
  <div v-if="error">Beim E-Mail verifiziert ist ein Fehler aufgetreten.</div>
  <div v-if="emailVerified">E-Mail erfolgreich verifziert! Du kannst dich jetzt einloggen.</div>
</template>
<script>

import {ref} from "vue";
import {useMutation} from "@vue/apollo-composable";
import verifyEmailMutation from "../queries/verifyemail.mutation.gql";

export default {
  setup () {
    const emailVerified = ref(false);
    const { mutate: verifyEmail, loading, error, onDone } = useMutation(verifyEmailMutation)

    onDone(() => {
      emailVerified.value = true;
    })

    return {
      verifyEmail,
      loading,
      onDone,
      error,
      emailVerified
    }
  },
  data() {
    return {

    }
  },
  created() {
    this.verifyEmail({id: this.$route.query.id, hash: this.$route.query.token, expires: parseInt(this.$route.query.expires), signature: this.$route.query.signature})

  },
  methods: {

  }
}
</script>