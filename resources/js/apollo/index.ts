import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client/core'
import { setContext } from 'apollo-link-context';

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

// HTTP connection to the API
const httpLink = createHttpLink({
    credentials: 'same-origin',
    uri: import.meta.env.VITE_BASE_URL + '/graphql',
})

// Cache implementation
const cache = new InMemoryCache()

// Create the apollo client
const apolloClient = new ApolloClient({
    link: authLink.concat(httpLink),
    cache,
})

export default apolloClient