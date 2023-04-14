<template>
  <q-page>
    <div class="row q-ma-md">
        <q-table
            class="col-12"
            ref="tableRef"
            title="Groups"
            :rows="rows"
            :columns="columns"
            row-key="id"
            :rows-per-page-options="[10, 25, 50, 100]"
            v-model:pagination="pagination"
            wrap-cells
            :loading="loading"
            :filter="filter"
            binary-state-sort
            @request="onRequest"
        >
          <template v-slot:top>
                <div class="row full-width">
                    <div class="col">
                        <p class="text-h4">Gruppen</p>
                        <p class="text-subtitle-1">Zum Editieren auf die Zeilen klicken</p>
                    </div>
                    <div class="col">
                        <q-btn
                            class="float-right"
                            color="primary"
                            label="Neue Gruppe"
                            icon="group_add"
                            @click="showNewGroupDialog = true"
                        />
                    </div>
                </div>
          </template>

          <template v-slot:body="props">
            <q-tr :props="props">
              <q-td key="id" :props="props">{{ props.row.id }}</q-td>
              <q-td key="name" :props="props">{{ props.row.name }}
                <q-popup-edit :model-value="props.row.name" v-slot="scope" @save="(value) => updateGroup(props.row.id, 'name', value)">
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

              <q-td key="athletes" :props="props">
                  {{ props.row.athletes ? props.row.athletes.length : 0 }} <q-icon name="edit" />
                  <q-popup-edit :model-value="props.row.athletes" v-slot="scope" @save="(value) => updateGroup(props.row.id, 'athletes', value.map(g => g.id))">
                      <q-select style="min-width: 300px;" v-model="scope.value" label="Mitglieder" multiple dense use-chips autofocus @keyup.enter="scope.set" :options="athletes" option-value="id" :option-label="(u) => u.firstname + ' ' + u.lastname">
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
                  <q-popup-edit :model-value="props.row.coaches" v-slot="scope" @save="(value) => updateGroup(props.row.id, 'coaches', value.map(g => g.id))">
                      <q-select style="min-width: 300px;" label="Trainer" v-model="scope.value" multiple dense use-chips autofocus @keyup.enter="scope.set" :options="coaches" option-value="id" :option-label="(u) => u.firstname + ' ' + u.lastname">
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
                          <q-item-section avatar><q-icon name="group_remove"/></q-item-section>
                          <q-item-section>Gruppe Löschen</q-item-section>
                      </q-item>
                  </q-menu>
                  </q-btn>
              </q-td>
            </q-tr>
          </template>

        </q-table>
      </div>
  </q-page>
    <q-dialog v-model="showNewGroupDialog" persistent>
        <q-card>
            <q-card-section class="row items-center">
                <div class="text-h6">Neue Gruppe</div>
            </q-card-section>

            <q-card-section>
                <q-form @submit="createGroup(editName)">
                    <q-input
                        v-model="editName"
                        label="Name"
                        filled
                        lazy-rules
                        :rules="[val => val.length > 0 || 'Bitte einen Namen eingeben']"
                    />
                    <div class="row justify-end q-mt-sm">
                        <q-btn label="Abbrechen" color="primary" flat @click="showNewGroupDialog = false" />
                        <q-btn label="Speichern" color="primary" type="submit" />
                    </div>
                </q-form>
            </q-card-section>
        </q-card>
    </q-dialog>
</template>
<script>

import groupsPaginationQuery from "../queries/grouppagination.query.gql";
import deleteGroupMutation from "../queries/groupdelete.mutation.gql";
import createGroupMutation from "../queries/groupcreate.mutation.gql";
import updateGroupsMutation from "../queries/groupupdate.mutation.gql";
import {computed, onMounted, ref} from "vue";
import apolloClient from "../apollo";
import {date} from 'quasar'
import { useQuasar } from 'quasar'
import {useQuery} from "@vue/apollo-composable";
import athletesQuery from "../queries/athletes.query.gql";
import coachesQuery from "../queries/coaches.query.gql";


