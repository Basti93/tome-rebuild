import {ApolloClient, from, InMemoryCache} from '@apollo/client/core'
import { setContext } from 'apollo-link-context';
import { createUploadLink } from 'apollo-upload-client'
import { onError } from "@apollo/client/link/error";
import PusherLink from "./PusherLink";
import Pusher from "pusher-js";

// XSRF token is required to make post requests to your Laravel backend
const authLink = setContext((_, { headers }) => {
    const token = localStorage.getItem('accessToken')
    return {
        headers: {
            ...headers,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            authorization: 'Bearer ' + token || ''
        },
    };
});

const errorLink = onError(({ graphQLErrors, networkError }) => {
    if (graphQLErrors) {
        graphQLErrors.forEach(({ message, locations, path }) =>
            console.log(
                `[GraphQL error]: Message: ${message}, Location: ${locations}, Path: ${path}`
            )
        );
        if (graphQLErrors.some(e => e.message === 'Unauthenticated.')) {
            localStorage.removeItem('accessToken')
            window.location.href = '/#/login'
        }
    }
    if (networkError) console.log(`[Network error]: ${networkError}`);
});

// HTTP connection to the API
const httpLink = createUploadLink({
    credentials: 'same-origin',
    uri: import.meta.env.VITE_BASE_URL + '/graphql',
})

// Cache implementation
const cache = new InMemoryCache({
});

const pusherLink = new PusherLink({
    pusher: new Pusher(import.meta.env.VITE_PUSHER_APP_KEY, {
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        authEndpoint: import.meta.env.VITE_BASE_URL + `/graphql/subscriptions/auth`,
        auth: {
            headers: {
                authorization: localStorage.getItem('accessToken'),
            },
        },
    }),
});

// Create the apollo client
const apolloClient = new ApolloClient({
    link: from([pusherLink, errorLink, authLink.concat(httpLink)]),
    cache,
})

export default apolloClient