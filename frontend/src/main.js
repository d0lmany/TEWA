import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

import ElementPlus from 'element-plus'
import ru from 'element-plus/es/locale/lang/ru'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import './css/global.css';
import '@fontsource/inter/400.css'
import '@fontsource/inter/500.css'
import '@fontsource/inter/600.css'
import '@fontsource/inter/700.css'

const app = createApp(App);

app.use(router);
app.use(ElementPlus, {
    locale: ru  
});

app.mount('#app');
