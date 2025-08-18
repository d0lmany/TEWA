<script setup>
// imports
import { provide, ref, watch } from 'vue';
// extra
const backendURL = 'http://127.0.0.1:8000/api';
// components
import Header from './components/Header.vue';
import Footer from './components/Footer.vue';
import AuthModal from './components/AuthModal.vue';
import RegModal from './components/regModal.vue';
// services
import { AuthService } from './services/AuthService';
const authService = new AuthService(backendURL);
// variables
const isAuthModalOpen = ref(false);
const isRegModalOpen = ref(false);
const isAuthenticated = ref(authService.isAuthenticated());
const user = ref(null);
const isDarkTheme = ref(false);
// methods
const openAuthModal = () => isAuthModalOpen.value = true;
const closeAuthModal = () => isAuthModalOpen.value = false;
const openRegModal = () => isRegModalOpen.value = true;
const closeRegModal = () => isRegModalOpen.value = false;
const openAuth = () => {
  closeRegModal();
  openAuthModal();
};
// provides
const provides = {
  authModal: {
    open: openAuthModal,
    close: closeAuthModal
  },
  backendURL: backendURL,
  authService: authService,
  API: authService.getApiInstance(),
  authState: { isAuthenticated, user },
  isDarkTheme: isDarkTheme,
  regModal: {
    open: openRegModal,
    close: closeRegModal
  }
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
</script>

<template>
  <Header/>
  <RouterView style="padding-top: 4.5rem;"/>
  <Footer/>
  <AuthModal 
    :visible="isAuthModalOpen"
    @close="closeAuthModal"
    />
  <RegModal
    :visible="isRegModalOpen"
    @login-click="openAuth"
    />
</template>