<script setup>
import { computed, ref, inject } from 'vue'
import { StarFilled, ShoppingBag, Star } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus';

const isAddedToFav = ref(false);
const isAddedToCart = ref(false);
const count = ref(0);
const preCount = ref(0);
const props = defineProps({
    id: { type: Number, required: true },
    img: { type: String, required: true },
    price: { type: Number, required: true },
    fee: { type: Number, default: null },
    title: { type: String, required: true },
    rating: { },
    quantity: { type: Number, required: true },
});
const API = inject('API');
const {isAuthenticated, user} = inject('authState');
const authModal = inject('authModal');
const regModal = inject('regModal');

const getPrice = computed(() => {
    const price = props.fee === null ? props.price : props.price * (1 - props.fee / 100);
    const isWholeNumber = Math.round(price * 100) / 100 % 1 === 0;
    
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: isWholeNumber ? 0 : 2,
        maximumFractionDigits: isWholeNumber ? 0 : 2
    }).format(price);
});
const createPhotoPath = computed(() => {
    //return `content/products/${props.img}`
    return `../../backend/storage/app/public/products/${props.img}`;
});

const toggleFav = async () => {
    if (isAuthenticated.value) {
        try {
            const response =  await API.post('/favorite', { product_id: props.id });
            let msg = `'${props.title}' `;
            switch (response.status) {
                case 201:
                    msg += 'добавлено в избранное';
                    isAddedToFav.value = true;
                    break;
                case 204:
                    msg += 'удалено из избранного';
                    isAddedToFav.value = false;
                    break;
            }
            ElMessage({message: msg, type: 'success'});
        } catch (e) {
            ElMessage({message: `Ошибка добавления/удаления из избранного: ${e}`, type: 'error'});
        }
    }
}
const addToCart = async (isInc = false) => {
    if (isAuthenticated.value) {
        try {
            const response = await API.post('/cart', { product_id: props.id });
            if (response.status === 201) {
                if (!isInc) {
                    ElMessage({message: `'${props.title}' добавлено в корзину`, type: 'success'});
                    count.value++;
                }
                isAddedToCart.value = true;
                preCount.value++;
            } else {
                throw response;
            }
        } catch (e) {
            ElMessage({message: `Ошибка добавления в корзину: ${e}`, type: 'error'});
        }
    }
}
const handleChange = async () => {
    const delta = count.value - preCount.value;
    
    if (delta === 0) {
        console.warn('Количество не изменилось');
        return;
    }

    try {
        if (delta > 0) {
            await addToCart(true);
        } else {
            const endpoint = count.value > 0 ? '/cart/reduce' : '/cart/remove';
            const response = await API.post(endpoint, { product_id: props.id });
            
            if (response.status === 204) {
                if (count.value === 0) {
                    ElMessage({message: `'${props.title}' удалено из корзины`, type: 'success'});
                    isAddedToCart.value = false;
                }
            }
        }
        preCount.value = count.value;
    } catch (error) {
        const action = delta > 0 ? 'добавления' : 'удаления';
        ElMessage({message: `Ошибка ${action} в корзину: ${error.message || error}`,  type: 'error'});
        console.error(error);
    }
}
</script>
<template>
    <el-card class="card" shadow="hover">
        <template #header>
            <el-image :src="createPhotoPath" fit="contain" lazy class="img"/>
        </template>
        <p>{{ title }}</p>
        <p class="flex">
            <b>{{ getPrice }} <s class="soft" v-if="props.fee > 0">{{ price }}</s></b>
            <div class="flex gap" v-if="rating !== null">
                <el-icon :size="20" color="gold"><StarFilled/></el-icon>
                <div>{{ rating }}</div>
            </div>
            <div class="soft" v-else>Нет оценок</div>
        </p>
        <p class="flex">
            <el-popover :disabled="isAuthenticated" trigger="click" v-if="!isAddedToCart">
                <template #reference>
                    <el-button type="primary" @click="addToCart(false)" round>
                        <el-icon :size="20" class="el-icon--left">
                            <ShoppingBag/>
                        </el-icon>
                        В корзину
                    </el-button>
                </template>
                <div class="flex col">
                    <p>Для этого действия нужен аккаунт</p>
                    <div class="flex col gap">
                        <el-button plain type="primary" @click="authModal.open()">Вход</el-button>
                        <el-button plain @click="regModal.open()">Регистрация</el-button>
                    </div>
                </div>
            </el-popover>
            <el-input-number v-else v-model="count" :min="0" :max="quantity" disabled-scientific @change="handleChange"/>
            <el-button round @click="$router.push(`/product/${id}/${title}`)">Перейти</el-button>
        </p>
        <el-popover :disabled="isAuthenticated" trigger="click">
            <template #reference>
                <el-icon :size="24" class="favorite" @click="toggleFav">
                    <Star v-if="!isAddedToFav"/>
                    <StarFilled v-else color="var(--el-color-primary)"/>
                </el-icon>
            </template>
            <div class="flex col">
                <p>Для этого действия нужен аккаунт</p>
                <div class="flex col gap">
                    <el-button plain type="primary" @click="authModal.open()">Вход</el-button>
                    <el-button plain @click="regModal.open()">Регистрация</el-button>
                </div>
            </div>
        </el-popover>
    </el-card>
</template>
<style scoped>
    .soft {
        color: var(--el-text-color-secondary);
    }
    .favorite {
        cursor: pointer;
        position: absolute;
        top: 1.5rem; right: 1.75rem;
    }
    .img {
        width: 100%;
        height: 20rem;
        border-radius: .5rem;
        background: var(--el-bg-color-page);
    }
    .card {
        border-radius: 1rem;
        position: relative;
    }
    .gap {
        gap: .25rem;
    }
    p {
        margin: 0;
    }
    p:not(p:first-child) {
        margin-top: 10px;
    }
    .flex.col p {
        margin-bottom: 1rem;
        text-align: center;
    }
    s {
        font-size: .75rem;
    }
    .card :deep(.el-card__body) {
        height: 7rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .flex :deep(.el-button + .el-button) {
        margin: 0;
    }
</style>