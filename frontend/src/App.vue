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
import AuthModal from './components/AuthModal.vue';
import RegModal from './components/RegModal.vue';
// services
import AuthService from './services/AuthService';
import I18nService from './services/I18nService';
import ProductService from './services/ProductService';
import CategoryService from './services/CategoryService';
import CartService from './services/CartService';
import FavoriteService from './services/FavoriteService';
import ClaimService from './services/ClaimService';
const createServices = () => {
  const auth = new AuthService(backendURL);
  const API = auth.getApiInstance();
  const i18n = new I18nService();
  const product = new ProductService(API);
  const category = new CategoryService(API);
  const cart = new CartService(API);
  const favorite = new FavoriteService(API);
  const claim = new ClaimService(API);

  return { auth, i18n, product,
    category, cart, favorite,
    claim };
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
const closeAuth = () => {
  modals.value.authOpen = false;
}
const closeReg = () => {
  modals.value.regOpen = false;
}
const setCart = async () => {
  const response = await services.cart.index();

  if (response.success) {
    userStore.setCart(response.data.data);
  } else {
    ElMessage.error(`Не удалось загрузить корзину: ${response.message}`);
  }
}
const setFavorite = async () => {
  const response = await services.favorite.index();

  if (response.success) {
    userStore.setFavorite(response.data.data);
  } else {
    ElMessage.error(`Не удалось загрузить избранное: ${response.message}`);
  }
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
  setCart();
  setFavorite();
}
</script>

<template>
  <Header/>
  <RouterView class="view"/>
  <Footer/>
  <AuthModal
    :visible="modals.authOpen"
    @close="closeAuth"
    @callReg="callRegFromAuth"
  />
  <RegModal
    :visible="modals.regOpen"
    @close="closeReg"
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