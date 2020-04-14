require('./bootstrap');

import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import App from './views/App'
import SubredditFinder from './views/SubredditFinder'
import SubredditGraph from './views/SubredditGraph'

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'subreddit-finder',
            component: SubredditFinder
        },
        {
            path: '/subreddit-graph',
            name: 'subreddit-graph',
            component: SubredditGraph,
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
