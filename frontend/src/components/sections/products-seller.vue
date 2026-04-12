<template>
<div class="section">
    <h2 class="section-header flex">
        <div class="flex low gap">
            <span>Товары</span>
            <el-tag>{{ `Товары: ${ productListRef?.products.length }` }}</el-tag>
        </div>
        <el-button @click="openCreateProduct">Добавить товар</el-button>
    </h2>
    <div class="section-body">
        <product-list
            :params="{ shop_id: shopStore.shop.id }"
            :card="productSellerCard"
            ref="productListRef"
        />
    </div>
    <create-product v-model="shopStore.createFormVisible"/>
</div>
</template>
<script setup lang="ts">
import { useShopStore } from '@/stores/shop'
import ProductList from '../cards/ProductList.vue'
import type { Product } from '@/ts/entities'
import { ref } from 'vue'
import createProduct from '../modals/create-product.vue'
import productSellerCard from '../cards/product-seller-card.vue'

const shopStore = useShopStore()
const productListRef = ref<{ products: Product[] }>()

const openCreateProduct = () => {
    shopStore.currentMode = 'create'
    shopStore.currentProduct = shopStore.emptyProduct
    shopStore.createFormVisible = true
}
</script>