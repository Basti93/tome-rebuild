<template>
  <q-page>
      <div class="row q-ma-md">
        <q-table
            class="col-12"
            ref="tableRef"
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
                                  class="col"
                                  v-model="filterCol"
                                  @update:model-value="tableRef.requestServerInteraction()"
                                  dense
                                  outlined
                                  options-dense
                                  emit-value
                                  map-options
                                  :options="columns.filter(c => c.name === 'firstname' || c.name === 'nickname' || c.name === 'lastname')"
                                  option-value="name"
                                  label="Spalte zum Suchen"
                          ></q-select>
                          <q-input class="col q-pl-md" label="Suche" filled dense debounce="300" color="primary" v-model="filter">
                              <template v-slot:append>
                                  <q-icon name="search" />
                              </template>
                          </q-input>
                        </div>
                    </div>
                </div>
              <q-card flat bordered class="q-pa-sm full-width">
                <div class="row">
                    <div class="col-12 q-pt-md">
                      <q-btn-toggle
                              v-model="approvedToggle"
                              @update:model-value="pagination.page = 1; tableRef.requestServerInteraction()"
                              :options="[
                                { label: 'Alle', value: 'all' },
                                { label: 'Serientermine', value: 'approved' },
                                { label: 'Manuel angelegt', value: 'notApproved' }
                              ]"
                              toggle-color="primary"
                              rounded />
                      </div>
                    <div class="col-12 q-pt-md">
                        <q-btn-toggle
                                v-model="roleToggle"
                                @update:model-value="pagination.page = 1; tableRef.requestServerInteraction()"
                                :options="roleToggleOptions"
                                toggle-color="primary"
                                rounded >
                        </q-btn-toggle>
                    </div>
                    <div class="col-12 col-lg-4 q-pt-md">
                        <q-select
                                width="100%"
                                label="Gruppe"
                                v-model="groupToggle"
                                @update:model-value="pagination.page = 1; tableRef.requestServerInteraction()"
                                :options="groupToggleOptions">
                        </q-select>
                    </div>
                </div>
              </q-card>
          </template>

          <template v-slot:body="props">
            <q-tr :props="props" :style="props.row.date_start > Date.now() ? 'text-color:blue' : ''">
              <q-td key="id" :props="props">{{ props.row.id }}</q-td>
              <q-td key="name" :props="props">{{ props.row.name }}
                <q-popup-edit :model-value="props.row.name" v-slot="scope" @save="(value) => updateTraining(props.row.id, 'name', value)">
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
                {{ props.row.description.length > 20 ? props.row.description.substring(0, 20) + '...' : props.row.description }}
                <q-popup-edit :model-value="props.row.description" v-slot="scope" @save="(value) => updateTraining(props.row.id, 'description', value)">
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
                  <q-btn v-if="props.row.status" @click="updateTraining(props.row.id, 'status', false)" size="xs" outline round icon="check" color="green" />
                  <q-btn v-if="!props.row.status" @click="updateTraining(props.row.id, 'status', true)" size="xs" outline round icon="clear" color="red" />
              </q-td>
              <q-td key="date_start" :props="props">{{ date.formatDate(props.row.date_start, 'DD.MM.YYYY HH:mm') }}
                <q-popup-edit :model-value="props.row.date_start" v-slot="scope" @save="(value) => updateTraining(props.row.id, 'date_start', value)">
                    <div class="row">
                    <q-date v-model="scope.value" mask="YYYY-MM-DD HH:mm" />
                    <q-time v-model="scope.value" mask="YYYY-MM-DD HH:mm" />
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
              <q-td key="date_end" :props="props">{{ date.formatDate(props.row.date_end, 'DD.MM.YYYY HH:mm') }}
                <q-popup-edit :model-value="props.row.date_end" v-slot="scope" @save="(value) => updateTraining(props.row.id, 'date_end', value)">
                    <div class="row">
                    <q-date v-model="scope.value" mask="YYYY-MM-DD HH:mm" />
                    <q-time v-model="scope.value" mask="YYYY-MM-DD HH:mm" />
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
                <q-popup-edit :model-value="props.row.location" v-slot="scope" @save="(value) => updateTraining(props.row.id, 'location', value.id)">
                  <q-select v-model="scope.value" dense autofocus @keyup.enter="scope.set" :options="locations" option-value="id" option-label="name">
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
                  <q-popup-edit :model-value="props.row.groups" v-slot="scope" @save="(value) => updateTraining(props.row.id, 'groups', value.map(g => g.id))">
                      <q-select v-model="scope.value" multiple dense use-chips autofocus @keyup.enter="scope.set" :options="groups" option-value="id" option-label="name">
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
                <q-popup-edit :model-value="props.row.athletes" v-slot="scope" @save="(value) => updateTraining(props.row.id, 'athletes', value.map(g => g.id))">
                    <q-select v-model="scope.value" multiple dense use-chips autofocus @keyup.enter="scope.set" :options="athletes" option-value="id" :option-label="(u) => u.nickname ? u.nickname : u.firstname + ' ' + u.lastname">
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
              <q-td key="coaches" :props="props">
                {{ props.row.coaches.map(u => u.firstname + ' ' + u.lastname).toString() }}
                <q-popup-edit :model-value="props.row.coaches" v-slot="scope" @save="(value) => updateTraining(props.row.id, 'coaches', value.map(g => g.id))">
                    <q-select v-model="scope.value" multiple dense use-chips autofocus @keyup.enter="scope.set" :options="coaches" option-value="id" :option-label="(u) => u.nickname ? u.nickname : u.firstname + ' ' + u.lastname">
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
                  <q-menu auto-close v-ripple >
                      <q-item clickable @click="confirmDelete(props.row)">
                          <q-item-section avatar><q-icon name="person_remove"/></q-item-section>
                          <q-item-section>Mitglied Löschen</q-item-section>
                      </q-item>
                  </q-menu>
                  </q-btn>
              </q-td>
            </q-tr>
          </template>

        </q-table>
      </div>
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
import { useQuasar } from 'quasar'
import {useQuery} from "@vue/apollo-composable";


