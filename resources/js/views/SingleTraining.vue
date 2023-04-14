<template>
    <q-card flat bordered class="single-training" v-if="training">
        <q-card-section>
            <q-img
                :src="training.location.imageUrl"
                height="250px"
            >
                <div class="absolute-bottom">
                    <div class="text-h6 q-mb-sm">{{training.name ? training.name : 'Training'}}</div>
                    <div v-if="training.description" class="text-subtitle-2 q-mb-sm">{{training.description}}</div>
                </div>
                <div class="absolute-top-right">
                    <user-avatar
                        size="50px"
                        class="q-mr-sm q-mt-sm"
                        v-for="coach in training.coaches"
                        :key="coach.id"
                        :id="coach.id"
                    />
                    <div class="text-caption absolute-left">Trainer</div>
                </div>
            </q-img>

            <q-list bordered padding>
                <q-item-label header>Gruppen</q-item-label>
                <q-item v-ripple v-for="group in training.groups" :key="group.id">
                    <q-item-section>
                        <q-item-label>{{group.name}}</q-item-label>
                    </q-item-section>
                </q-item>
            </q-list>
            <q-list bordered padding>
                <q-item-label header>Teilnehmer</q-item-label>
                <q-item v-ripple v-for="athlete in training.athletes" :key="athlete.id">
                    <q-item-section>
                        <q-item-label>{{athlete.firstname + ' ' + athlete.lastname}}</q-item-label>
                    </q-item-section>
                </q-item>
            </q-list>
        </q-card-section>

        <q-separator/>
        <q-card-actions>
            <q-btn flat round icon="event"/>
            <q-btn flat>
                {{ date.formatDate(training.date_start, 'HH:mm') }} Uhr
            </q-btn>
            <q-btn flat>
                {{ date.formatDate(training.date_end, 'HH:mm') }} Uhr
            </q-btn>
            <q-btn flat color="primary">
                Anmelden
            </q-btn>
        </q-card-actions>
    </q-card>
</template>

<script>
import apolloClient from "../apollo";
import trainingQuery from "../queries/training.query.gql";
import {ref} from "vue";
import {date, useQuasar} from "quasar";
import UserAvatar from "../components/UserAvatar.vue";

export default {
    name: "SingleTraining",
    components: {UserAvatar},
    props: ['id'],
    setup(props) {
        const $q = useQuasar()
        const training = ref(null);
        apolloClient.query({
            query: trainingQuery,
            variables: {
                id: props.id
            }
        }).then(({data}) => {
            training.value = data.training;
        }).catch((error) => {
            $q.notify({
                message: 'Trainning mit id ' + props.id + ' konnte nicht geladen werden',
                type: 'negative'
            });
        });

        return {
            training,
            date,
        }
    }
}
</script>

<style scoped>

</style>