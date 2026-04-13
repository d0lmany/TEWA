<script setup lang="ts">
import { computed, inject } from 'vue'
import { useRouter } from 'vue-router'
import { Message, StarFilled } from '@element-plus/icons-vue'
import type { UI } from '@/ts/types'
import type { FullProduct, Product } from '@/ts/entities'
import { useShopStore } from '@/stores/shop'
import type { Services } from '@/ts/services'
import { ElMessage } from 'element-plus'

const shopStore = useShopStore()
const router = useRouter()
const formatter = (inject('ui') as UI).currencyFormatter
const { product } = defineProps<{
    product: Product
}>()
const ProductService = (inject('services') as Services).product
const formatPrice = (price: number) => formatter.format(price)
const showProduct = () => router.push({
    name: 'Product',
    params: {
        id: product.id,
        slug: product.name
    }
})
const updateProduct = () => {
    shopStore.currentMode = 'update'
    shopStore.currentProduct = product as FullProduct
    shopStore.createFormVisible = true
}
const destroy = async () => {
    try {
        const response = await ProductService.destroy(product.id);
        if (response.success) {
            ElMessage.success('Удалено!');
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (e) {
        ElMessage.error(`Произошла ошибка при удалении товара: ${e instanceof Error ? e.message : 'Неизвестная ошибка'}`);
    }
}

const rating = computed(() => parseFloat(product.feedbacks.rating.toString()).toFixed(1));
</script>
<template>
<el-card
    shadow="hover"
    class="card"
    header-class="product-card-header"
    role="article"
    tabindex="0"
    @keydown.enter="showProduct"
    @click.self="showProduct"
>
    <template #header>
        <el-image :src="product.photo" fit="contain" lazy class="img"/>
    </template>
    <div class="flex gap">
        <b>{{ formatPrice(product.price.final_price) }} <s class="soft" v-if="product.price.discount > 0">{{ formatPrice(product.price.base_price) }}</s></b>
        <div class="rating" v-if="rating">
            <el-icon :size="20" color="gold"><StarFilled/></el-icon>
            <div>{{ rating }}</div>
        </div>
        <div class="soft" v-else>Нет оценок</div>
    </div>
    <p>{{ product.name }}</p>
    <template #footer>
        <div class="flex gap">
            <el-button
                style="flex: 1"
                @click="updateProduct"
            >Управление</el-button>
            <el-button
                plain
                type="danger"
                style="flex: 1"
                @click="destroy"
            >Удалить</el-button>
        </div>
    </template>
</el-card>
</template>
<style scoped>
.card {
    border-radius: .5rem;
    will-change: box-shadow;
}
.card:deep(.el-card__footer) {
    transition: var(--el-transition-duration);
}
.card:hover {
    --el-card-border-color: var(--el-color-primary);
}
.card:hover :is(p, b) {
    color: var(--el-color-primary);
}
.img {
    width: 100%;
    height: 18rem;
    border-radius: .25rem;
    background: var(--el-bg-color-page);
}
p {
    margin-block: .5rem 0;
    will-change: color;
    transition: var(--el-transition-color);
}
.soft {
    font-size: .75rem;
    color: var(--el-text-color-secondary);
}
.rating {
    display: flex;
    gap: .5rem;
}
</style>