<script setup lang="ts">
import { useShopStore } from '@/stores/shop';
import { useUserStore } from '@/stores/userStore';
import { ElMessage } from 'element-plus';
import { onMounted } from 'vue';
import { Shop as ShopIcon } from '@element-plus/icons-vue';

const [userStore, shopStore] = [useUserStore(), useShopStore()];

onMounted(() => {
    if (!userStore.user.seller?.verified_at) {
        ElMessage.warning('Ваш аккаунт не был подтверждён, размещение товаров недоступно');
    }
})
</script>
<template>
<div class="section">
    <h2 class="section-header">Главная</h2>
    <div class="section-body">
        <el-card shadow="hover" body-style="padding: .75rem" body-class="flex low gap">
            <el-avatar
                size="large"
                shape="square"
                style="width: 75px; height: 75px;"
                :src="shopStore.shop.picture"
                lazy
            >
                <el-icon :size="24"><shop-icon/></el-icon>
            </el-avatar>
            <div class="flex col gap" style="align-items: start">
                <h1 class="section-header">
                    {{ shopStore.shop.name }}
                </h1>
                <div class="flex gap">
                    <el-tag v-if="shopStore.shop.rating">Рейтинг: {{ shopStore.shop.rating.toFixed(1) }}</el-tag>
                    <el-tag v-if="shopStore.shop.products">Товары: {{ shopStore.shop.products.length }}</el-tag>
                    <el-tag v-if="shopStore.shop.reviewsCount">Отзывы: {{ shopStore.shop.reviewsCount }}</el-tag>
                </div>
            </div>
            <div class="flex col gap" style="margin-left: auto">
                <el-button
                    round
                    @click="$router.push({ name: 'Shop', params: { id: shopStore.shop.id } })"
                >К публичной странице</el-button>
            </div>
            <template #footer>
                {{ shopStore.shop.description }}
            </template>
        </el-card>
    </div>
</div>
</template>
<style scoped>
.el-card:deep(.el-card__footer) {
    padding: .75rem;
}
</style>