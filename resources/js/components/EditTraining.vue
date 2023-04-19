<template>
    <q-form @submit.prevent="updateOrCreateTraining">
        <div class="row">
            <q-toggle
                    class="col-12 q-pa-sm"
                    v-model="editTraining.status"
                    label="Aktiv"
                    color="primary">
            </q-toggle>
        </div>
        <div class="row">
            <q-input
                    class="col-12 q-pa-sm"
                    type="text"
                    v-model="editTraining.name"
                    label="Name"
                    filled>
            </q-input>
        </div>
        <div class="row">
            <q-input
                    class="col-12 q-pa-sm"
                    type="textarea"
                    v-model="editTraining.description"
                    label="Beschreibung"
                    filled>
            </q-input>
        </div>
        <div class="row">
            <q-input
                    label="Tag"
                    class="col-4 col-lg-2 q-pa-sm"
                    type="date"
                    v-model="editTraining.date">
            </q-input>
            <q-input
                    label="Start"
                    class="col-4 col-lg-1 q-pa-sm"
                    type="time"
                    v-model="editTraining.start_time">
            </q-input>
            <q-input
                    label="Ende"
                    class="col-4 col-lg-1 q-pa-sm"
                    type="time"
                    v-model="editTraining.end_time">
            </q-input>
        </div>
        <div class="row">
            <q-select
                    class="col-lg-3 col-12 q-pa-sm"
                    v-model="editTraining.location"
                    label="Ort"
                    filled
                    :options="locations"
                    option-value="id"
                    option-label="name">
            </q-select>
        </div>
        <div class="row">
            <div class="col-lg-12"/>
            <q-select
                    class="col-lg-6 col-12 q-pa-sm"
                    v-model="editTraining.groups"
                    label="Gruppen"
                    filled
                    multiple
                    :options="groups"
                    option-value="id"
                    option-label="name">
            </q-select>
            <q-select
                    class="col-lg-6 col-12 q-pa-sm"
                    v-model="editTraining.coaches"
                    label="Trainer"
                    filled
                    multiple
                    dense
                    use-chips
                    :options="filterByGroups(coaches, editTraining.groups)"
                    option-value="id"
                    :option-label="opt => opt.firstname + ' ' + opt.lastname">
            </q-select>
        </div>
        <div class="row">
            <q-select
                    class="col-lg-6 col-12 q-pa-sm"
                    v-model="editTraining.athletesAttending"
                    label="Teilnehmer"
                    filled
                    multiple
                    dense
                    use-chips
                    :options="pickableAttendingAthletes"
                    option-value="id"
                    :option-label="opt => opt.firstname + ' ' + opt.lastname">
            </q-select>
            <q-select
                    class="col-lg-6 col-12 q-pa-sm"
                    v-model="editTraining.athletesNotAttending"
                    label="Absagen oder noch nicht zugesagt"
                    filled
                    multiple
                    dense
                    use-chips
                    :options="pickableNotAttendingAthletes"
                    option-value="id"
                    :option-label="opt => opt.firstname + ' ' + opt.lastname">
            </q-select>
        </div>
        <div class="row justify-center">
            <q-btn
                    class="q-ma-sm"
                    color="primary"
                    flat
                    :disable="loading"
                    @click="$emit('cancel')"
                    label="Abbrechen"/>
            <q-btn
                    class="q-ma-sm"
                    type="submit"
                    color="primary"
                    :disable="loading"
                    label="Speichern"/>
        </div>
    </q-form>
</template>

<script>
import {computed, reactive, ref} from "vue";
import apolloClient from "../apollo";
import trainingQuery from "../queries/training.query.gql";
import locationsQuery from "../queries/locations.query.gql";
import athletesQuery from "../queries/athletes.query.gql";
import coachesQuery from "../queries/coaches.query.gql";
import groupsQuery from "../queries/groups.query.gql";
import {date, useQuasar} from "quasar";
import updateTrainingsMutation from "../queries/trainingupdate.mutation.gql";
import {filterByGroups} from "../helpers/userhelper";
import {useQuery} from "@vue/apollo-composable";

