<template>
  <q-avatar v-if="user"
            v-bind="$attrs"
  >
    <q-img v-if="user.imageUrl" :src="user.imageUrl" />
    <q-img v-else="user.imageUrl" src="../../img/boy-avatar.png" />
    <q-tooltip>
        {{ user.firstname + ' ' + user.lastname }}
    </q-tooltip>
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
    props: ['id'],
    setup(props) {
        const $q = useQuasar()
        const user = ref(null);
        apolloClient.query({
            query: userQuery,
            variables: {
                id: props.id
            }
        }).then(({data}) => {
            user.value = data.user;
        }).catch(() => {
            $q.notify({
                message: 'Could not load user with id ' + props.id,
                color: 'negative',
            })
        });
        return {
            user
        }
    }
}
</script>

<style scoped>
.avatar-skeleton {
    display: inline-block;
}
</style>