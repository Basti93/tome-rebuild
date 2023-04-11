<template>
  <q-page>
    <div class="row q-ma-md">
        <q-table
            class="col-12"
            ref="tableRef"
            title="Configs"
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
                        <p class="text-h4">Einstellungen</p>
                        <p class="text-subtitle-1">Zum Editieren auf die Zeilen klicken</p>
                    </div>
                </div>
          </template>

          <template v-slot:body="props">
            <q-tr :props="props">
              <q-td key="id" :props="props">{{ props.row.id }}</q-td>
              <q-td key="key" :props="props">{{ props.row.key }}</q-td>
              <q-td key="value" :props="props">{{ props.row.value }}
                <q-popup-edit :model-value="props.row.value" v-slot="scope" @save="(value) => updateConfig(props.row.id, 'value', value)">
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
            </q-tr>
          </template>

        </q-table>
      </div>
  </q-page>
</template>
<script>

import configsPaginationQuery from "../queries/configpagination.query.gql";
import updateConfigsMutation from "../queries/configupdate.mutation.gql";
import { onMounted, ref} from "vue";
import apolloClient from "../apollo";
import {date} from 'quasar'
import { useQuasar } from 'quasar'


export default {
  emits: ["config-changed"],
  setup(props, { emit }) {
    const $q = useQuasar()
    const tableRef = ref()
    const filter = ref('')
    const filterCol = ref('name')
    const editName = ref('');
    const approvedToggle = ref('all');
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
      {name: 'key', align: 'center', label: 'Schlüssel', field: 'key', sortable: true},
      {name: 'value', align: 'center', label: 'Wert', field: 'value', sortable: true},
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
        query: configsPaginationQuery,
        variables: variables.value
      }).then(({data}) => {
          // update rowsCount with appropriate value
          pagination.value.rowsNumber = data.configsPaginate.paginatorInfo.total

          // clear out existing data and add new
          rows.value.splice(0, rows.value.length, ...data.configsPaginate.data)

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

    const updateConfig = (configId, field, value) => {
      loading.value = true;
      apolloClient.mutate({
        mutation: updateConfigsMutation,
        variables: {id: configId, [field]: value}
      }).then(({data}) => {
          $q.notify({
              message: 'Einstellung ' + data.updateConfig.name + ' aktualisiert',
              color: 'positive'})
          const index = rows.value.findIndex(row => row.id == data.updateConfig.id);
          emit('config-changed')
          if (index > -1) { // only splice array when item is found
              rows.value[index] = data.updateConfig
          }
      }).catch(() => {
          $q.notify({
              message: 'Einstellung konnte nicht aktualisiert werden',
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
      onRequest,
      pagination,
      columns,
      updateConfig,
      filterCol,
      editName,
      date,
    }
  },
}
</script>