export default {
    name: "EditTraining",
    props: ["trainingId"],
    emits: ["trainingUpdated", "trainingCreated", "cancel"],
    setup(props, {emit}) {
        const $q = useQuasar();
        const loading = ref(false);
        const editTraining = reactive({
            name: null,
            description: null,
            status: true,
            date: null,
            startTime: null,
            endTime: null,
            location: null,
            athletes: [],
            coaches: [],
            groups: [],
        });
        const {result: groupsResult} = useQuery(groupsQuery);
        const {result: locationsResult} = useQuery(locationsQuery);
        const {result: athletesResult} = useQuery(athletesQuery);
        const {result: coachesResult} = useQuery(coachesQuery);
        const groups = computed(() => groupsResult.value?.groups ?? [])
        const locations = computed(() => locationsResult.value?.locations ?? [])
        const athletes = computed(() => athletesResult.value?.users ?? [])
        const coaches = computed(() => coachesResult.value?.users ?? [])
        const pickableAttendingAthletes = computed(() => filterByGroups(athletes.value, editTraining.groups)
            .filter(a => editTraining.athletesNotAttending.filter(an => a.id === an.id).length === 0))
        const pickableNotAttendingAthletes = computed(() => filterByGroups(athletes.value, editTraining.groups)
            .filter(a => editTraining.athletesAttending.filter(an => a.id === an.id).length === 0))

        apolloClient.query({
            query: trainingQuery,
            variables: {
                id: props.trainingId
            }
        }).then(({data}) => {
            editTraining.name = data.training.name;
            editTraining.description = data.training.description;
            editTraining.status = data.training.status;
            editTraining.date = date.formatDate(data.training.date_start, 'YYYY-MM-DD');
            editTraining.start_time = date.formatDate(data.training.date_start, 'HH:mm');
            editTraining.end_time = date.formatDate(data.training.date_end, 'HH:mm');
            editTraining.location = data.training.location;
            editTraining.groups = data.training.groups;
            editTraining.athletesAttending = data.training.athletes.filter(a => a.pivot.attendance);
            editTraining.athletesNotAttending = data.training.athletes.filter(a => !a.pivot.attendance);
            editTraining.coaches = data.training.coaches;
        }).catch(() => {
            $q.notify({
                type: 'negative',
                message: 'Fehler beim Laden des Trainings'
            })
        })

        const updateOrCreateTraining = () => {
            loading.value = true;
            apolloClient.mutate({
                mutation: updateTrainingsMutation,
                variables: {
                    id: props.trainingId,
                    name: editTraining.name,
                    description: editTraining.description,
                    status: editTraining.status,
                    date_start: date.formatDate(new Date(editTraining.date + 'T' + editTraining.start_time), 'YYYY-MM-DD HH:mm:ss'),
                    date_end: date.formatDate(new Date(editTraining.date + 'T' + editTraining.end_time), 'YYYY-MM-DD HH:mm:ss'),
                    location: {
                        connect: editTraining.location.id
                    },
                    athletes: {
                        sync:
                            editTraining.athletesAttending.map(a => ({
                                id: a.id,
                                attendance: true,
                                role: "athlete"
                            })).concat(editTraining.athletesNotAttending.map(a => ({
                                id: a.id,
                                attendance: false,
                                role: "athlete"
                            })))
                    },
                    coaches: {
                        sync:
                            editTraining.coaches.map(c => ({
                                id: c.id,
                                role: "coach"
                            }))
                    },
                    groups: {
                        sync: editTraining.groups.map(g => g.id)
                    },
                }
            }).then(({data}) => {
                emit('trainingUpdated', data.updateTraining)
                $q.notify({
                    message: 'Training ' + data.updateTraining.id + ' aktualisiert',
                    color: 'positive'
                })
            }).catch(() => {
                $q.notify({
                    message: 'Training konnte nicht aktualisiert werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false;
            })
        }


        return {
            editTraining,
            locations,
            groups,
            athletes,
            coaches,
            loading,
            pickableAttendingAthletes,
            pickableNotAttendingAthletes,
            updateOrCreateTraining,
            filterByGroups,
            date,
        }
    }
}
</script>

<style scoped>

</style>