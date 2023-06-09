<template>
    <q-page>
        <div class="row q-ma-md">
            <q-table
                    class="col-12"
                    ref="tableRef"
                    title="Users"
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
                    :visible-columns="visibleColumns"
            >
                <template v-slot:top>
                    <div class="row full-width">
                        <div class="col">
                            <p class="text-h4">Mitglieder</p>
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
                                        :options="columns.filter(c => c.name === 'firstname' || c.name === 'nickname' || c.name === 'lastname')"
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
                            header-class="bg-primary text-white"
                            expand-icon-class="text-white"
                            label="Filtereinstellungen"
                            caption="Nach Gruppen, Rollen und Freigabe filtern"
                    >
                        <q-card flat bordered>
                            <div class="row q-ma-lg">
                                <div class="col-12 col-lg-5 q-pt-md ">
                                    <q-btn-toggle
                                            v-model="approvedToggle"
                                            @update:model-value="pagination.page = 1; tableRef.requestServerInteraction()"
                                            :options="[
                                { label: 'Alle', value: 'all' },
                                { label: 'Freigeschaltet', value: 'approved' },
                                { label: 'Nicht freigeschaltet', value: 'notApproved' }
                              ]"
                                            toggle-color="primary"
                                            rounded/>
                                </div>
                                <div class="col-12 col-lg-5 q-pt-md">
                                    <q-btn-toggle
                                            v-model="roleToggle"
                                            @update:model-value="pagination.page = 1; tableRef.requestServerInteraction()"
                                            :options="roleToggleOptions"
                                            toggle-color="primary"
                                            rounded>
                                    </q-btn-toggle>
                                </div>
                                <div class="col-12 col-lg-4 q-pt-md">
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
                            </div>
                        </q-card>
                    </q-expansion-item>
                </template>

                <template v-slot:body="props">
                    <q-tr :props="props">
                        <q-td key="id" :props="props">{{ props.row.id }}</q-td>
                        <q-td key="profile_image" :props="props">
                            <q-avatar>
                                <q-img :src="props.row.imageUrl"/>
                            </q-avatar>
                        </q-td>
                        <q-td key="nickname" :props="props">{{ props.row.nickname }}
                            <q-popup-edit :model-value="props.row.nickname" v-slot="scope"
                                          @save="(value) => updateUser(props.row.id, 'nickname', value)">
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
                            <q-popup-edit :model-value="props.row.firstname" v-slot="scope"
                                          @save="(value) => updateUser(props.row.id, 'firstname', value)">
                                <q-input v-model="scope.value"
                                         dense autofocus
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

                        <q-td key="lastname" :props="props">{{ props.row.lastname }}
                            <q-popup-edit :model-value="props.row.lastname" v-slot="scope"
                                          @save="(value) => updateUser(props.row.id, 'lastname', value)">
                                <q-input v-model="scope.value"
                                         dense autofocus
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
                        <q-td key="phone" :props="props">
                            <p><a :href="'tel:' + props.row.phone">
                                <q-icon name="phone"/>
                                {{ props.row.phone }}</a></p>
                            <p><a :href="'https://wa.me/' + props.row.phone">Whatsapp</a></p>
                        </q-td>
                        <q-td key="email" :props="props"><a
                                :href="'mailto:' + props.row.email">{{ props.row.email }}</a></q-td>
                        <q-td key="birthdate" :props="props">{{ date.formatDate(props.row.birthdate, 'DD.MM.YYYY') }}
                            <q-popup-edit :model-value="props.row.birthdate" v-slot="scope"
                                          @save="(value) => updateUser(props.row.id, 'birthdate', value)">

                                <q-input type="date" :v-model="date.formatDate(scope.value, 'DD.MM.YYYY')" mask="date"
                                         :rules="[val => !val || /^-?[\d]+\.[0-1]\d\.[0-3]\d$/.test(val) || 'Falsches Format']"
                                         dense autofocus @keyup.enter="scope.set">
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
                            <q-btn v-if="props.row.approved"
                                   :disable="props.row.roles.some(r => ['admin', 'coach'].includes(r.name))"
                                   @click="removeAthleteRole(props.row)" size="xs" outline round icon="check"
                                   color="green"/>
                            <q-btn v-if="!props.row.approved" @click="addAthleteRole(props.row)" size="xs" outline round
                                   icon="clear" color="red"/>
                        </q-td>

                        <q-td key="groups" :props="props">
                            {{ props.row.groups.map(g => g.name).toString() }}
                            <q-popup-edit :model-value="props.row.groups" v-slot="scope"
                                          @save="(value) => updateUser(props.row.id, 'groups', value.map(g => g.id))">
                                <q-select v-model="scope.value" multiple dense use-chips autofocus
                                          @keyup.enter="scope.set" :options="groups" option-value="id"
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
                        <q-td key="hasRole" :props="props">
                            {{ props.row.roles.map(r => r.name).toString() }}
                            <q-popup-edit :model-value="props.row.roles" v-slot="scope"
                                          @save="(value) => updateUser(props.row.id, 'roles', value.map(r => r.id))">
                                <q-select v-model="scope.value" multiple dense autofocus @keyup.enter="scope.set"
                                          :options="roles" option-value="id" option-label="name">
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
                                    <q-item clickable @click="confirmDelete(props.row)">
                                        <q-item-section avatar>
                                            <q-icon name="person_remove"/>
                                        </q-item-section>
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

