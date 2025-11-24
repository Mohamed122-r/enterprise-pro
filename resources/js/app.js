import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './components/Layout/AppLayout.vue';
import './bootstrap';
import '../css/app.css';

// إنشاء تطبيق Vue
const app = createApp(App);

// استخدام Pinia للإدارة الحالة
const pinia = createPinia();
app.use(pinia);

// استخدام Router
app.use(router);

// تركيب التطبيق
app.mount('#app');

// رسالة تحميل
console.log('🚀 Enterprise Pro App Loaded Successfully');
