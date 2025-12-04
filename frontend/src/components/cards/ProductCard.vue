<script setup lang="ts">
import { computed, inject } from 'vue';
import { useRouter } from 'vue-router';
import { StarFilled } from '@element-plus/icons-vue';
import type { UI } from '@/ts/types/Provides';
import type { Product } from '@/ts/entities/Product';

const router = useRouter();
const currency = (inject('ui') as UI).currency;
const props = defineProps<{
   product: Product
}>()
const isWholeNumber = Math.round(props.product.price.final_price * 100) / 100 % 1 === 0;
const formatter = new Intl.NumberFormat(navigator.language, {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: isWholeNumber ? 0 : 2,
      maximumFractionDigits: isWholeNumber ? 0 : 2
});

const formatPrice = (price: number) => formatter.format(price);
const showProduct = () => router.push({
   name: 'Product',
   params: {
      id: props.product.id,
      slug: props.product.name
   }
});

const rating = computed(() => parseFloat(props.product.feedbacks.rating.toString()).toFixed(1));
</script>
<template>
<el-card
   shadow="hover"
   class="card"
   @click="showProduct"
   header-class="product-card-header"
>
   <template #header>
      <el-image :src="props.product.photo" fit="contain" lazy class="img"/>
   </template>
   <div class="flex gap">
      <b>{{ formatPrice(props.product.price.final_price) }} <s class="soft" v-if="props.product.price.discount > 0">{{ formatPrice(props.product.price.base_price) }}</s></b>
      <div class="rating" v-if="rating">
         <el-icon :size="20" color="gold"><StarFilled/></el-icon>
         <div>{{ rating }}</div>
      </div>
      <div class="soft" v-else>Нет оценок</div>
   </div>
   <p>{{ props.product.name }}</p>
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