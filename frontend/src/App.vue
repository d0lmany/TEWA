<script setup lang="ts">
// imports
import { onMounted, provide, reactive, watch } from 'vue';
// extra
const backendURL = `http://127.0.0.1:8000/api/v1`;
// components
import AppHeader from '@/components/AppHeader.vue';
import AppFooter from '@/components/AppFooter.vue';
import LogModal from '@/components/modals/LoginModal.vue';
import RegModal from '@/components/modals/RegistrationModal.vue';
// services
import type Services from '@/ts/types/Services';
import ApiService from '@/ts/services/ApiService';
import UserService from '@/ts/services/UserService';
import CategoryService from '@/ts/services/CategoryService';
import ProductService from '@/ts/services/ProductService';
import I18n from '@/ts/services/I18n';
import CartService from '@/ts/services/CartService';
import FavoriteService from '@/ts/services/FavoriteService';
import ClaimService from '@/ts/services/ClaimService';
import AddressService from "@/ts/services/AddressService";
import PickupService from "@/ts/services/PickupService";

const createServices = (): Services => {
    const api = new ApiService(backendURL);
    const user = new UserService(api);
    const category = new CategoryService(api);
    const product = new ProductService(api);
    const i18n = new I18n();
    const cart = new CartService(api);
    const favorite = new FavoriteService(api);
    const claim = new ClaimService(api);
    const address = new AddressService(api);
    const pickup = new PickupService(api);

    return {
        api, user, category,
        product, i18n, cart,
        favorite, claim, address,
        pickup,
    }
}
const services = createServices();
// stores
import { useUserStore } from '@/stores/userStore';
const userStore = useUserStore();
// UI
import { ElMessage, ElNotification } from 'element-plus';
import type { UI } from '@/ts/types/Provides';
const currencyFormatter = new Intl.NumberFormat(navigator.language, {
    style: 'currency',
    currency: 'RUB',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2
});
const dateFormatter = new Intl.DateTimeFormat(navigator.language, {
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
});
const ui = reactive<UI>({
    darkTheme: false,
    regVisible: false,
    loginVisible: false,
    currencyFormatter,
    dateFormatter,
});
// methods
import { AuthState } from '@/ts/types/AuthState';
const uiMethods = {
    callReg: () => {
        ui.loginVisible = false;
        ui.regVisible = true;
    },
    callLog: () => {
        ui.regVisible = false;
        ui.loginVisible = true;
    },
    changeTheme: () => document.documentElement.setAttribute('class', ui.darkTheme ? 'dark' : ''),
}
/**
 * Uploads the user data
 * @returns Auth state
 */
const loadUser = async (): Promise<AuthState> => {
    services.api.authToken = UserService.storedToken;
    if (await services.user.isAuthenticated()) {

        try {
            const userData = await services.user.loadUserData();

            if (userData.success && userData.data) {
                const {cart, favorite, user, errors} = userData.data;
                if (!cart || !favorite || !user) {
                    ElMessage.warning([errors.cart, errors.favorite, errors.user].join('. '));
                    console.error(errors.cart, errors.favorite, errors.user)
                }

                userStore.login(user);
                userStore.setCart(cart);
                userStore.setFavorite(favorite);

                return AuthState.Accept;
            }
            
            return AuthState.Reject;
        } catch (error) {
            console.error(error);
            return AuthState.Reject;
        }
    }

    return AuthState.NotTry;
}
// provides
const provides: {
    services: Services,
    ui: UI,
    links: Record<string, string>
} = {
    services: services,
    ui: ui,
    links: {
        backendURL
    }
};
(Object.keys(provides) as Array<keyof typeof provides>).forEach(key => {
    provide(key, provides[key]);
});
// watches
watch(() => ui.darkTheme, uiMethods.changeTheme);
// logic
ui.darkTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

if (window.location.origin !== 'http://localhost:5173') {
    ElNotification({
        title: 'Внимание',
        message: 'Это тестовый релиз для дипломного проекта, не пытайтесь воспользоваться системой',
        type: 'info'
    })
}

onMounted(() => {
    loadUser()
        .then(state => {
            if (state === AuthState.Reject) {
                ElMessage.warning('Не удалось войти');
            }
        })
        .catch(error => {
            console.error('Unexpected error in loadUser:', error);
            ElMessage.error('Произошла непредвиденная ошибка');
        });
})
</script>
<template>
    <app-header/>
    <div class="view">
        <router-view/>
    </div>
    <app-footer/>
    <log-modal
        v-model="ui.loginVisible"
        @callReg="uiMethods.callReg"
    />
    <reg-modal
        v-model="ui.regVisible"
        @callLog="uiMethods.callLog"
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