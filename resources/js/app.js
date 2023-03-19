import {createApp, provide, h} from 'vue'
import App from './App.vue'
import router from "./router";
import { DefaultApolloClient } from '@vue/apollo-composable'
import apolloClient from "./apollo";

// Vuetify
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'


const vuetify = createVuetify({
    components,
    directives,
})

createApp({
    setup () {
        provide(DefaultApolloClient, apolloClient)
    },
    render: () => h(App),
}).use(router).use(vuetify).mount('#app')
