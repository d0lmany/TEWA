<script setup lang="ts">
// imports
import { onMounted, onUnmounted, provide, reactive, ref, watch } from 'vue';
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
import ShopService from "@/ts/services/ShopService";
import OrderService from '@/ts/services/OrderService';

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
    const shop = new ShopService(api);
    const order = new OrderService(api);

    return {
        api, user, category,
        product, i18n, cart,
        favorite, claim, address,
        pickup, shop, order,
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
const footerVisible = ref(false);
const routerViewRef = ref<HTMLElement>();
let observer: ResizeObserver | null = null;
// methods
import { AuthState } from '@/ts/types/AuthState';
import type { User } from '@/ts/entities/User';
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
    const userCondition = await services.user.isAuthenticated();
    if (userCondition) {
        try {
            const userData = await services.user.loadUserData({
                loadUser: false,
            });

            if (userData.success && userData.data) {
                const {cart, favorite, errors} = userData.data;
                if (!cart || !favorite) {
                    ElMessage.warning([errors.cart, errors.favorite].join('. '));
                    console.error(errors.cart, errors.favorite)
                }

                userStore.login(userCondition as User)
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
const checkHeight = () => {
    if (!routerViewRef.value) return
    
    const routerViewHeight = routerViewRef.value.scrollHeight
    const windowHeight = window.innerHeight
    
    footerVisible.value = routerViewHeight < windowHeight
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

    observer = new ResizeObserver(checkHeight)
    
    if (routerViewRef.value && observer) {
        observer.observe(routerViewRef.value)
    }

    window.addEventListener('resize', checkHeight)

    checkHeight()
})

onUnmounted(() => {
    if (observer) {
        observer.disconnect()
    }
    window.removeEventListener('resize', checkHeight)
})
</script>
<template>
    <app-header/>
    <div class="view" ref="routerViewRef">
        <router-view/>
    </div>
    <app-footer :visible="footerVisible"/>
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