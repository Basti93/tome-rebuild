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
            <q-btn class="q-ml-sm" color="red" :disable="selected.length == 0 || loading" @click="confirmDelete">
              <q-icon left name="delete" />
              <div>Löschen</div>
            </q-btn>
            <q-space />
            <q-input filled dense debounce="300" color="primary" v-model="filter">
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>
          </template>

          <template v-slot:body="props">
            <q-tr :props="props">
              <q-td>
                <q-checkbox v-model="props.selected"/>
              </q-td>
              <q-td key="id" :props="props">{{ props.row.id }}</q-td>
              <q-td key="nickname" :props="props">{{ props.row.nickname }}
                <q-popup-edit :model-value="props.row.nickname" v-slot="scope" @save="(value) => updateUser(props.row.id, 'nickname', value)">
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
              <q-td key="firstname" :props="props">
                {{ props.row.firstname }}
                <q-popup-edit :model-value="props.row.firstname" v-slot="scope" @save="(value) => updateUser(props.row.id, 'firstname', value)">
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

              <q-td key="lastname" :props="props">{{ props.row.lastname }}
                <q-popup-edit :model-value="props.row.lastname" v-slot="scope" @save="(value) => updateUser(props.row.id, 'lastname', value)">
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
              <q-td key="birthdate" :props="props">{{ props.row.birthdate }}
                <q-popup-edit :model-value="props.row.birthdate" v-slot="scope" @save="(value) => updateUser(props.row.id, 'birthdate', value)">
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
              <q-td key="approved" :props="props">
                <q-btn v-if="props.row.approved" @click="updateUser(props.row.id, 'approved', false)" flat round><q-icon color="green" name="check" /></q-btn>
                <q-btn v-if="!props.row.approved" @click="updateUser(props.row.id, 'approved', true)"  flat round><q-icon color="red" name="cancel" /></q-btn>
              </q-td>

            </q-tr>
          </template>

        </q-table>
      </div>
    </div>
  </q-page>
</template>
<script>

import usersPaginationQuery from "../queries/userpagination.query.gql";
import deleteUsersMutation from "../queries/userdelete.mutation.gql";
import updateUsersMutation from "../queries/userupdate.mutation.gql";
import {computed, onMounted, ref} from "vue";
import apolloClient from "../apollo";
import {date} from 'quasar'
import { useQuasar } from 'quasar'
import {useStore} from "vuex";


export default {
  setup() {
    const $q = useQuasar()
    const store = useStore();
    const tableRef = ref()
    const filter = ref('')
    const firstnameEdit = ref('');
    const rows = ref([])
    const selected = ref([])
    const loading = ref(false)
    const me = computed(() => store.state.auth.user)
    const pagination = ref({
      sortBy: 'id',
      descending: true,
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
      }},
      {name: 'approved', align: 'center', label: 'Freigeschaltet', field: 'approved', sortable: true,}
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
            message: 'Folgende Benutzer wirklich löschen? <ul>' + selected.value.map(function(item)  {
              return '<li>' + item['id'] + ' - ' + item['firstname'] + ' ' + item['lastname'] + '</li>';
            }) +"</ul>",
            cancel: true,
            html: true,
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

    const updateUser = async (userId, field, value) => {
      loading.value = true;
      const {data} = await apolloClient.mutate({
        mutation: updateUsersMutation,
        variables: {id: userId, [field]: value}
      })
      $q.notify({
        message: 'Benutzer ' + data.updateUser.firstname + " " + data.updateUser.lastname + ' aktualisiert',
        color: 'positive'})

      const index = rows.value.findIndex(row => row.id == data.updateUser.id);
      if (index > -1) { // only splice array when item is found
        rows.value[index] = data.updateUser
      }
      loading.value = false;
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
      updateUser,
      me,
      firstnameEdit,
    }
  },
}
</script>