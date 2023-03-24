<template>
  <v-card>
    <v-card-title>
      Users
      <v-spacer></v-spacer>
      <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          label="Search"
          single-line
          hide-details
      ></v-text-field>
    </v-card-title>
  <v-data-table-server
      :search="search"
      :loading="loading"
      v-model:sort-by="sortBy"
      item-value="id"
      item-title="name"
      show-select
      v-model="selected"
      v-model:items-per-page="itemsPerPage"
      :items="users"
      v-model:items-length="total"
      :headers="headers"
      v-model:page="page"
      @update:options="options = $event">
  </v-data-table-server>
  </v-card>
</template>
<script>
import {useQuery} from "@vue/apollo-composable";
import usersPaginationQuery from "../queries/userpagination.query.gql";
import {computed, reactive, ref, watch, watchEffect} from "vue";

export default {
  setup() {
    const itemsPerPage = ref(25);
    const page = ref(1);
    const sortBy = reactive([{ key: 'id', order: 'asc' }]);
    const search = ref('');
    const options = ref({});
    const selected = reactive([]);
    const headers = reactive([
      {title: 'ID', key: 'id',},
      {title: 'Name', key: 'name'},
      {title: 'E-Mail', key: 'email'},
      {title: 'Actions', key: 'actions', sortable: false},
    ]);
    const variables = ref({
      first: itemsPerPage,
      page: page,
      orderBy: [
        {
          column: sortBy[0].key.toUpperCase(),
          order: sortBy[0].order.toUpperCase()
        }
      ],
    });
    const {loading, result} = useQuery(usersPaginationQuery, variables,() => ({
      debounce: 500,
      deep: true
    }));
    const changeQueryVariables = () => {
      if (search.value.trim().length == 0) {
        delete variables.value.search;
      } else {
        variables.value.search = search.value + "%"
      }
    };
    watch(search, () => {
      changeQueryVariables();
    })
    watch(options, () => {
      changeQueryVariables();
    }, {deep: true})


    const users = computed(() =>  result.value ?.users.data ?? []);
    const total = computed(() => result.value ?.users.paginatorInfo.total ?? 0)

    return {
      result,
      search,
      total,
      users,
      loading,
      itemsPerPage,
      sortBy,
      page,
      options,
      headers,
      selected
    }
  },
}
</script>