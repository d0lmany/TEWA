<script setup lang="ts">
import homeSeller from '@/components/sections/home-seller.vue';
import financesSeller from '@/components/sections/finances-seller.vue';
import productsSeller from '@/components/sections/products-seller.vue';
import { SellerView } from '@/ts/types';
import { ref, type Component } from 'vue';
import { House, Back, Money, ShoppingCartFull } from '@element-plus/icons-vue';

const currentView = ref<SellerView>(SellerView.Home);
const views = [
    { type: SellerView.Home, name: 'Главная', icon: House },
    { type: SellerView.Finances, name: 'Финансы', icon: Money },
    { type: SellerView.Products, name: 'Товары', icon: ShoppingCartFull },
]
const sections: Record<SellerView, Component> = {
    [SellerView.Home]: homeSeller,
    [SellerView.Finances]: financesSeller,
    [SellerView.Products]: productsSeller,
}
</script>
<template>
<div class="container">
    <aside>
        <h1 class="section-header" style="padding-bottom: 1rem">Кабинет продавца</h1>
        <el-button-group class="buttons">
            <el-button text
                v-for="view in views"
                :key="view.type"
                :type="currentView === view.type ? 'primary' : ''"
                @click="currentView = view.type"
            >
                <el-icon class="el-icon--left" :size="20">
                    <component :is="view.icon"/>
                </el-icon>
                {{ view.name }}
            </el-button>
            <el-button
                text
                @click="$router.push({name: 'Profile'})"
            >
                <el-icon class="el-icon--left" :size="20">
                    <back/>
                </el-icon>
                Вернуться в профиль
            </el-button>
        </el-button-group>
    </aside>
    <main>
        <article>
            <component :is="sections[currentView]"/>
        </article>
    </main>
</div>
</template>
<style scoped>
.container {
    display: grid;
    grid-template-columns: 280px 1fr;
    margin-bottom: 1rem;
    margin-inline: 1rem;
    gap: 1rem;
}
aside, article {
    background: var(--el-color-primary-light-9);
    border-radius: 1rem;
    padding: 1rem;
    height: min-content;
}
.buttons {
    display: flex;
    flex-direction: column;
    border-radius: .5rem;
}
.buttons .el-button {
    font-size: 1rem;
    padding-block: 1.1rem;
    width: 100%;
    justify-content: start;
}
.buttons .el-button:not(:first-child) {
    border-top: solid 2px var(--el-border-color);
}
</style>