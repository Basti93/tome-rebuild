<template>
    <q-page>
        <div class="row q-ma-md">
            <q-table
                    class="col-12 full-height full-width"
                    ref="tableRef"
                    :visible-columns="visibleColumns"
                    title="Trainings"
                    :rows="rows"
                    :columns="columns"
                    row-key="id"
                    :rows-per-page-options="[10, 25, 50, 100]"
                    v-model:pagination="pagination"
                    :loading="loading"
                    :filter="filter"
                    wrap-cells
                    @request="onRequest"
                    dense
                    binary-state-sort
            >
                <template v-slot:top>
                    <div class="row full-width">
                        <div class="col">
                            <p class="text-h4">Trainings</p>
                            <p class="text-subtitle-1">Zum Editieren auf die Zeilen klicken</p>
                        </div>
                        <div class="col self-center">
                            <div class="row">
                                <q-select
                                        v-model="visibleColumns"
                                        multiple
                                        outlined
                                        dense
                                        options-dense
                                        class="q-pr-md col"
                                        label="Spalten zum Anzeigen"
                                        :display-value="$q.lang.table.columns"
                                        emit-value
                                        map-options
                                        :options="columns.filter(c => c.name !== 'id' && c.name !== 'actions')"
                                        option-value="name"
                                        options-cover
                                        style="min-width: 200px"
                                />
                                <q-select
                                        class="col"
                                        v-model="filterCol"
                                        @update:model-value="tableRef.requestServerInteraction()"
                                        dense
                                        outlined
                                        options-dense
                                        emit-value
                                        map-options
                                        :options="columns.filter(c => c.name === 'name' || c.name === 'description')"
                                        option-value="name"
                                        label="Spalte zum Suchen"
                                ></q-select>
                                <q-input class="col q-pl-md" label="Suche" filled dense debounce="300" color="primary"
                                         v-model="filter">
                                    <template v-slot:append>
                                        <q-icon name="search"/>
                                    </template>
                                </q-input>
                            </div>
                        </div>
                    </div>
                    <q-expansion-item
                            class="full-width shadow-1 overflow-hidden"
                            expand-separator
                            icon="filter_list"
                            style="border-radius: 30px"
                            header-class="bg-secondary text-white"
                            expand-icon-class="text-white"
                            label="Filtereinstellungen"
                            caption="Nach Gruppe, Serienterminen und Rollen filtern"
                    >
                        <q-card flat bordered>
                            <div class="row q-ma-lg-lg">
                                <div class="col-12 col-lg-5 q-pa-md">
                                    <q-select
                                            label="Gruppen"
                                            v-model="groupsSelection"
                                            @update:model-value="pagination.page = 1; tableRef.requestServerInteraction()"
                                            :options="groups"
                                            option-value="id"
                                            emit-value
                                            map-options
                                            :option-label="opt => opt.name"
                                            multiple
                                            clearable
                                            filled>
                                    </q-select>
                                </div>
                                <div class="col-12 col-lg-4 q-pa-md">
                                    <q-select
                                            label="Trainer"
                                            v-model="coachesSelection"
                                            @update:model-value="pagination.page = 1; tableRef.requestServerInteraction()"
                                            :options="coaches"
                                            option-value="id"
                                            emit-value
                                            map-options
                                            :option-label="opt => opt.firstname + ' ' + opt.lastname"
                                            multiple
                                            clearable
                                            filled>
                                    </q-select>
                                </div>
                                <div class="col-12 col-lg-4 q-pl-md q-pr-md q-pt-sm">
                                    <q-select
                                            label="Sportler"
                                            v-model="athletesSelection"
                                            @update:model-value="pagination.page = 1; tableRef.requestServerInteraction()"
                                            :options="athletes"
                                            option-value="id"
                                            emit-value
                                            map-options
                                            :option-label="opt => opt.firstname + ' ' + opt.lastname"
                                            multiple
                                            clearable
                                            filled>
                                    </q-select>
                                </div>
                            </div>
                        </q-card>
                    </q-expansion-item>
                </template>

                <template v-slot:body="props">
                    <q-tr :props="props" :style="props.row.date_start > Date.now() ? 'text-color:blue' : ''">
                        <q-td key="id" :props="props">{{ props.row.id }}</q-td>
                        <q-td key="name" :props="props">{{ props.row.name }}
                            <q-popup-edit :model-value="props.row.name" v-slot="scope"
                                          @save="(value) => updateTraining(props.row.id, 'name', value)">
                                <q-input v-model="scope.value" dense autofocus @keyup.enter="scope.set">
                                    <template v-slot:after>
                                        <q-btn
                                                flat dense color="negative" icon="cancel"
                                                @click.stop.prevent="scope.cancel"
                                        />

                                        <q-btn
                                                flat dense color="positive" icon="check_circle"
                                                @click.stop.prevent="scope.set"
                                        />
                                    </template>
                                </q-input>
                            </q-popup-edit>
                        </q-td>
                        <q-td key="description" :props="props">
                            {{
                            props.row.description.length > 20 ? props.row.description.substring(0, 20) + '...' : props.row.description
                            }}
                            <q-popup-edit :model-value="props.row.description" v-slot="scope"
                                          @save="(value) => updateTraining(props.row.id, 'description', value)">
                                <q-input v-model="scope.value"
                                         dense autofocus
                                         type="textarea"
                                         @keyup.enter="scope.set"
                                         :rules="[val => val && val.length > 0 || 'Feld darf nicht leer sein']">
                                    <template v-slot:after>
                                        <q-btn
                                                flat dense color="negative" icon="cancel"
                                                @click.stop.prevent="scope.cancel"
                                        />

                                        <q-btn
                                                flat dense color="positive" icon="check_circle"
                                                @click.stop.prevent="scope.set"
                                        />
                                    </template>
                                </q-input>
                            </q-popup-edit>
                        </q-td>
                        <q-td key="status" :props="props">
                            <q-btn v-if="props.row.status" @click="updateTraining(props.row.id, 'status', false)"
                                   size="xs" outline round icon="check" color="green"/>
                            <q-btn v-if="!props.row.status" @click="updateTraining(props.row.id, 'status', true)"
                                   size="xs" outline round icon="clear" color="red"/>
                        </q-td>
                        <q-td key="date_start" :props="props">
                            {{ date.formatDate(props.row.date_start, 'DD.MM.YYYY HH:mm') }}
                            <q-popup-edit :model-value="props.row.date_start" v-slot="scope"
                                          @save="(value) => updateTraining(props.row.id, 'date_start', date.formatDate(value, 'YYYY-MM-DD HH:mm:ss'))">
                                <div class="row">
                                    <q-date v-model="scope.value" mask="YYYY-MM-DD HH:mm"/>
                                    <q-time v-model="scope.value" mask="YYYY-MM-DD HH:mm"/>
                                </div>
                                <div class="row">
                                    <q-btn
                                            label="Abbrechen"
                                            flat dense color="negative" icon="cancel"
                                            @click.stop.prevent="scope.cancel"
                                    />

                                    <q-btn
                                            label="Speichern"
                                            flat dense color="positive" icon="check_circle"
                                            @click.stop.prevent="scope.set"
                                    />
                                </div>
                            </q-popup-edit>
                        </q-td>
                        <q-td key="date_end" :props="props">{{
                            date.formatDate(props.row.date_end, 'DD.MM.YYYY HH:mm')
                            }}
                            <q-popup-edit :model-value="props.row.date_end" v-slot="scope"
                                          @save="(value) => updateTraining(props.row.id, 'date_end', date.formatDate(value, 'YYYY-MM-DD HH:mm:ss'))">
                                <div class="row">
                                    <q-date v-model="scope.value" mask="YYYY-MM-DD HH:mm"/>
                                    <q-time v-model="scope.value" mask="YYYY-MM-DD HH:mm"/>
                                </div>
                                <div class="row">
                                    <q-btn
                                            label="Abbrechen"
                                            flat dense color="negative" icon="cancel"
                                            @click.stop.prevent="scope.cancel"
                                    />

                                    <q-btn
                                            label="Speichern"
                                            flat dense color="positive" icon="check_circle"
                                            @click.stop.prevent="scope.set"
                                    />
                                </div>
                            </q-popup-edit>
                        </q-td>
                        <q-td key="location" :props="props">
                            {{ props.row.location.name }}
                            <q-popup-edit :model-value="props.row.location" v-slot="scope"
                                          @save="(value) => updateTraining(props.row.id, 'location', value.id)">
                                <q-select v-model="scope.value" dense autofocus @keyup.enter="scope.set"
                                          :options="locations" option-value="id" option-label="name">
                                    <template v-slot:after>
                                        <q-btn
                                                flat dense color="negative" icon="cancel"
                                                @click.stop.prevent="scope.cancel"
                                        />

                                        <q-btn
                                                flat dense color="positive" icon="check_circle"
                                                @click.stop.prevent="scope.set"
                                        />
                                    </template>
                                </q-select>
                            </q-popup-edit>
                        </q-td>


                        <q-td key="groups" :props="props">
                            {{ props.row.groups.map(g => g.name).toString() }}
                            <q-popup-edit :model-value="props.row.groups" v-slot="scope"
                                          @save="(value) => updateTraining(props.row.id, 'groups', value.map(g => g.id))">
                                <q-select style="min-width: 300px;" v-model="scope.value" multiple dense use-chips
                                          autofocus @keyup.enter="scope.set" :options="groups" option-value="id"
                                          option-label="name">
                                    <template v-slot:after>
                                        <q-btn
                                                flat dense color="negative" icon="cancel"
                                                @click.stop.prevent="scope.cancel"
                                        />

                                        <q-btn
                                                flat dense color="positive" icon="check_circle"
                                                @click.stop.prevent="scope.set"
                                        />
                                    </template>
                                </q-select>
                            </q-popup-edit>
                        </q-td>
                        <q-td key="athletes" :props="props">
                            {{ props.row.athletes ? props.row.athletes.length : 0 }}
                        </q-td>
                        <q-td key="coaches" :props="props">
                            {{ props.row.coaches.map(u => u.firstname + ' ' + u.lastname).toString() }}
                            <q-popup-edit :model-value="props.row.coaches" v-slot="scope"
                                          @save="(value) => updateTraining(props.row.id, 'coaches', value.map(g => g.id))">
                                <q-select style="min-width: 300px;" label="Trainer"
                                          hint="Gefiltert nach Trainings-Gruppen" v-model="scope.value" multiple dense
                                          use-chips autofocus @keyup.enter="scope.set"
                                          :options="filterByGroups(coaches, props.row.groups)" option-value="id"
                                          :option-label="(u) => u.firstname + ' ' + u.lastname">
                                    <template v-slot:after>
                                        <q-btn
                                                flat dense color="negative" icon="cancel"
                                                @click.stop.prevent="scope.cancel"
                                        />

                                        <q-btn
                                                flat dense color="positive" icon="check_circle"
                                                @click.stop.prevent="scope.set"
                                        />
                                    </template>
                                </q-select>
                            </q-popup-edit>
                        </q-td>
                        <q-td key="actions" :props="props">
                            <q-btn round flat icon="more_vert">
                                <q-menu auto-close v-ripple>
                                    <q-item clickable @click="editTraining(props.row)">
                                        <q-item-section avatar>
                                            <q-icon name="edit"/>
                                        </q-item-section>
                                        <q-item-section>Bearbeiten</q-item-section>
                                    </q-item>
                                    <q-item clickable @click="confirmDelete(props.row)">
                                        <q-item-section avatar>
                                            <q-icon name="person_remove"/>
                                        </q-item-section>
                                        <q-item-section>Löschen</q-item-section>
                                    </q-item>
                                </q-menu>
                            </q-btn>
                        </q-td>
                    </q-tr>
                </template>

            </q-table>
        </div>
        <q-dialog
                v-model="openEditDialog"
                maximized>
            <q-card>
                <q-card-section class="row items-center q-pb-none">
                    <div class="text-h6">Training {{ editTrainingId ? 'Bearbeiten' : 'Anlegen' }}</div>
                    <q-space/>
                    <q-btn icon="close" flat round dense v-close-popup/>
                </q-card-section>
                <q-card-section>
                    <EditTraining
                            :training-id="editTrainingId"
                            @cancel="openEditDialog = false"
                            @training-updated="trainingUpdated">

                    </EditTraining>
                </q-card-section>
            </q-card>
        </q-dialog>
    </q-page>
