import { createRouter, createWebHashHistory } from 'vue-router';
import routes from './routes';

const router = createRouter({
    history: createWebHashHistory(import.meta.env.BASE_URL),
    routes,
});

router.beforeEach(async (to, from) => {
    if (
        // make sure the user is authenticated
        //!isAuthenticated &&
        // Avoid an infinite redirect
        localStorage.getItem('accessToken') == null && to.name !== 'Login' && to.name !== 'Register' && to.name !== 'VerifyEmail' && to.name !== 'ForgotPassword' && to.name !== 'ResetPassword'
    ) {
        // redirect the user to the login page
        return { name: 'Login' }
    }
})

export default router;