<script setup>
import { computed, inject } from 'vue';
import { useRouter } from 'vue-router';
import { StarFilled } from '@element-plus/icons-vue';

const router = useRouter();
const storageURL = inject('storageURL');
const currency = inject('currency');
const product = defineModel({
    type: Object,
    required: true,
});

const getPrice = computed(() => {
    return product.value.discount === null ? product.value.base_price
        : product.value.base_price * (1 - product.value.discount / 100);
});
const isWholeNumber = Math.round(getPrice.value * 100) / 100 % 1 === 0;
const formatter = new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: isWholeNumber ? 0 : 2,
        maximumFractionDigits: isWholeNumber ? 0 : 2
});

const formatPrice = (price) => formatter.format(price);
const showProduct = () => router.push(`/product/${product.value.id}/${product.value.name}`);
const createPhotoPath = computed(() => `${storageURL}/products/${product.value.photo}`);
</script>
<template>
<el-card
    shadow="hover"
    class="card"
    @click="showProduct"
    header-class="product-card-header"
>
    <template #header>
        <el-image :src="createPhotoPath" fit="contain" lazy class="img"/>
    </template>
    <div class="flex gap">
        <b>{{ formatPrice(getPrice) }} <s class="soft" v-if="product.discount > 0">{{ formatPrice(product.base_price) }}</s></b>
        <div class="rating" v-if="product.rating !== null">
            <el-icon :size="20" color="gold"><StarFilled/></el-icon>
            <div>{{ parseFloat(product.rating) }}</div>
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
    height: 20rem;
    border-radius: .5rem;
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