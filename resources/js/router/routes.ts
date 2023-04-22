export default [
    {
        path: "/",
        redirect: "/home",
        children: [
            {
                path: "/home",
                name: "Home",
                component: () => import("@/views/Home.vue"),
            },
            {
                path: "/login",
                name: "Login",
                component: () => import("@/views/Login.vue"),
            },
            {
                path: "/register",
                name: "Register",
                component: () => import("@/views/Register.vue"),
            },
            {
                path: "/trainings",
                name: "Trainings",
                component: () => import("@/views/Trainings.vue"),
            },
            {
                path: "/training/:id",
                name: "Training",
                component: () => import("@/views/Training.vue"),
            },
            {
                path: "/users",
                name: "Users",
                component: () => import("@/views/Users.vue"),
            },
            {
                path: "/groups",
                name: "Groups",
                component: () => import("@/views/Groups.vue"),
            },
            {
                path: "/locations",
                name: "Locations",
                component: () => import("@/views/Locations.vue"),
            },
            {
                path: "/configs",
                name: "Configs",
                component: () => import("@/views/Configs.vue"),
            },
            {
                path: "/profile",
                name: "Profile",
                component: () => import("@/views/Profile.vue"),
            },
            {
                path: "/activity-log",
                name: "ActivityLog",
                component: () => import("@/views/ActivityLog.vue"),
            },
            {
                path: "/logout",
                name: "Logout",
                component: () => import("@/views/Logout.vue"),
            },
            {
                path: "/forgot-password",
                name: "ForgotPassword",
                component: () => import("@/views/ForgotPassword.vue"),
            },
            {
                path: "/verify-email",
                name: "VerifyEmail",
                component: () => import("@/views/VerifyEmail.vue"),
            },
            {
                path: "/reset-password",
                name: "ResetPassword",
                component: () => import("@/views/ResetPassword.vue"),
            }
        ],
    },
];