export default {
  setup() {
    const $q = useQuasar()
    const tableRef = ref()
    const filter = ref('')
    const filterCol = ref('firstname')
    const approvedToggle = ref('approved');
    const roleToggle = ref('3');
    const groupToggle = ref({value: 'all', label: 'Alle'});
    const rows = ref([])
    const loading = ref(false)
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
      {name: 'date_start', align: 'center', label: 'Start', field: 'date_start', sortable: false},
      {name: 'date_end', align: 'center', label: 'Ende', field: 'date_end', sortable: false},
      {name: 'location', align: 'center', label: 'Ort', field: 'location', sortable: true,},
      {name: 'groups', align: 'center', label: 'Gruppen', field: 'groups', sortable: false,},
      {name: 'athletes', align: 'center', label: 'Teilnehmer', field: 'athletes', sortable: false,},
      {name: 'coaches', align: 'center', label: 'Trainer', field: 'coaches', sortable: false,},
      {name: 'actions', align: 'center', label: 'Aktionen', field: '', sortable: false,},
    ]
    const {result: rolesResult} = useQuery(rolesQuery);
    const {result: groupsResult} = useQuery(groupsQuery);
    const {result: locationsResult} = useQuery(locationsQuery);
    const {result: athletesResult} = useQuery(athletesQuery);
    const {result: coachesResult} = useQuery(coachesQuery);
    const roles = computed(() => rolesResult.value?.roles ?? [])
    const roleToggleOptions = computed(() => {
        const options = [...roles.value.map(r => {
            return {
                label: r.name,
                value: r.id
            }
        })];
        options.unshift({
            label: 'Alle',
            value: 'all'
        })
      return options
    })
    const groupToggleOptions = computed(() => {
        const options = [...groups.value.map(g => {
            return {
                label: g.name,
                value: g.id
            }
        })];
        options.unshift({
            label: 'Alle',
            value: 'all'
        })
      return options
    })
    const groups = computed(() => groupsResult.value?.groups ?? [])
    const locations = computed(() => locationsResult.value?.locations ?? [])
    const athletes = computed(() => athletesResult.value?.users ?? [])
    const coaches = computed(() => coachesResult.value?.users ?? [])

    const onRequest = (props) => {
      loading.value = true
      const {page, rowsPerPage, sortBy, descending} = props.pagination
      // calculate starting row of data
      const filter = props.filter
      const variables = ref({
        first: rowsPerPage,
        page: page,
      });
      if (sortBy) {
        variables.value.orderBy = [
          {
            column: sortBy.toUpperCase(),
            order: descending ? 'DESC' : 'ASC',
          }
        ]
      }
      if (filter) {
          variables.value[`${filterCol.value}`] = "%" + filter + "%";
      }
      if (roleToggle.value != 'all') {
          variables.value[`hasRole`] = {
              column: 'ID',
              value: roleToggle.value
          }
      }
      if (groupToggle.value.value !== 'all') {
          variables.value[`hasGroup`] = {
              column: 'ID',
              value: groupToggle.value.value
          }
      }
      if (approvedToggle.value !== 'all') {
          variables.value['approved'] = approvedToggle.value == 'approved';
      }

      apolloClient.query({
        query: trainingsPaginationQuery,
        variables: variables.value
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
              color: 'negative'})
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
    const deleteTraining = (training) => {
      loading.value = true
      apolloClient.mutate({
        mutation: deleteTrainingsMutation,
        variables: {id: training.id}
      }).then(({data}) => {
        $q.notify({
            message: 'Training ' + data.deleteTraining.id + ' gelöscht',
            color: 'positive'})
        const index = rows.value.indexOf(training);
        if (index > -1) { // only splice array when item is found
            rows.value.splice(index, 1); // 2nd parameter means remove one item only
        }
      }).catch(() => {
        $q.notify({
            message: 'Training ' + training.id + ' konnte nicht gelöscht werden',
            color: 'negative'})
      }).finally(() => {
          loading.value = false
      })
    }

    const updateTraining = (trainingId, field, value) => {
      loading.value = true;
      apolloClient.mutate({
        mutation: updateTrainingsMutation,
        variables: {id: trainingId, [field]: value}
      }).then(({data}) => {
          $q.notify({
              message: 'Training ' + data.updateTraining.id + ' aktualisiert',
              color: 'positive'})
          const index = rows.value.findIndex(row => row.id == data.updateTraining.id);
          if (index > -1) { // only splice array when item is found
              rows.value[index] = data.updateTraining
          }
      }).catch(() => {
          $q.notify({
              message: 'Training konnte nicht aktualisiert werden',
              color: 'negative'})
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
                color: 'positive'})
            const index = rows.value.findIndex(row => row.id == data.updateTraining.id);
            if (index > -1) { // only splice array when item is found
                rows.value[index] = data.updateTraining
            }
        }).catch(() => {
            $q.notify({
                message: 'Training konnte nicht aktualisiert werden',
                color: 'negative'})
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
                color: 'positive'})
            const index = rows.value.findIndex(row => row.id == data.updateTraining.id);
            if (index > -1) { // only splice array when item is found
                rows.value[index] = data.updateTraining
            }
        }).catch(() => {
            $q.notify({
                message: 'Training konnte nicht aktualisiert werden',
                color: 'negative'})
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
      approvedToggle,
      roleToggle,
      groupToggle,
      locations,
      loading,
      onRequest,
      pagination,
      columns,
      deleteTraining,
      confirmDelete,
      updateTraining,
      removeAthleteRole,
      addAthleteRole,
      filterCol,
      roles,
      roleToggleOptions,
      groupToggleOptions,
      groups,
      athletes,
      coaches,
      date,
    }
  },
}
</script>