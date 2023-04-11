<template>
  <q-page>
    <div class="row q-ma-md">
        <q-table
            class="col-12"
            ref="tableRef"
            title="Locations"
            :rows="rows"
            :columns="columns"
            row-key="id"
            :rows-per-page-options="[10, 25, 50, 100]"
            v-model:pagination="pagination"
            :loading="loading"
            :filter="filter"
            binary-state-sort
            @request="onRequest"
        >
          <template v-slot:top>
                <div class="row full-width">
                    <div class="col">
                        <p class="text-h4">Orte</p>
                        <p class="text-subtitle-1">Zum Editieren auf die Zeilen klicken</p>
                    </div>
                    <div class="col">
                        <q-btn
                            class="float-right"
                            color="primary"
                            label="Neuer Ort"
                            icon="add"
                            @click="showNewLocationDialog = true"
                        />
                    </div>
                </div>
          </template>

          <template v-slot:body="props">
            <q-tr :props="props">
              <q-td key="id" :props="props">{{ props.row.id }}</q-td>
              <q-td key="name" :props="props">{{ props.row.name }}
                <q-popup-edit :model-value="props.row.name" v-slot="scope" @save="(value) => updateLocation(props.row.id, 'name', value)">
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

              <q-td key="actions" :props="props">
                  <q-btn round flat icon="more_vert">
                  <q-menu auto-close v-ripple >
                      <q-item clickable @click="confirmDelete(props.row)">
                          <q-item-section avatar><q-icon name="delete"/></q-item-section>
                          <q-item-section>Löschen</q-item-section>
                      </q-item>
                  </q-menu>
                  </q-btn>
              </q-td>
            </q-tr>
          </template>

        </q-table>
      </div>
  </q-page>
    <q-dialog v-model="showNewLocationDialog" persistent>
        <q-card>
            <q-card-section class="row items-center">
                <div class="text-h6">Neuer Ort</div>
            </q-card-section>

            <q-card-section>
                <q-form @submit="createLocation(editName)">
                    <q-input
                        v-model="editName"
                        label="Name"
                        filled
                        lazy-rules
                        :rules="[val => val.length > 0 || 'Bitte einen Namen eingeben']"
                    />
                    <div class="row justify-end q-mt-sm">
                        <q-btn label="Abbrechen" color="primary" flat @click="showNewLocationDialog = false" />
                        <q-btn label="Speichern" color="primary" type="submit" />
                    </div>
                </q-form>
            </q-card-section>
        </q-card>
    </q-dialog>
</template>
<script>

import locationsPaginationQuery from "../queries/locationpagination.query.gql";
import deleteLocationMutation from "../queries/locationdelete.mutation.gql";
import createLocationMutation from "../queries/locationcreate.mutation.gql";
import updateLocationsMutation from "../queries/locationupdate.mutation.gql";
import { onMounted, ref} from "vue";
import apolloClient from "../apollo";
import {date} from 'quasar'
import { useQuasar } from 'quasar'


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
    const showNewLocationDialog = ref(false)
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
      {name: 'actions', align: 'center', label: 'Aktionen', field: '', sortable: false,},
    ]

    const onRequest = (props) => {
      loading.value = true
      const {page, rowsPerPage, sortBy, descending} = props.pagination
      // calculate starting row of data
      const filter = props.filter
      const variables = ref({
        first: rowsPerPage,
        page: page,
        orderBy: [
            {
                column: sortBy.toUpperCase(),
                order: descending ? 'DESC' : 'ASC',
            }
        ]
      });
      if (filter) {
          if (filterCol.value === 'hasRole') {
              variables.value[`${filterCol.value}`] = {
                  column: 'NAME',
                  operator: 'LIKE',
                  value: filter
              }
          } else {
              variables.value[`${filterCol.value}`] = "%" + filter + "%";
          }
      }

      apolloClient.query({
        query: locationsPaginationQuery,
        variables: variables.value
      }).then(({data}) => {
          // update rowsCount with appropriate value
          pagination.value.rowsNumber = data.locationsPaginate.paginatorInfo.total

          // clear out existing data and add new
          rows.value.splice(0, rows.value.length, ...data.locationsPaginate.data)

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

    const createLocation = (name) => {
      loading.value = true
      apolloClient.mutate({
        mutation: createLocationMutation,
        variables: {name: name}
      }).then(({data}) => {
        $q.notify({
            message: 'Ort ' + data.createLocation.name + ' erstellt',
            color: 'positive'})
        rows.value.unshift(data.createLocation)
        editName.value = ''
        showNewLocationDialog.value = false
      }).catch(() => {
          $q.notify({
              message: 'Ort konnte nicht erstellt werden',
              color: 'negative'})
      }).finally(() => {
          loading.value = false
      })
    }

    const confirmDelete = (location) => {
          $q.dialog({
            title: 'Löschen Bestätigen',
            message: 'Ort "' + location['id'] + ' - ' + location['name'] + '" wirklich löschen?',
            cancel: true,
            html: true,
            persistent: true
          }).onOk(() => {
            deleteLocation(location)
          })
    }
    const deleteLocation = (location) => {
      loading.value = true
      apolloClient.mutate({
        mutation: deleteLocationMutation,
        variables: {id: location.id}
      }).then(({data}) => {
        $q.notify({
            message: 'Ort ' + data.deleteLocation.name + ' gelöscht',
            color: 'positive'})
        const index = rows.value.indexOf(location);
        if (index > -1) { // only splice array when item is found
            rows.value.splice(index, 1); // 2nd parameter means remove one item only
        }
      }).catch(() => {
        $q.notify({
            message: 'Ort ' + location.name + ' konnte nicht gelöscht werden',
            color: 'negative'})
      }).finally(() => {
          loading.value = false
      })
    }

    const updateLocation = (locationId, field, value) => {
      loading.value = true;
      apolloClient.mutate({
        mutation: updateLocationsMutation,
        variables: {id: locationId, [field]: value}
      }).then(({data}) => {
          $q.notify({
              message: 'Ort ' + data.updateLocation.name + ' aktualisiert',
              color: 'positive'})
          const index = rows.value.findIndex(row => row.id == data.updateLocation.id);
          if (index > -1) { // only splice array when item is found
              rows.value[index] = data.updateLocation
          }
      }).catch(() => {
          $q.notify({
              message: 'Ort konnte nicht aktualisiert werden',
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
      showNewLocationDialog,
      onRequest,
      pagination,
      columns,
      createLocation,
      deleteLocation,
      confirmDelete,
      updateLocation,
      filterCol,
      editName,
      date,
    }
  },
}
</script>