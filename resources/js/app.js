require('./bootstrap');

import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import App from './views/App'
import PostGraph from './views/PostGraph'

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'subreddit-finder',
            component: App
        },
        {
            path: '/subreddit-graph',
            name: 'subreddit-graph',
            component: PostGraph,
        },
    ],
});

Vue.prototype.$http = axios

const app = new Vue({
    el: '#app',
    // data:{route:Vue.$route},
    components: {App},
    router,

});
