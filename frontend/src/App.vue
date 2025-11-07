<script setup>
// imports
import { provide, ref, watch } from 'vue';
// extra
const backendURL = 'http://127.0.0.1:8000/api';
const storageURL = 'http://127.0.0.1:8000/storage';
const currency = 'RUB';
// components
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import LogModal from './components/modals/Login.vue';
import RegModal from './components/modals/Registration.vue';
// services
import AuthService from './services/AuthService';
import I18nService from './services/I18nService';
import ProductService from './services/ProductService';
import CategoryService from './services/CategoryService';
import CartService from './services/CartService';
import FavoriteService from './services/FavoriteService';
import ClaimService from './services/ClaimService';
import UserService from './services/UserService';
const createServices = () => {
  const auth = new AuthService(backendURL);
  const API = auth.getApiInstance();
  const i18n = new I18nService();
  const product = new ProductService(API);
  const category = new CategoryService(API);
  const cart = new CartService(API);
  const favorite = new FavoriteService(API);
  const claim = new ClaimService(API);
  const user = new UserService(cart, favorite);

  return { auth, i18n, product,
    category, cart, favorite,
    claim, user };
}
const services = createServices();
// stores
import { useUserStore } from './stores/userStore';
import { ElMessage } from 'element-plus';
const userStore = useUserStore();
userStore.setIsAuth(services.auth.isAuthenticated());
// UI
const modals = ref({
  authOpen: false,
  regOpen: false,
});
modals.value.login = () => {
  modals.value.regOpen = false;
  modals.value.authOpen = true;
}
const isDarkTheme = ref(false);
// methods
const callRegFromAuth = () => {
  modals.value.authOpen = false;
  modals.value.regOpen = true;
}
const callAuthFromReg = () => {
  modals.value.regOpen = false;
  modals.value.authOpen = true;
}
const loadUserData = async () => {
  const result = await services.user.loadAndSave();
  if (result) ElMessage.warning('Не все данные были загружены');
}
// provides
const provides = {
  // API
  backendURL: backendURL,
  storageURL: storageURL,
  // services
  services: services,
  // UI
  isDarkTheme: isDarkTheme,
  currency: currency,
  modals: modals.value,
  globalMethods: { loadUserData }
};
Object.keys(provides).forEach(key => {
  provide(key, provides[key]);
});
// watches
watch(isDarkTheme, (value) => {
  document.documentElement.setAttribute('class', value? 'dark': '');
});
// logic
isDarkTheme.value = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
if (userStore.isAuth) {
  loadUserData();
}
</script>

<template>
  <Header/>
  <router-view class="view"/>
  <Footer/>
  <log-modal
    v-model="modals.authOpen"
    @callReg="callRegFromAuth"
  />
  <reg-modal
    v-model="modals.regOpen"
    @callAuth="callAuthFromReg"
  />
</template>
<style scoped>
.view {
  margin-top: 4.5rem !important;
}
@media (max-width: 1250px) {
  .view {
    margin-top: 7rem !important;
  }
}
</style>