import usersPaginationQuery from "../queries/userpagination.query.gql";
import rolesQuery from "../queries/roles.query.gql";
import groupsQuery from "../queries/groups.query.gql";
import deleteUsersMutation from "../queries/userdelete.mutation.gql";
import updateUsersMutation from "../queries/userupdate.mutation.gql";
import {computed, onMounted, ref} from "vue";
import apolloClient from "../apollo";
import {date} from 'quasar'
import {useQuasar} from 'quasar'
import {useQuery} from "@vue/apollo-composable";


export default {

    setup() {
        const $q = useQuasar()
        const tableRef = ref()
        const visibleColumns = ref(['id', 'profile_image', 'firstname', 'lastname', 'birthdate', 'phone', 'approved', 'groups', 'actions']);
        const filter = ref('')
        const filterCol = ref('firstname')
        const approvedToggle = ref('all');
        const roleToggle = ref('3');
        const groupsSelection = ref(null);
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
            {name: 'profile_image', label: 'Bild', align: 'left', field: '', sortable: false},
            {name: 'nickname', align: 'center', label: 'Spitzname', field: 'nickname', sortable: true},
            {name: 'firstname', align: 'center', label: 'Vorname', field: 'firstname', sortable: true},
            {name: 'lastname', align: 'center', label: 'Nachname', field: 'lastname', sortable: true},
            {name: 'phone', align: 'center', label: 'Telefon', field: 'phone', sortable: false},
            {name: 'email', align: 'center', label: 'E-Mail', field: 'email', sortable: false},
            {name: 'birthdate', align: 'center', label: 'Geburtsdatum', field: 'birthdate', sortable: true,},
            {name: 'approved', align: 'center', label: 'Freigeschaltet', field: 'approved', sortable: false,},
            {name: 'groups', align: 'center', label: 'Gruppen', field: 'groups', sortable: false,},
            {name: 'hasRole', align: 'center', label: 'Rollen', field: 'roles', sortable: false,},
            {name: 'actions', align: 'center', label: 'Aktionen', field: '', sortable: false,},
        ]
        const {result: rolesResult} = useQuery(rolesQuery);
        const {result: groupsResult} = useQuery(groupsQuery);
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
        const groups = computed(() => groupsResult.value?.groups ?? [])

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
            // calculate starting row of data
            const filter = props.filter
            const variables = {
                first: rowsPerPage,
                page: page,
            };
            if (sortBy) {
                variables.orderBy = [
                    {
                        column: sortBy.toUpperCase(),
                        order: descending ? 'DESC' : 'ASC',
                    }
                ]
            }
            if (filter) {
                variables[`${filterCol.value}`] = "%" + filter + "%";
            }
            if (roleToggle.value != 'all') {
                variables[`hasRole`] = {
                    column: 'ID',
                    value: roleToggle.value
                }
            }
            if (groupsSelection.value && groupsSelection.value.length > 0) {
                addToWhere(variables, 'groups', groupsSelection.value);
            }
            if (approvedToggle.value !== 'all') {
                variables['approved'] = approvedToggle.value == 'approved';
            }

            apolloClient.query({
                query: usersPaginationQuery,
                variables: variables
            }).then(({data}) => {
                pagination.value.rowsNumber = data.usersPagination.paginatorInfo.total
                rows.value.splice(0, rows.value.length, ...data.usersPagination.data)
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

        const confirmDelete = (user) => {
            $q.dialog({
                title: 'Löschen Bestätigen',
                message: 'Mitglied "' + user['id'] + ' - ' + user['firstname'] + ' ' + user['lastname'] + '" wirklich löschen?',
                cancel: true,
                html: true,
                persistent: true
            }).onOk(() => {
                deleteUser(user)
            })
        }
        const deleteUser = (user) => {
            loading.value = true
            apolloClient.mutate({
                mutation: deleteUsersMutation,
                variables: {id: user.id}
            }).then(({data}) => {
                $q.notify({
                    message: 'Benutzer ' + data.deleteUser.firstname + " " + data.deleteUser.lastname + ' gelöscht',
                    color: 'positive'
                })
                const index = rows.value.indexOf(user);
                if (index > -1) { // only splice array when item is found
                    rows.value.splice(index, 1); // 2nd parameter means remove one item only
                }
            }).catch(() => {
                $q.notify({
                    message: 'Benutzer ' + user.firstname + " " + user.lastname + ' konnte nicht gelöscht werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false
            })
        }

        const updateUser = (userId, field, value) => {
            loading.value = true;
            if (field == 'groups') {
                value = {
                    sync: value
                }
            } else if (field == 'roles') {
                value = {
                    sync: value
                }
            }
            apolloClient.mutate({
                mutation: updateUsersMutation,
                variables: {id: userId, [field]: value}
            }).then(({data}) => {
                $q.notify({
                    message: 'Benutzer ' + data.updateUser.firstname + " " + data.updateUser.lastname + ' aktualisiert',
                    color: 'positive'
                })
                const index = rows.value.findIndex(row => row.id == data.updateUser.id);
                if (index > -1) { // only splice array when item is found
                    rows.value[index] = data.updateUser
                }
            }).catch(() => {
                $q.notify({
                    message: 'Benutzer konnte nicht aktualisiert werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false;
            })
        }

        const addAthleteRole = (user) => {
            loading.value = true;
            const existingRoles = [...user.roles];
            existingRoles.push({
                id: 3,
                name: 'athlete'
            });
            apolloClient.mutate({
                mutation: updateUsersMutation,
                variables: {id: user.id, 'roles': {sync: existingRoles.map(role => role.id)}}
            }).then(({data}) => {
                $q.notify({
                    message: 'Benutzer ' + data.updateUser.firstname + " " + data.updateUser.lastname + ' aktualisiert',
                    color: 'positive'
                })
                const index = rows.value.findIndex(row => row.id == data.updateUser.id);
                if (index > -1) { // only splice array when item is found
                    rows.value[index] = data.updateUser
                }
            }).catch(() => {
                $q.notify({
                    message: 'Benutzer konnte nicht aktualisiert werden',
                    color: 'negative'
                })
            }).finally(() => {
                loading.value = false;
            })

        }
        const removeAthleteRole = (user) => {
            loading.value = true;
            const existingRoles = [...user.roles];
            const index = existingRoles.findIndex(role => role.name == 'athlete');
            if (index > -1) {
                existingRoles.splice(index, 1);
            }
            apolloClient.mutate({
                mutation: updateUsersMutation,
                variables: {
                    id: user.id,
                    'roles': {sync: existingRoles.map(role => role.id)}
                }
            }).then(({data}) => {
                $q.notify({
                    message: 'Benutzer ' + data.updateUser.firstname + " " + data.updateUser.lastname + ' aktualisiert',
                    color: 'positive'
                })
                const index = rows.value.findIndex(row => row.id == data.updateUser.id);
                if (index > -1) { // only splice array when item is found
                    rows.value[index] = data.updateUser
                }
            }).catch(() => {
                $q.notify({
                    message: 'Benutzer konnte nicht aktualisiert werden',
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
            approvedToggle,
            roleToggle,
            groupsSelection,
            loading,
            onRequest,
            pagination,
            columns,
            deleteUser,
            confirmDelete,
            updateUser,
            removeAthleteRole,
            addAthleteRole,
            filterCol,
            roles,
            roleToggleOptions,
            groups,
            date,
            computed,
            visibleColumns,
        }
    },
}
</script>