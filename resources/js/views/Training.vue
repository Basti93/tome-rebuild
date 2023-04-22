<template>
    <q-page>
        <div class="row " v-if="training">
            <q-card flat class="q-ma-md q-pa-md full-width full-height">
                <training-card
                    :training="training">
                </training-card>
                <q-card>
                    <q-card-section>Details</q-card-section>
                    <q-card-section>
                        <div>Erstellt am {{training.created_at}}</div>
                        <div>Aktualisiert am {{training.updated_at}}</div>
                    </q-card-section>
                </q-card>
            </q-card>

        </div>
    </q-page>
</template>

<script>
import {date, useQuasar} from "quasar";
import {ref} from "vue";
import {useRoute} from "vue-router";
import apolloClient from "../apollo";
import trainingQuery from "../queries/training.query.gql";
import TrainingCard from "../components/TrainingCard.vue";

export default {
    name: "Training",
    components: {TrainingCard},
    setup(props, {emit}) {
        const $q = useQuasar()
        const $route = useRoute()
        const training = ref(null);
        const loading = ref(false);
        const $trainingId = $route.params.id

        const fetchTraining = async () => {
            loading.value = true;
            apolloClient.query({
                query: trainingQuery,
                variables: {
                    id: $trainingId
                }
            }).then(({data}) => {
                training.value = data.training;
            }).catch((error) => {
                $q.notify({
                    message: 'Trainning mit id ' + $trainingId + ' konnte nicht geladen werden',
                    type: 'negative'
                });
            }).finally(() => {
                loading.value = false;
            })
        }

        fetchTraining();

        return {
            training,
            loading,
            date,
        }
    }
}
</script>

<style scoped>

</style>