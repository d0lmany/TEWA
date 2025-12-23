<script setup lang="ts">
import { computed, inject } from 'vue';
import { useRouter } from 'vue-router';
import { StarFilled } from '@element-plus/icons-vue';
import type { UI } from '@/ts/types/Provides';
import type { Product } from '@/ts/entities/Product';

const router = useRouter();
const formatter = (inject('ui') as UI).currencyFormatter;
const { product } = defineProps<{
    product: Product
}>();
const formatPrice = (price: number) => formatter.format(price);
const showProduct = () => router.push({
    name: 'Product',
    params: {
        id: product.id,
        slug: product.name
    }
});

const rating = computed(() => parseFloat(product.feedbacks.rating.toString()).toFixed(1));
</script>
<template>
<el-card
    shadow="hover"
    class="card"
    @click="showProduct"
    header-class="product-card-header"
    role="article"
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
</el-card>
</template>
<style scoped>
.card {
    border-radius: .5rem;
    cursor: pointer;
    will-change: box-shadow;
}
.card:hover p {
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