export default {
  setup() {
    const $q = useQuasar()
    const tableRef = ref()
    const filter = ref('')
    const filterCol = ref('name')
    const editName = ref('');
    const approvedToggle = ref('all');
    const rows = ref([])
    const loading = ref(false)
    const showNewGroupDialog = ref(false)
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
      {name: 'athletes', align: 'center', label: 'Sportler', field: 'athletes', sortable: true,},
      {name: 'coaches', align: 'center', label: 'Trainer', field: 'coaches', sortable: false,},
      {name: 'actions', align: 'center', label: 'Aktionen', field: '', sortable: false,},
    ]
    const {result: athletesResult} = useQuery(athletesQuery);
    const {result: coachesResult} = useQuery(coachesQuery);
    const athletes = computed(() => athletesResult.value?.users ?? [])
    const coaches = computed(() => coachesResult.value?.users ?? [])

    const onRequest = (props) => {
      loading.value = true
      const {page, rowsPerPage, sortBy, descending} = props.pagination
      // calculate starting row of data
      const filter = props.filter
      const variables = {
        first: rowsPerPage,
        page: page,
      };
      if (sortBy) {
          if (sortBy == 'athletes') {
              variables.orderBy = [
                  {
                      athletes: { aggregate: 'COUNT' },
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
          if (filterCol.value === 'hasRole') {
              variables[`${filterCol.value}`] = {
                  column: 'NAME',
                  operator: 'LIKE',
                  value: filter
              }
          } else {
              variables[`${filterCol.value}`] = "%" + filter + "%";
          }
      }

      apolloClient.query({
        query: groupsPaginationQuery,
        variables: variables
      }).then(({data}) => {
          // update rowsCount with appropriate value
          pagination.value.rowsNumber = data.groupsPaginate.paginatorInfo.total

          // clear out existing data and add new
          rows.value.splice(0, rows.value.length, ...data.groupsPaginate.data)

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

    const createGroup = (name) => {
      loading.value = true
      apolloClient.mutate({
        mutation: createGroupMutation,
        variables: {name: name}
      }).then(({data}) => {
        $q.notify({
            message: 'Gruppe ' + data.createGroup.name + ' erstellt',
            color: 'positive'})
        rows.value.unshift(data.createGroup)
        editName.value = ''
        showNewGroupDialog.value = false
      }).catch(() => {
          $q.notify({
              message: 'Gruppe konnte nicht erstellt werden',
              color: 'negative'})
      }).finally(() => {
          loading.value = false
      })
    }

    const confirmDelete = (group) => {
          $q.dialog({
            title: 'Löschen Bestätigen',
            message: 'Gruppe "' + group['id'] + ' - ' + group['name'] + '" wirklich löschen?',
            cancel: true,
            html: true,
            persistent: true
          }).onOk(() => {
            deleteGroup(group)
          })
    }
    const deleteGroup = (group) => {
      loading.value = true
      apolloClient.mutate({
        mutation: deleteGroupMutation,
        variables: {id: group.id}
      }).then(({data}) => {
        $q.notify({
            message: 'Gruppe ' + data.deleteGroup.name + ' gelöscht',
            color: 'positive'})
        const index = rows.value.indexOf(group);
        if (index > -1) { // only splice array when item is found
            rows.value.splice(index, 1); // 2nd parameter means remove one item only
        }
      }).catch(() => {
        $q.notify({
            message: 'Gruppe ' + group.name + ' konnte nicht gelöscht werden',
            color: 'negative'})
      }).finally(() => {
          loading.value = false
      })
    }

    const updateGroup = (groupId, field, value) => {
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
      }

      apolloClient.mutate({
        mutation: updateGroupsMutation,
        variables: {id: groupId, [field]: value}
      }).then(({data}) => {
          $q.notify({
              message: 'Gruppe ' + data.updateGroup.name + ' aktualisiert',
              color: 'positive'})
          const index = rows.value.findIndex(row => row.id == data.updateGroup.id);
          if (index > -1) {
              rows.value[index] = data.updateGroup
          }
      }).catch(() => {
          $q.notify({
              message: 'Gruppe konnte nicht aktualisiert werden',
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
      loading,
      showNewGroupDialog,
      onRequest,
      pagination,
      columns,
      createGroup,
      deleteGroup,
      confirmDelete,
      updateGroup,
      filterCol,
      editName,
      coaches,
      athletes,
      date,
    }
  },
}
</script>