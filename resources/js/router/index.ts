import { createRouter, createWebHashHistory } from 'vue-router';
import routes from './routes';

const router = createRouter({
    history: createWebHashHistory(import.meta.env.BASE_URL),
    routes,
});

router.beforeEach(async (to, from) => {
    if (
        // make sure the user is authenticated
        // and the route they are trying to access is not the login page or any other public page
        localStorage.getItem('accessToken') == null
        && ['Login', 'Register', 'ForgotPassword', 'VerifyEmail', 'ResetPassword'].indexOf(to.name) === -1
    ) {
        // redirect the user to the login page
        return { name: 'Login', query: { redirect: to.path} }
    }
})

export default router;