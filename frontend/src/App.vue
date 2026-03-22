<script setup lang="ts">
// imports
import { onMounted, onUnmounted, provide, reactive, ref, watch } from 'vue';
// extra
const backendURL = `http://127.0.0.1:8001/api/v1`;
// components
import AppHeader from '@/components/AppHeader.vue';
import AppFooter from '@/components/AppFooter.vue';
import LogModal from '@/components/modals/LoginModal.vue';
import RegModal from '@/components/modals/RegistrationModal.vue';
// types
import { type UI, AuthState } from '@/ts/types';
// entities
import type { User } from '@/ts/entities';
// services
import {
    type Services, ApiService, UserService,
    CategoryService, ProductService, I18n,
    CartService, FavoriteService, ClaimService,
    AddressService, PickupService, ShopService,
    OrderService, TagService, ConfigService,
} from '@/ts/services'

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
    const tag = new TagService(api);
    const config = new ConfigService(api);

    return {
        api, user, category,
        product, i18n, cart,
        favorite, claim, address,
        pickup, shop, order,
        tag, config, 
    }
}
const services = createServices();
// stores
import { useUserStore } from '@/stores/userStore';
import { useCartStore } from '@/stores/cartStore';
import { useFavoriteStore } from '@/stores/favoriteStore';

const [userStore, cartStore, favoriteStore, appStore]
= [useUserStore(), useCartStore(), useFavoriteStore(), useAppStore()];
// UI
import { ElMessage, ElNotification } from 'element-plus';
import { useAppStore } from './stores/appStore';
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
    categoriesVisible: false,
    currencyFormatter,
    dateFormatter,
});
const footerVisible = ref(false);
const routerViewRef = ref<HTMLElement>();
let observer: ResizeObserver | null = null;
// methods
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
                cartStore.set(cart);
                favoriteStore.set(favorite);

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
const loadConfig = async () => {
    try {
        const response = await services.config.index();

        if (response.success && response.data) {
            appStore.setConfig(response.data);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (e) {
        console.error(e);
        ElMessage.error('Произошла ошибка при конфигурации приложения. Попробуйте войти позже');
    }
}
// provides
const provides: {
    services: Services,
    ui: UI
} = {
    services: services,
    ui: ui
};
(Object.keys(provides) as Array<keyof typeof provides>).forEach(key => {
    provide(key, provides[key]);
});
// watches
watch(() => ui.darkTheme, uiMethods.changeTheme);
// logic
ui.darkTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

if (!window.location.origin.includes('localhost')) {
    ElNotification({
        title: 'Внимание',
        message: 'Это тестовый релиз для дипломного проекта, не пытайтесь воспользоваться системой',
        type: 'info'
    })
}

onMounted(async () => {
    try {
        await loadConfig();

        const state = await loadUser();
        if (state === AuthState.Reject) {
            ElMessage.warning('Не удалось войти');
        }
    } catch (error) {
        console.error('Ошибка при инициализации:', error);
        ElMessage.error('Не удалось загрузить настройки приложения');
    }

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