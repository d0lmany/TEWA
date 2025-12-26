<script setup lang="ts">
import CartProductCard from '@/components/cards/CartProductCard.vue';
import { useUserStore } from '@/stores/userStore';
import type { CartProduct } from '@/ts/entities/Items';
import type Services from '@/ts/types/Services';
import { ElMessage } from 'element-plus';
import { InfoFilled, ShoppingBag, Top } from '@element-plus/icons-vue';
import { inject, reactive, ref, watch, computed } from 'vue';
import type { UI } from '@/ts/types/Provides';

const {
    cart: CartService,
    favorite: FavoriteService,
} = inject('services') as Services;
const userStore = useUserStore();
const loading = ref<boolean>(false);
const cart = reactive<CartProduct[]>([]);
const cartForbidden = reactive<CartProduct[]>([]);
const checkedCartItems = computed<CartProduct[]>(() => cart.filter(item => item.checked));
const totalCheckedCartItems = computed<number>(() => checkedCartItems.value.reduce((total: number, item: CartProduct) => total + ((item.product.price.total || item.product.price.final_price) * item.quantity), 0));
const formatter = (inject('ui') as UI).currencyFormatter;
const isAllChecked = computed<boolean>(() => checkedCartItems.value.length === cart.length);

const loadCart = async () => {
    if (userStore.cart.length === cart.length + cartForbidden.length) return;

    try {
        loading.value = true;

        const response = await CartService.index();

        if (response.success && response.data) {
            cart.splice(0);
            cartForbidden.splice(0);

            cart.push(...response.data.filter((cartItem: CartProduct) => cartItem.product.quantity > 0 && cartItem.product.status === 'on'));
            cartForbidden.push(...response.data.filter((cartItem: CartProduct) => cartItem.product.quantity === 0 || cartItem.product.status !== 'on'))

            checkFavorites();
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось загрузить корзину');
    } finally {
        loading.value = false;
    }
}
const increase = async (item: CartProduct) => {
    if (item.quantity < (item.product.quantity || 0)) {
        item.quantity++;
        try {
            const response = await CartService.update(item.id || 0, {
                quantity: item.quantity
            });

            if (response.success) {
                userStore.updateCartItem(item.id || 0, item.quantity);
            } else {
                item.quantity--;
                console.error(response);
                throw new Error(response.message);
            }
        } catch (error) {
            ElMessage.error(error instanceof Error ? error.message : 'Не удалось обновить количество товара');
        }
    }
}
const deleteItem = async (item: CartProduct, forForbiddenCart: boolean = false) => {
    try {
        const response = await CartService.destroy(item.id || 0);
        if (response.success) {
            ElMessage.success(`${item.product.name} - удалён из корзины`);
            userStore.removeCartItem(item.id || 0);
            const index = (forForbiddenCart ? cartForbidden : cart).findIndex(cartItem => cartItem.id === item.id);
            if (index) (forForbiddenCart ? cartForbidden : cart).splice(index, 1);
        } else {
            console.error(response);
            throw new Error('Не удалось удалить товар из корзины');
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Неизвестная ошибка');
    }
}
const decrease = async (item: CartProduct) => {
    if (item.quantity <= 0) return;

    item.quantity--;
    try {
        if (item.quantity === 0) {
            await deleteItem(item);
        } else {
            const response = await CartService.update(item.id || 0, {
                quantity: item.quantity
            });

            if (response.success) {
                userStore.updateCartItem(item.id || 0, item.quantity);
            } else {
                console.error(response);
                throw new Error(`Не удалось обновить количество товара: ${response.message}`);
            }
        }
    } catch (error) {
        item.quantity++;
        ElMessage.error(error instanceof Error ? error.message : 'Неизвестная ошибка');
    }
}
const checkFavorites = () => {
    // TODO: как сделаем отдельный cart/favorite store - пусть подсвечивается и добавляется в каком списке хранится товар
    cart.forEach(item => item.isFavorite = !!userStore.getFavoriteItem(item.product.id))
    cartForbidden.forEach(item => item.isFavorite = !!userStore.getFavoriteItem(item.product.id))
}
const toggleFavorite = async (item: CartProduct) => {
    try {
        const response = await FavoriteService.toggle(item.product.id || 0);

        if (response.success) {
            if (response.message === 'added') {
                ElMessage.success(`${item.product.name} - добавлено в избранное`);
                userStore.addToFavorite(response.data);
                item.isFavorite = true;
            } else {
                // TODO: некорректно удаляется товар из избранного
                // решение?: по нажатию на кнопку, будет вызываться метод, который уберёт товар из ВСЕХ списков
                // либо хранить в карточке товара ещё и то в каком списке он лежит
                ElMessage.success(`${item.product.name} - удалено из избранного`);
                userStore.removeFromFavoriteByProductId(item.product.id);
                item.isFavorite = false;
            }
        } else {
            console.error(response);
            throw new Error(`Не удалось добавить/убрать товар из избранного: ${response.message}`);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Ошибка при манипуляции со списком избранного');
    }
}
const setCheckedForAll = (definition: boolean) => cart.forEach(item => item.checked = definition);
const destroyRange = async () => {
    try {
        const items = checkedCartItems.value.map(item => item.id || 0);
        const response = await CartService.destroyRange(items);

        if (response.success) {
            ElMessage.success('Товары удалены');
            items.forEach(item => {
                const inCartItem = cart.findIndex(cartItem => cartItem.id === item)
                if (inCartItem) cart.splice(inCartItem, 1);
            });
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось удалить товары');
    }
}

watch(
    () => Object.keys(userStore.favorite).length,
    checkFavorites
)
watch(
    () => userStore.cart.length,
    async () => await loadCart()
)

loadCart();
</script>
<template>
<div class="container">
    <main>
        <div class="flex">
            <h1 class="section-header">Корзина</h1>
            <div class="flex gap">
                <el-text size="large">Товаров всего: {{ userStore.cart.length }}</el-text>
                <el-button v-if="!isAllChecked" @click="setCheckedForAll(true)">Выбрать все</el-button>
                <el-button v-else @click="setCheckedForAll(false)">Убрать все</el-button>
                <el-button @click="destroyRange">Удалить выбранные</el-button>
            </div>
        </div>
        <el-empty
            v-if="loading"
            description="Загружаю..."
        />
        <el-empty
            v-else-if="!cart.length && !cartForbidden.length"
            description="Корзина пуста"
        />
        <div class="contents" v-else>
            <div class="flex low gap" v-if="cart.length">
                <h2 class="section-header">Можно заказать</h2>
                <el-text>{{ cart.length }}</el-text>
            </div>
            <section class="cards" v-if="cart.length">
                <cart-product-card
                    v-for="cartItem in cart"
                    :key="cartItem.id"
                    :item="cartItem"
                    :allow-check="true"
                    @decrease="decrease(cartItem)"
                    @increase="increase(cartItem)"
                    @delete="deleteItem(cartItem)"
                    @toggle-favorite="toggleFavorite(cartItem)"
                />
            </section>
            <div class="flex low gap" v-if="cartForbidden.length">
                <h2 class="section-header">Нельзя заказать</h2>
                <el-text>{{ cartForbidden.length }}</el-text>
                <el-popover :width="250">
                    <div style="text-align: center">
                        Такие товары возможно закончились, либо продавец снял их с продажи
                    </div>
                    <template #reference>
                        <el-icon><info-filled/></el-icon>
                    </template>
                </el-popover>
            </div>
            <section class="cards" v-if="cartForbidden.length">
                <cart-product-card
                    v-for="cartItem in cartForbidden"
                    :key="cartItem.id"
                    :item="cartItem"
                    @delete="deleteItem(cartItem, true)"
                    @toggle-favorite="toggleFavorite(cartItem)"
                />
            </section>
            <el-backtop>
                <el-icon :size="24"><Top/></el-icon>
            </el-backtop>
        </div>
    </main>
    <aside>
        <div class="flex">
            <el-text size="large">Товары ({{ checkedCartItems.length }})</el-text>
            <el-text size="large">{{ formatter.format(totalCheckedCartItems) }}</el-text>
        </div>
        <el-button
            size="large"
            type="success"
            :disabled="checkedCartItems.length < 1"
            v-unimplemented="'Оформление заказа'"
        >
            <el-icon :size="18" class="el-icon--left"><shopping-bag/></el-icon>
            Перейти к оформлению
        </el-button>
        <el-text style="display: block; text-align: justify">Доступные способы доставки и другие параметры можно выбрать при оформлении заказа</el-text>
    </aside>
</div>
</template>
<style scoped>
.container {
    display: grid;
    grid-template-columns: 1fr 280px;
    margin-bottom: 1rem;
    margin-inline: 1rem;
    gap: 1rem;
}
aside, main {
    background: var(--el-color-primary-light-9);
    border-radius: 1rem;
    padding: 1rem;
    height: min-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
h2, h3 {
    font-size: 1.25rem;
}
.cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .5rem;
}
.el-button + .el-button {
    margin: 0;
}
</style>