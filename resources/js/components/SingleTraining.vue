<template>
    <q-card flat bordered class="single-training" v-if="training">
        <q-item>
            <q-item-section>
                <q-item-label>
                    <div class="row">
                        <div class="col-6 text-h6">{{training.name ? training.name : 'Training'}} - {{date.formatDate(training.date_start, 'ddd, DD.MM.YYYY')}}</div>
                        <div class="col-6 row justify-end">
                            <q-btn
                                v-if="isMeCoach"
                                outline
                                :color="training.status ? 'positive' : 'negative'"
                                @click="toggleStatus"
                            >
                                {{ training.status ? 'Aktiv' : 'Inaktiv' }}
                                <q-tooltip>Zum ändern klicken</q-tooltip>
                            </q-btn>
                            <q-btn v-if="isMeCoach" flat color="primary" @click="openEditDialog = true">
                                <q-icon name="edit" />
                            </q-btn>
                            <q-btn v-if="isMeCoach" flat color="primary" @click="confirmDelete">
                                <q-icon name="delete" />
                            </q-btn>
                            <q-badge v-if="attending" color="positive" outline floating>Angemeldet</q-badge>
                        </div>
                    </div>
                </q-item-label>
                <q-item-label v-if="training.description" caption>
                    <div class="text-subtitle-2 q-mb-sm">{{training.description}}</div>
                </q-item-label>
            </q-item-section>
        </q-item>
        <q-separator />
        <q-card-actions>
            <q-chip icon="event">
                {{ date.formatDate(training.date_start, 'HH:mm') }} bis {{ date.formatDate(training.date_end, 'HH:mm') }} Uhr
            </q-chip>
            <q-item-label><q-chip icon="pin_drop">{{training.location.name}}<q-tooltip anchor="bottom middle">{{training.location.address}}</q-tooltip></q-chip></q-item-label>
            <q-btn v-if="isMeAthleteInTraining && canAttend" flat color="positive" @click="updateAttendance(id, true)">
                Anmelden
                <q-tooltip>24 Stunden vor dem Training wirst du automatisch angemeldet.</q-tooltip>
            </q-btn>
            <q-btn v-if="isMeAthleteInTraining && !canAttend" flat color="negative" @click="updateAttendance(id, false)">
                Abmelden
                <q-tooltip>Bei Abmeldung 24 Stunden vor dem Training muss ein Grund angegeben werden.</q-tooltip>
            </q-btn>
        </q-card-actions>
        <q-separator />
        <q-img
                style="background-color: #f5f5f5"
                :src="training.location.imageUrl"
                height="300px"
            >
                <div class="absolute-bottom">
                    <div class="text-caption">Trainer</div>
                    <user-avatar
                        class="q-mr-sm q-mt-sm"
                        v-for="coach in training.coaches"
                        :key="coach.id"
                        :user="coach"
                    />
                    <div class="text-caption">Teilnehmer ({{training.athletes.filter(a => a.pivot.attendance).length}}/{{training.athletes.length}})</div>
                        <user-avatar
                            v-for="(athlete, index) in training.athletes"
                            size="40px"
                            :key="athlete.id"
                            :user="athlete"
                            :badge-check="athlete.pivot.attendance"
                            :badge-cancel="!athlete.pivot.attendance"
                        >
                        </user-avatar>
                </div>
                <div class="absolute-bottom-right" style="background-color: transparent; font-size: 8px;">
                    {{trainingId}}
                </div>
            </q-img>
        <q-expansion-item>
            <template v-slot:header>
                <div class="text-h6">Details</div>
            </template>
            <q-card-section>
                <q-list dense bordered padding>
                    <q-item-label header>Gruppen</q-item-label>
                    <q-item v-ripple v-for="group in training.groups" :key="group.id">
                        <q-item-section>
                            <q-item-label>{{group.name}}</q-item-label>
                        </q-item-section>
                    </q-item>
                </q-list>
                <q-list dense bordered padding>
                    <q-item-label header>Teilnehmer</q-item-label>
                    <q-item v-ripple v-for="athlete in training.athletes" :key="athlete.id">
                        <q-item-section>
                            <q-item-label>{{athlete.firstname + ' ' + athlete.lastname}}</q-item-label>
                        </q-item-section>
                    </q-item>
                </q-list>
            </q-card-section>
        </q-expansion-item>
        <q-dialog
            v-model="openEditDialog"
            maximized>
            <q-card>
                <q-card-section class="row items-center q-pb-none">
                    <div class="text-h6">Training Bearbeiten</div>
                    <q-space />
                    <q-btn icon="close" flat round dense v-close-popup />
                </q-card-section>
                <q-card-section>
                    <EditTraining :training-id="trainingId"
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
    name: "SingleTraining",
    components: {EditTraining, UserAvatar},
    props: ['trainingId'],
    emits: ["trainingDeleted", "trainingUpdated"],
    setup(props, {emit}) {
        const $q = useQuasar()
        const me = ref(null);
        const training = ref(null);
        const loading = ref(false);
        const openEditDialog = ref(false);
        const isMeCoach = computed(() => {
            if (me.value) {
                if (training.value.coaches) {
                    return training.value.coaches.find(c => c.id === me.value.id);
                }
            }
            return false;
        });

        const checkTrainingIsInFuture = () => {
            if (training.value) {
                const now = new Date();
                const start = new Date(training.value.date_start);
                const end = new Date(training.value.date_end);
                return now < start && now < end;
            }
            return false;
        }

        const isMeAthleteInTraining = computed(() => {
            if (training.value) {
                if (training.value.athletes) {
                    return training.value.athletes.find(a => a.id === me.value.id);
                }
            }
            return false;
        });

        const attending = computed(() => {
            if (training.value) {
                if (training.value.athletes) {
                    const athlete = training.value.athletes.find(a => a.id === me.value.id);
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
            await fetchMe();
            apolloClient.query({
                query: trainingQuery,
                variables: {
                    id: props.trainingId
                }
            }).then(({data}) => {
                training.value = data.training;
            }).catch((error) => {
                $q.notify({
                    message: 'Trainning mit id ' + props.trainingId + ' konnte nicht geladen werden',
                    type: 'negative'
                });
            }).finally(() => {
                loading.value = false;
            })
        }

        fetchTraining();


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
                training.value = data.updateTrainingAttendance;
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
                    id: props.trainingId,
                }
            }).then(({data}) => {
                $q.notify({
                    message: 'Training erfolgreich gelöscht',
                    type: 'positive'
                });
                emit('trainingDeleted', props.trainingId);
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
            console.log(training)
            openEditDialog.value = false
            training.value = updatedTraining;
        }

        const toggleStatus = () => {
            apolloClient.mutate({
                mutation: updateTrainingMutation,
                variables: {
                    id: training.value.id,
                    status: !training.value.status
                }
            }).then(({data}) => {
                training.value = data.updateTraining;
                $q.notify({
                    message: 'Status erfolgreich geändert',
                    type: 'positive'
                });
                emit('trainingUpdated', props.trainingId);
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
            training,
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