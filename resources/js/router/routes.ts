export default [
    {
        path: "/",
        redirect: { path: "/home" },
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
        ],
    },
];