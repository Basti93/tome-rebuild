<template>
    <q-card flat bordered class="training-card" v-if="trainingData">
        <q-item>
            <q-item-section>
                <q-item-label>
                    <div class="row">
                        <div class="col-6 text-h6">{{trainingData.name ? trainingData.name : 'Training'}} - {{date.formatDate(trainingData.date_start, 'ddd, DD.MM.YYYY')}}</div>
                        <div class="col-6 row justify-end">
                            <q-btn
                                v-if="isMeCoach"
                                outline
                                :color="trainingData.status ? 'positive' : 'negative'"
                                @click="toggleStatus"
                            >
                                {{ trainingData.status ? 'Aktiv' : 'Inaktiv' }}
                                <q-tooltip>Zum ändern klicken</q-tooltip>
                            </q-btn>
                            <q-btn v-if="isMeCoach" flat color="primary" @click="openEditDialog = true">
                                <q-icon name="edit" />
                            </q-btn>
                            <q-btn v-if="isMeCoach" flat color="primary" @click="confirmDelete">
                                <q-icon name="delete" />
                            </q-btn>
                            <q-btn outline color="primary" :to="'/training/' + trainingData.id">
                                <q-icon name="pageview" />
                            </q-btn>
                            <q-badge v-if="attending" color="positive" outline floating>Angemeldet</q-badge>
                        </div>
                    </div>
                </q-item-label>
                <q-item-label v-if="trainingData.description" caption>
                    <div class="text-subtitle-2 q-mb-sm">{{trainingData.description}}</div>
                </q-item-label>
            </q-item-section>
        </q-item>
        <q-separator />
        <q-card-actions>
            <q-chip icon="event">
                {{ date.formatDate(trainingData.date_start, 'HH:mm') }} bis {{ date.formatDate(trainingData.date_end, 'HH:mm') }} Uhr
            </q-chip>
            <q-item-label><q-chip icon="pin_drop">{{trainingData.location.name}}<q-tooltip anchor="bottom middle">{{trainingData.location.address}}</q-tooltip></q-chip></q-item-label>
            <q-btn v-if="isMeAthleteInTraining && canAttend" flat color="positive" @click="updateAttendance(trainingData.id, true)">
                Anmelden
                <q-tooltip>24 Stunden vor dem Training wirst du automatisch angemeldet.</q-tooltip>
            </q-btn>
            <q-btn v-if="isMeAthleteInTraining && !canAttend" flat color="negative" @click="updateAttendance(trainingData.id, false)">
                Abmelden
                <q-tooltip>Bei Abmeldung 24 Stunden vor dem Training muss ein Grund angegeben werden.</q-tooltip>
            </q-btn>
        </q-card-actions>
        <q-separator />
        <q-img
                style="background-color: #f5f5f5"
                :src="trainingData.location.imageUrl"
                height="300px"
            >
            <div class="absolute-bottom">
                <div class="text-caption">Trainer</div>
                <user-avatar
                    class="q-mr-sm q-mt-sm"
                    v-for="coach in trainingData.coaches"
                    :key="coach.id"
                    :user="coach"
                />
                <div class="text-caption">Teilnehmer ({{trainingData.athletes.filter(a => a.pivot.attendance).length}}/{{trainingData.athletes.length}})</div>
                    <user-avatar
                        v-for="(athlete, index) in trainingData.athletes"
                        size="40px"
                        :key="athlete.id"
                        :user="athlete"
                        :badge-check="athlete.pivot.attendance"
                        :badge-cancel="!athlete.pivot.attendance"
                    >
                    </user-avatar>
            </div>
            <div class="absolute-bottom-right" style="background-color: transparent; font-size: 8px;">
                {{trainingData.id}}
            </div>
        </q-img>
        <q-dialog
            v-model="openEditDialog"
            maximized>
            <q-card>
                <q-card-section class="row items-center q-pb-none">
                    <div class="text-h6">Training {{trainingData.id}} bearbeiten</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup />
                </q-card-section>
                <q-card-section>
                    <EditTraining :training-id="trainingData.id"
                                  @cancel="openEditDialog = false"
                                  @training-updated="trainingUpdated">

                    </EditTraining>
                </q-card-section>
            </q-card>
        </q-dialog>
    </q-card>

</template>

<script>
import apolloClient from "../apollo";
import trainingQuery from "../queries/training.query.gql";
import attendTrainingMutation from "../queries/updatetrainingattendance.mutation.gql";
import updateTrainingMutation from "../queries/trainingupdate.mutation.gql";
import deleteTrainingMutation from "../queries/trainingdelete.mutation.gql";
import {computed, ref} from "vue";
import {date, useQuasar} from "quasar";
import UserAvatar from "./UserAvatar.vue";
import meQuery from "../queries/me.query.gql";
import EditTraining from "./EditTraining.vue";

