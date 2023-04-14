<template>
  <q-avatar v-if="user"
            v-bind="$attrs"
  >
    <q-img v-if="user.imageUrl" :src="user.imageUrl" />
    <q-img v-else src="../../img/boy-avatar.png" />
    <q-tooltip>
        <div>{{badgeCancel ? 'Absage ' : ''}}{{badgeCheck ? 'Zusage' : ''}}</div>
        <div>{{ user.firstname + ' ' + user.lastname }}</div>
    </q-tooltip>
      <q-badge v-if="badgeCheck" style="top: unset; right: unset;" class="absolute-bottom-left" floating rounded color="positive">
          <q-icon name="check" />
      </q-badge>
      <q-badge v-if="badgeCancel" style="top: unset; right: unset;" class="absolute-bottom-left" floating rounded color="negative">
          <q-icon name="cancel" />
      </q-badge>
  </q-avatar>
  <q-skeleton v-else type="QAvatar" class="avatar-skeleton" />
</template>

<script>
import {useQuasar} from "quasar";
import {ref} from "vue";
import apolloClient from "../apollo";
import userQuery from "../queries/user.query.gql";
export default {
    name: "UserAvatar",
    props: ['user', 'badgeCheck', 'badgeCancel'],
}
</script>

<style scoped>
.avatar-skeleton {
    display: inline-block;
}
</style>