import {createApp, provide, h} from 'vue'
import App from './App.vue'
import router from "./router";
import store from "./store";
import { DefaultApolloClient } from '@vue/apollo-composable'
import apolloClient from "./apollo";

// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { VDataTableServer } from 'vuetify/labs/VDataTable'
import {de} from 'vuetify/locale'
import '@mdi/font/css/materialdesignicons.css'



const vuetify = createVuetify({
    components: {
        ...components,
       VDataTableServer
    },
    directives,
    locale: {
        locale: 'de',
        messages: { de }
    }
})

createApp({
    setup () {
        provide(DefaultApolloClient, apolloClient)
    },
    render: () => h(App),
}).use(router).use(store).use(vuetify).mount('#app')
