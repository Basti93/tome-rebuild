import {createApp, provide, h} from 'vue'
import App from './App.vue'
import router from "./router";
import { DefaultApolloClient } from '@vue/apollo-composable'
import apolloClient from "./apollo";

//Quasar
import {Quasar, Notify, Dialog, Dark} from 'quasar'
import quasarLang from 'quasar/lang/de'

// Import icon libraries
import '@quasar/extras/roboto-font/roboto-font.css'
import '@quasar/extras/material-icons/material-icons.css'

// Import Quasar css
import 'quasar/src/css/index.sass'
import "quasar/src/css/flex-addon.sass";

createApp({
    name: 'T.O.M.E. Rebuild',
    setup () {
        provide(DefaultApolloClient, apolloClient)
    },
    render: () => h(App),
}).use(router).use(Quasar, {
    plugins: {Notify, Dialog, Dark}, // import Quasar plugins and add here
    lang: quasarLang,
    config: {
        cssAddon: true,
    }
}).mount('#app')
