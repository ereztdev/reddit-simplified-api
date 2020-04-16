require('./bootstrap');
import Vue from 'vue'
import VueRouter from 'vue-router'
import App from './views/App'
Vue.use(VueRouter);
Vue.prototype.$http = axios;

const app = new Vue({
    el: '#app',
    components: {App},
});