</template>
<script>

import trainingsPaginationQuery from "../queries/trainingpagination.query.gql";
import rolesQuery from "../queries/roles.query.gql";
import locationsQuery from "../queries/locations.query.gql";
import athletesQuery from "../queries/athletes.query.gql";
import coachesQuery from "../queries/coaches.query.gql";
import groupsQuery from "../queries/groups.query.gql";
import deleteTrainingsMutation from "../queries/trainingdelete.mutation.gql";
import updateTrainingsMutation from "../queries/trainingupdate.mutation.gql";
import {computed, onMounted, ref} from "vue";
import apolloClient from "../apollo";
import {date} from 'quasar'
import {useQuasar} from 'quasar'
import {useQuery} from "@vue/apollo-composable";
import {filterByGroups} from "../helpers/userhelper"
import EditTraining from "../components/EditTraining.vue";

export default {
    components: {EditTraining},
    setup() {
        const $q = useQuasar()
        const tableRef = ref()
        const visibleColumns = ref(['id', 'name', 'date_start', 'date_end', 'status', 'groups', 'athletes', 'coaches', 'actions']);
        const filter = ref('')
        const filterCol = ref('name')
        const groupsSelection = ref(null);
        const coachesSelection = ref(null);
        const athletesSelection = ref(null);
        const rows = ref([])
        const loading = ref(false)
        const openEditDialog = ref(false)
        const editTrainingId = ref(null)
        const pagination = ref({
            sortBy: 'id',
            descending: true,
            page: 1,
            rowsPerPage: 10,
            rowsNumber: 10
        })
        const columns = [
            {name: 'id', required: true, label: 'ID', align: 'left', field: 'id', sortable: true},
            {name: 'name', align: 'center', label: 'Name', field: 'name', sortable: true},
            {name: 'description', align: 'center', label: 'Beschreibung', field: 'description', sortable: false},
            {name: 'status', align: 'center', label: 'Aktiv', field: 'status', sortable: true},
            {name: 'date_start', align: 'center', label: 'Start', field: 'date_start', sortable: true},
            {name: 'date_end', align: 'center', label: 'Ende', field: 'date_end', sortable: true},
            {name: 'location', align: 'center', label: 'Ort', field: 'location', sortable: false,},
            {name: 'groups', align: 'center', label: 'Gruppen', field: 'groups', sortable: false,},
            {
                name: 'athletes',
                align: 'center',
                label: 'Zugewiesene Sportler*innen',
                field: 'athletes',
                sortable: true,
            },
            {name: 'coaches', align: 'center', label: 'Trainer*innen', field: 'coaches', sortable: false,},
            {name: 'actions', align: 'center', label: 'Aktionen', field: '', sortable: false,},
        ]
        const {result: rolesResult} = useQuery(rolesQuery);
        const {result: groupsResult} = useQuery(groupsQuery);
        const {result: locationsResult} = useQuery(locationsQuery);
        const {result: athletesResult} = useQuery(athletesQuery);
        const {result: coachesResult} = useQuery(coachesQuery);
        const roles = computed(() => rolesResult.value?.roles ?? [])
        const groups = computed(() => groupsResult.value?.groups ?? [])
        const locations = computed(() => locationsResult.value?.locations ?? [])
        const athletes = computed(() => athletesResult.value?.users ?? [])
        const coaches = computed(() => coachesResult.value?.users ?? [])

        const addToWhere = (variables, relation, value) => {
            if (!variables.where) {
                variables.where = {};
                variables.where.AND = [];
            }
            variables.where.AND.push({
                HAS: {
                    relation: relation,
                    condition: {
                        column: 'ID',
                        operator: 'IN',
                        value: value
                    }
                }
            });
        }

        const onRequest = (props) => {
            loading.value = true
            const {page, rowsPerPage, sortBy, descending} = props.pagination
            const filter = props.filter
            const variables = {
                first: rowsPerPage,
                page: page,
            };
            if (sortBy) {
                if (sortBy == 'athletes') {
                    variables.orderBy = [
                        {
                            athletes: {aggregate: 'COUNT'},
                            order: descending ? 'DESC' : 'ASC',
                        }
                    ]
                } else {
                    variables.orderBy = [
                        {
                            column: sortBy.toUpperCase(),
                            order: descending ? 'DESC' : 'ASC',
                        }
                    ]
                }

            }
            if (filter) {
                variables[`${filterCol.value}`] = "%" + filter + "%";
            }

            if (athletesSelection.value && athletesSelection.value.length > 0) {
                addToWhere(variables, 'athletes', athletesSelection.value);
            }
            if (coachesSelection.value && coachesSelection.value.length > 0) {
                addToWhere(variables, 'coaches', coachesSelection.value);
            }
            if (groupsSelection.value && groupsSelection.value.length > 0) {
                addToWhere(variables, 'groups', groupsSelection.value);
            }

            apolloClient.query({
                query: trainingsPaginationQuery,
                variables: variables
            }).then(({data}) => {
                // update rowsCount with appropriate value
                pagination.value.rowsNumber = data.trainings.paginatorInfo.total

                // clear out existing data and add new
                rows.value.splice(0, rows.value.length, ...data.trainings.data)

                // don't forget to update local pagination object
                pagination.value.page = page
                pagination.value.rowsPerPage = rowsPerPage
                pagination.value.sortBy = sortBy
                pagination.value.descending = descending
            }).catch(() => {
                $q.notify({
                    message: 'Daten konnten nicht geladen werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false
            })
        }

        const confirmDelete = (training) => {
            $q.dialog({
                title: 'Löschen Bestätigen',
                message: 'Mitglied "' + training['id'] + '" wirklich löschen?',
                cancel: true,
                html: true,
                persistent: true
            }).onOk(() => {
                deleteTraining(training)
            })
        }

        const editTraining = (training) => {
            editTrainingId.value = training.id;
            openEditDialog.value = true;
        }

        const trainingUpdated = (trainingUpdated) => {
            editTrainingId.value = null;
            openEditDialog.value = false;
            const index = rows.value.findIndex(row => row.id == trainingUpdated.id);
            if (index > -1) {
                rows.value[index] = trainingUpdated
            }
        }

        const deleteTraining = (training) => {
            loading.value = true
            apolloClient.mutate({
                mutation: deleteTrainingsMutation,
                variables: {id: training.id}
            }).then(({data}) => {
                $q.notify({
                    message: 'Training ' + data.deleteTraining.id + ' gelöscht',
                    color: 'positive'
                })
                const index = rows.value.indexOf(training);
                if (index > -1) { // only splice array when item is found
                    rows.value.splice(index, 1); // 2nd parameter means remove one item only
                }
            }).catch(() => {
                $q.notify({
                    message: 'Training ' + training.id + ' konnte nicht gelöscht werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false
            })
        }

        const updateTraining = (trainingId, field, value) => {
            loading.value = true;
            if (field == 'athletes') {
                value = {
                    sync:
                        value.map(id => ({
                            id: id,
                            role: "athlete"
                        }))
                }
            } else if (field == 'coaches') {
                value = {
                    sync:
                        value.map(id => ({
                            id: id,
                            role: "coach"
                        }))
                }
            } else if (field == 'groups') {
                value = {
                    sync: value
                }
            } else if (field == 'location') {
                value = {
                    connect: value
                }
            }
            apolloClient.mutate({
                mutation: updateTrainingsMutation,
                variables: {id: trainingId, [field]: value}
            }).then(({data}) => {
                $q.notify({
                    message: 'Training ' + data.updateTraining.id + ' aktualisiert',
                    color: 'positive'
                })
                const index = rows.value.findIndex(row => row.id == data.updateTraining.id);
                if (index > -1) { // only splice array when item is found
                    rows.value[index] = data.updateTraining
                }
            }).catch(() => {
                $q.notify({
                    message: 'Training konnte nicht aktualisiert werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false;
            })
        }

        const addAthleteRole = (training) => {
            loading.value = true;
            const existingRoles = [...training.roles].map(role => role.id);
            existingRoles.push(3);
            apolloClient.mutate({
                mutation: updateTrainingsMutation,
                variables: {id: training.id, 'roles': existingRoles}
            }).then(({data}) => {
                $q.notify({
                    message: 'Training ' + data.updateTraining.id + ' aktualisiert',
                    color: 'positive'
                })
                const index = rows.value.findIndex(row => row.id == data.updateTraining.id);
                if (index > -1) { // only splice array when item is found
                    rows.value[index] = data.updateTraining
                }
            }).catch(() => {
                $q.notify({
                    message: 'Training konnte nicht aktualisiert werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false;
            })

        }
        const removeAthleteRole = (training) => {
            loading.value = true;
            const existingRoles = [...training.roles].map(role => role.id);
            const index = existingRoles.indexOf("3");
            if (index > -1) {
                existingRoles.splice(index, 1);
            }
            apolloClient.mutate({
                mutation: updateTrainingsMutation,
                variables: {id: training.id, 'roles': existingRoles}
            }).then(({data}) => {
                $q.notify({
                    message: 'Training ' + data.updateTraining.id + ' aktualisiert',
                    color: 'positive'
                })
                const index = rows.value.findIndex(row => row.id == data.updateTraining.id);
                if (index > -1) { // only splice array when item is found
                    rows.value[index] = data.updateTraining
                }
            }).catch(() => {
                $q.notify({
                    message: 'Training konnte nicht aktualisiert werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false;
            })

        }

        onMounted(() => {
            // get initial data from server (1st page)
            tableRef.value.requestServerInteraction()
        })


        return {
            rows,
            filter,
            tableRef,
            coachesSelection,
            athletesSelection,
            groupsSelection,
            locations,
            loading,
            onRequest,
            pagination,
            columns,
            deleteTraining,
            confirmDelete,
            editTraining,
            updateTraining,
            removeAthleteRole,
            addAthleteRole,
            filterCol,
            roles,
            groups,
            athletes,
            coaches,
            date,
            filterByGroups,
            visibleColumns,
            openEditDialog,
            editTrainingId,
            trainingUpdated,
        }
    },
}
</script>