import apolloClient from "../../apollo";
import loginMutation from "../../queries/login.mutation.gql"
import logoutMutation from "../../queries/logout.mutation.gql"
import meQuery from "../../queries/me.query.gql"
import router from '../../router'

export default {
    namespaced: true,
    state: {
        accessToken: null,
        user: null,
        loggingIn: false,
        loginError: null
    },
    mutations: {
        loginStart: state => state.loggingIn = true,
        loginStop: (state, errorMessage) => {
            state.loggingIn = false;
            state.loginError = errorMessage;
        },
        updateAccessToken: (state, accessToken) => {
            state.accessToken = accessToken;
        },
        updateUser: (state, user) => {
            state.user = user;
        }
    },
    actions: {
        doLogout({commit}) {
            apolloClient.mutate({
                mutation: logoutMutation
            }).then(response => {
                commit('updateAccessToken', null);
                commit('updateUser', null);
                localStorage.removeItem('accessToken');
            })
        },
        doLogin({commit}, loginData) {
            commit('loginStart');
            apolloClient.mutate({
                mutation: loginMutation,
                variables: { email: loginData.email, password: loginData.password, },
            }).then(response => {
                localStorage.setItem('accessToken', response.data.login.token);
                commit('updateAccessToken', response.data.login.token);
                commit('loginStop', null);
                router.push("/home")
            }).catch(error => {
                commit('loginStop', error.message);
                commit('updateAccessToken', null);
                localStorage.removeItem('accessToken');
                commit('updateUser', null);
            });

        },
        getMe({ commit }) {
            apolloClient.query({query: meQuery}).then((response => {
                if (response.data.me != null) {
                    commit('updateUser', response.data.me);
                } else {
                    localStorage.removeItem('accessToken');
                }
            }))
        },
        fetchAccessToken({commit}) {
            commit('updateAccessToken', localStorage.getItem('accessToken'));
        }
    }
}