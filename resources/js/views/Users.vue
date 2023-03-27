<template>
  <q-page class="full-height full-width row justify-center items-center">
    <div class="column">
      <div class="row">
        <h5 class="text-h5 q-my-md">Benutzer</h5>
      </div>
      <div class="row">
        <q-table
            ref="tableRef"
            title="Users"
            :rows="rows"
            :columns="columns"
            row-key="id"
            :rows-per-page-options="[10, 25, 50, 100]"
            v-model:pagination="pagination"
            :loading="loading"
            :filter="filter"
            selection="multiple"
            v-model:selected="selected"
            binary-state-sort
            @request="onRequest"
        >
          <template v-slot:top>
            <q-btn class="q-ml-sm" color="primary" :disable="selected.length == 0 || loading" label="Benutzer löschen" @click="confirmDelete" />
            <q-space />
            <q-input filled dense debounce="300" color="primary" v-model="filter">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>

        </q-table>
      </div>
    </div>
  </q-page>
</template>
<script>

import usersPaginationQuery from "../queries/userpagination.query.gql";
import deleteUsersMutation from "../queries/userdelete.mutation.gql";
import {onMounted, ref} from "vue";
import apolloClient from "../apollo";
import {date} from 'quasar'
import { useQuasar } from 'quasar'


export default {
  setup() {
    const $q = useQuasar()
    const tableRef = ref()
    const filter = ref('')
    const rows = ref([])
    const selected = ref([])
    const loading = ref(false)
    const pagination = ref({
      sortBy: 'id',
      descending: false,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 10
    })
    const columns = [
      {name: 'id', required: true, label: 'ID', align: 'left', field: 'id', sortable: true},
      {name: 'nickname', align: 'center', label: 'Spitzname', field: 'nickname', sortable: true},
      {name: 'firstname', align: 'center', label: 'Vorname', field: 'firstname', sortable: true},
      {name: 'lastname', align: 'center', label: 'Nachname', field: 'lastname', sortable: true},
      {name: 'birthdate', align: 'center', label: 'Geburtsdatum', field: 'birthdate', sortable: true, format: (val) => {
          return date.formatDate(date.extractDate(val, 'YYYY-MM-DD'), 'YYYY-MM-DD');
      }}
    ]


    const onRequest = async (props) => {
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
        ],
      });
      if (filter) {
        variables.value.filter =  "%" + filter + "%";
      }

      const {data} = await apolloClient.query({
        query: usersPaginationQuery,
        variables: variables.value
      })

      // update rowsCount with appropriate value
      pagination.value.rowsNumber = data.users.paginatorInfo.total

      // clear out existing data and add new
      rows.value.splice(0, rows.value.length, ...data.users.data)

      // don't forget to update local pagination object
      pagination.value.page = page
      pagination.value.rowsPerPage = rowsPerPage
      pagination.value.sortBy = sortBy
      pagination.value.descending = descending

      loading.value = false
    }

    const confirmDelete = () => {
          $q.dialog({
            title: 'Löschen Bestätigen',
            message: 'Folgende Benutzer wirklich löschen? ' + selected.value.map(function(item) {
              return item['firstname'] + ' ' + item['lastname'] + ', ';
            }),
            cancel: true,
            persistent: true
          }).onOk(() => {
            deleteUsers()
          })
    }
    const deleteUsers = async () => {
      loading.value = true
      for (const userToDelete of selected.value) {
        const {data} = await apolloClient.mutate({
          mutation: deleteUsersMutation,
          variables: {id: userToDelete.id}
        })
        $q.notify({
          message: 'Benutzer ' + data.deleteUser.firstname + " " + data.deleteUser.lastname + ' gelöscht',
          color: 'positive'})
        const index = rows.value.indexOf(userToDelete);
        if (index > -1) { // only splice array when item is found
          rows.value.splice(index, 1); // 2nd parameter means remove one item only
        }
      }
      selected.value = []
      loading.value = false
    }

    onMounted(() => {
      // get initial data from server (1st page)
      tableRef.value.requestServerInteraction()
    })


    return {
      rows,
      filter,
      tableRef,
      loading,
      onRequest,
      pagination,
      columns,
      selected,
      deleteUsers,
      confirmDelete,
    }
  },
}
</script>