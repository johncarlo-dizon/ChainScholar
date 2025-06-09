import axios from 'axios';
import { createApp } from 'vue';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.createApp = createApp;