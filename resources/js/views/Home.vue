<template>
    <q-page>
        <div class="row q-ma-md">
            <q-card class="col-12" v-if="me">
                <q-card-section>
                    <div class="text-h6">Herzlich Willkommen {{me.firstname}}</div>
                    <div class="text-subtitle-1">Hier findest du alle deine kommenden Trainings</div>
                </q-card-section>
                <q-tabs
                    v-if="meIsCoach && meIsAthlete"
                    v-model="trainingsTab"
                    dense
                    class="text-grey"
                    active-color="primary"
                    indicator-color="primary"
                    align="justify"
                >
                    <q-tab name="coachTrainings" label="Als Trainer" />
                    <q-tab v-if="upcomingTrainingsCoach.length > 0" name="athleteTrainings" label="Als Sportler" />
                </q-tabs>

                <q-separator />

                <q-tab-panels v-model="trainingsTab" animated>
                    <q-tab-panel name="coachTrainings" class="row justify-center">
                        <div class="col-12 text-h6 q-pt-md" style="text-align:center;">{{upcomingTrainingsCoach.length}} Trainings</div>
                        <single-training
                            class="col-12 col-lg-5 q-ma-md"
                            v-for="training in upcomingTrainingsCoach"
                            v-on:trainingDeleted="trainingDeleted"
                            :key="training.id"
                            :trainingId="training.id"/>
                    </q-tab-panel>

                    <q-tab-panel name="athleteTrainings" class="q-pa-none row justify-center">
                        <div class="col-12 text-h6 q-pt-md" style="text-align:center;">{{upcomingTrainingsAthlete.length}} Trainings</div>
                        <single-training
                            class="col-12 col-lg-5 q-ma-md"
                            v-for="training in upcomingTrainingsAthlete"
                            :key="training.id"
                            :trainingId="training.id"/>
                    </q-tab-panel>
                </q-tab-panels>

                <q-card-section class="row justify-center">
                    <div class="text-h6">Alte Trainings laden</div>
                </q-card-section>
            </q-card>
        </div>
    </q-page>
</template>
<script>
import SingleTraining from "../components/SingleTraining.vue";
import apolloClient from "../apollo";
import meQuery from "../queries/me.query.gql";
import {computed, ref} from "vue";
import {useQuasar} from "quasar";

export default {
    components: {SingleTraining},

    setup() {
        const $q = useQuasar();
        const trainingsTab = ref('athletesTrainings');
        const upcomingTrainingsAthlete = ref(null);
        const upcomingTrainingsCoach = ref(null);
        const me = ref(null);
        const meIsCoach = computed(() => {
            if (me.value) {
                return me.value.roles.find(r => r.name === 'coach');
            }
            return false;
        });
        const meIsAthlete = computed(() => {
            if (me.value) {
                return me.value.roles.find(r => r.name === 'athlete');
            }
            return false;
        });
        const fetchUpcomingTrainings = async () => {
            await apolloClient.query({query: meQuery})
                .then(({data}) => {
                    me.value = data.me;
                    if (meIsCoach.value) {
                        trainingsTab.value = 'coachTrainings';
                    }
                    upcomingTrainingsAthlete.value = data.me.upcomingTrainingsAsAthlete;
                    upcomingTrainingsCoach.value = data.me.upcomingTrainingsAsCoach;
                }).catch(() => {
                    $q.notify({
                        type: 'negative',
                        message: 'Fehler beim Laden der kommenden Trainings'
                    })
                })
        }

        fetchUpcomingTrainings();

        const trainingDeleted = (id) => {
            upcomingTrainingsCoach.value = upcomingTrainingsCoach.value.filter(t => t.id != id);
        }

        return {
            upcomingTrainingsAthlete,
            upcomingTrainingsCoach,
            me,
            trainingsTab,
            meIsCoach,
            meIsAthlete,
            trainingDeleted,
        }
    }
}
</script>