export default {
    name: "TrainingCard",
    components: {EditTraining, UserAvatar},
    props: ['trainingId', 'training'],
    emits: ["trainingDeleted", "trainingUpdated"],
    setup(props, {emit}) {
        const $q = useQuasar()
        const me = ref(null);
        const trainingData = ref(null);
        const loading = ref(false);
        const openEditDialog = ref(false);
        const isMeCoach = computed(() => {
            if (me.value) {
                if (trainingData.value.coaches) {
                    return trainingData.value.coaches.some(c => c.id === me.value.id);
                }
            }
            return false;
        });

        const checkTrainingIsInFuture = () => {
            if (trainingData.value) {
                const now = new Date();
                const start = new Date(trainingData.value.date_start);
                const end = new Date(trainingData.value.date_end);
                return now < start && now < end;
            }
            return false;
        }

        const isMeAthleteInTraining = computed(() => {
            if (trainingData.value) {
                if (trainingData.value.athletes) {
                    return trainingData.value.athletes.some(a => a.id === me.value.id);
                }
            }
            return false;
        });

        const attending = computed(() => {
            if (trainingData.value) {
                if (trainingData.value.athletes) {
                    const athlete = trainingData.value.athletes.find(a => a.id === me.value.id);
                    if (athlete) {
                        return athlete.pivot.attendance != null ? athlete.pivot.attendance : false;
                    }
                }
            }
            return false;
        });

        const canAttend = computed(() => {
            return checkTrainingIsInFuture() && !attending.value
        });


        const fetchMe = async () => {
            await apolloClient.query({query: meQuery})
            .then(({data}) => {
                me.value = data.me;
            }).catch(() => {
                $q.notify({
                    type: 'negative',
                    message: 'Fehler beim Laden des Benutzers'
                })
            })
        }
        const fetchTraining = async () => {
            loading.value = true;
            apolloClient.query({
                query: trainingQuery,
                variables: {
                    id: props.trainingId
                }
            }).then(({data}) => {
                trainingData.value = data.training;
            }).catch((error) => {
                $q.notify({
                    message: 'Trainning mit id ' + props.trainingId + ' konnte nicht geladen werden',
                    type: 'negative'
                });
            }).finally(() => {
                loading.value = false;
            })
        }

        fetchMe().then(() => {
                if (!props.training) {
                    fetchTraining();
                } else {
                    trainingData.value = props.training;
                }
            }
        );


        const updateAttendance = (id, attend) => {
            loading.value = true;
            const attendString = attend ? 'angemeldet' : 'abgemeldet';
            apolloClient.mutate({
                mutation: attendTrainingMutation,
                variables: {
                    id: id,
                    attend: attend,
                }
            }).then(({data}) => {
                trainingData.value = data.updateTrainingAttendance;
                $q.notify({
                    message: 'Du hast dich erfolgreich ' + attendString,
                    type: 'positive'
                });
            }).catch((error) => {
                $q.notify({
                    message: 'Du konntest nicht ' + attendString + ' werden',
                    type: 'negative'
                });
            }).finally(() => {
                loading.value = false;
            })
        }

        const deleteTraining = () => {
            loading.value = true;
            apolloClient.mutate({
                mutation: deleteTrainingMutation,
                variables: {
                    id: trainingData.id,
                }
            }).then(({data}) => {
                $q.notify({
                    message: 'Training erfolgreich gelöscht',
                    type: 'positive'
                });
                emit('trainingDeleted', trainingData.id);
            }).catch((error) => {
                $q.notify({
                    message: 'Training konnte nicht gelöscht werden',
                    type: 'negative'
                });
            }).finally(() => {
                loading.value = false;
            })
        };

        const confirmDelete = () => {
            $q.dialog({
                title: 'Training löschen',
                message: 'Möchtest du das Training wirklich löschen?',
                cancel: true,
                persistent: true
            }).onOk(() => {
                deleteTraining();
            })
        }

        const trainingUpdated = (updatedTraining) => {
            console.log(trainingData)
            openEditDialog.value = false
            trainingData.value = updatedTraining;
        }

        const toggleStatus = () => {
            apolloClient.mutate({
                mutation: updateTrainingMutation,
                variables: {
                    id: trainingData.value.id,
                    status: !trainingData.value.status
                }
            }).then(({data}) => {
                trainingData.value = data.updateTraining;
                $q.notify({
                    message: 'Status erfolgreich geändert',
                    type: 'positive'
                });
                emit('trainingUpdated', trainingData.id);
            }).catch((error) => {
                $q.notify({
                    message: 'Status konnte nicht geändert werden',
                    type: 'negative'
                });
            }).finally(() => {
                loading.value = false;
            })
        }

        const editTraining = () => {

        }

        return {
            canAttend,
            trainingData,
            updateAttendance,
            date,
            attending,
            me,
            isMeCoach,
            isMeAthleteInTraining,
            confirmDelete,
            toggleStatus,
            editTraining,
            openEditDialog,
            trainingUpdated,
        }
    }
}
</script>

<style scoped>

</style>