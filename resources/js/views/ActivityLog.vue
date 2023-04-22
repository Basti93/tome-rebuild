<template>
    <q-page>
        <div class="row q-ma-md">
            <q-table
                class="col-12"
                ref="tableRef"
                title="Activity Log"
                :rows="rows"
                :columns="columns"
                row-key="id"
                :rows-per-page-options="[10, 25, 50, 100]"
                v-model:pagination="pagination"
                wrap-cells
                :loading="loading"
                @request="onRequest"
            >
                <template v-slot:top>
                    <div class="row full-width">
                        <div class="col">
                            <p class="text-h4">Activity Log</p>
                        </div>
                    </div>
                </template>

                <template v-slot:body="props">
                    <q-tr :props="props">
                        <q-td key="id" :props="props">{{ props.row.id }}</q-td>
                        <q-td key="event" :props="props">{{ props.row.event }}</q-td>
                        <q-td key="subject_type" :props="props">{{ props.row.subject_type }}</q-td>
                        <q-td key="subject_id" :props="props">{{ props.row.subject_id }}</q-td>
                        <q-td key="causer_type" :props="props">{{ props.row.causer_type }}</q-td>
                        <q-td key="causer_id" :props="props">{{ props.row.causer_id }}</q-td>
                        <q-td key="properties" :props="props">{{ props.row.properties }}</q-td>
                        <q-td key="created_at" :props="props">{{ props.row.created_at }}</q-td>
                    </q-tr>
                </template>

            </q-table>
        </div>
    </q-page>
</template>

<script>
import {date, useQuasar} from "quasar";
import {onMounted, ref} from "vue";
import apolloClient from "../apollo";
import activityPaginationQuery from "../queries/activitypagination.query.gql";
import Pusher from "pusher-js";

export default {
    name: "ActivityLog",
    setup(props, {emit}) {
        const $q = useQuasar()
        const tableRef = ref()
        const filter = ref('')
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
            {name: 'event', label: 'Event', align: 'left', field: 'event', sortable: false},
            {name: 'subject_type', label: 'Subject Type', align: 'left', field: 'subject_type', sortable: false},
            {name: 'subject_id', label: 'Subject ID', align: 'left', field: 'subject_id', sortable: false},
            {name: 'causer_type', label: 'Causer Type', align: 'left', field: 'causer_type', sortable: false},
            {name: 'causer_id', label: 'Causer ID', align: 'left', field: 'causer_id', sortable: false},
            {name: 'properties', label: 'Properties', align: 'left', field: 'properties', sortable: false},
            {name: 'created_at', label: 'Datum', align: 'left', field: 'created_at', sortable: false},
        ]

        const onRequest = (props) => {
            loading.value = true
            const {page, rowsPerPage, sortBy, descending} = props.pagination
            // calculate starting row of data
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

            apolloClient.query({
                query: activityPaginationQuery,
                variables: variables.value
            }).then(({data}) => {
                // update rowsCount with appropriate value
                pagination.value.rowsNumber = data.activityPaginate.paginatorInfo.total

                // clear out existing data and add new
                rows.value.splice(0, rows.value.length, ...data.activityPaginate.data)

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

        onMounted(() => {
            // get initial data from server (1st page)
            tableRef.value.requestServerInteraction()
        })

        return {
            rows,
            loading,
            date,
            pagination,
            columns,
            onRequest,
            tableRef,
            filter,

        }
    }
}
</script>

<style scoped>

</style>