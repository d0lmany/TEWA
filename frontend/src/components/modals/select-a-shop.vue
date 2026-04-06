<template>
<el-dialog
    title="Выберите магазин"
    v-model="visible"
    center
    align-center
    width="max-content"
    :show-close="false"
>
    <section class="flex low gap">
        <article
            v-for="shop in userStore.user.seller?.shops"
            @click="gotoShop(shop)"
            class="flex col gap"
            :key="shop.id"
        >
            <div class="image-wrap">
                <el-image :src="shop.picture"/>
            </div>
            <el-text size="large">
                {{ `${shop.name} - ${shop.rating}` }}
            </el-text>
        </article>
        <article class="flex col gap" v-unimplemented="'Создание магазина'">
            <el-icon size="200">
                <Plus/>
            </el-icon>
            <el-text size="large">Создать новый?</el-text>
        </article>
    </section>
</el-dialog>
</template>
<script setup lang="ts">
import { useShopStore } from '@/stores/shop';
import { useUserStore } from '@/stores/userStore';
import type { Shop } from '@/ts/entities';
import { Plus } from '@element-plus/icons-vue';
import { useRouter } from 'vue-router';

const [userStore, shopStore] = [useUserStore(), useShopStore()]
const visible = defineModel<boolean>()
const router = useRouter()

const gotoShop = (shop: Shop) => {
    shopStore.shop = shop;
    router.push({ name: 'SellerOffice' });
}
</script>
<style scoped>
section {
    align-items: stretch;
    flex-wrap: wrap;
    max-width: 75vw;
}
article {
    border: solid 1px var(--el-text-color-secondary);
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    transition: .2s;
    width: 200px;
}
article .image-wrap {
    width: 100%;
    aspect-ratio: 1 / 1;
    overflow: hidden;
    object-fit: cover;
    border-radius: 4px;
}
article:hover {
    border-color: var(--el-color-primary);
}
</style>