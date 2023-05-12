import {createApp, provide, h} from 'vue'
import App from './App.vue'
import router from "./router";
import { DefaultApolloClient } from '@vue/apollo-composable'
import apolloClient from "./apollo";
import {createI18n} from 'vue-i18n'

//Quasar
import {Quasar, Notify, Dialog, Dark} from 'quasar'
import quasarLang from 'quasar/lang/de'

// Import icon libraries
import '@quasar/extras/roboto-font/roboto-font.css'
import '@quasar/extras/material-icons/material-icons.css'

// Import Quasar css
import 'quasar/src/css/index.sass'
import "quasar/src/css/flex-addon.sass";


import i18nMessages from "./i18nMessages";
const i18n = createI18n({
    legacy: false,
    locale: "de",
    globalInjection: true,
    messages: i18nMessages
});

createApp({
        name: 'T.O.M.E. Rebuild',
        setup () {
            provide(DefaultApolloClient, apolloClient)
        },
        render: () => h(App),
    })
    .use(router)
    .use(Quasar, {
        plugins: {Notify, Dialog, Dark}, // import Quasar plugins and add here
        lang: quasarLang,
        config: {
            cssAddon: true,
        },
    })
    .use(i18n)
    .mount('#app')
