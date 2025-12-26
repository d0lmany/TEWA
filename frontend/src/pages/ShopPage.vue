<script setup lang="ts">
import { Shop as ShopIcon, InfoFilled, Service, Top, Share, Refresh } from '@element-plus/icons-vue';
import type { Shop } from '@/ts/entities/Shop';
import type Services from '@/ts/types/Services';
import { useRoute, useRouter } from 'vue-router';
import { inject, onMounted, reactive, ref, computed } from 'vue';
import { ElMessage } from 'element-plus';
import type Filters from '@/ts/types/Filters';
import ClaimModal from '@/components/modals/ClaimModal.vue';
import SearchFilters from '@/components/SearchFilters.vue';
import ProductCard from '@/components/cards/ProductCard.vue';
import { useUserStore } from '@/stores/userStore';

const {
    shop: ShopService,
    i18n,
} = (inject('services') as Services);
const shop = reactive<Partial<Shop>>({});
const loading = ref(true);
const route = useRoute();
const router = useRouter();
const claimFormVisible = ref(false);
const filters = reactive<Filters>({});
const userStore = useUserStore();

const getShop = async () => {
    try {
        const response = await ShopService.show(route.params.id as unknown as number);

        if (response.success && response.data) {
            Object.assign(shop, response.data);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        if (error instanceof Error) {
            if (error.message.includes('404')) router.push({name: 'NotFound'});
            ElMessage.error(`Ошибка: ${error}`);
        } else {
            router.push({name: 'Home'});
            ElMessage.error(`Ошибка: ${error}`);
        }
    } finally {
        loading.value = false;
    }
}
const translate = (phrase: string): string => {
    const word = i18n.translate(phrase);
    return word[0]?.toUpperCase() + word.slice(1);
}

const copyURL = async () => await copy(window.location.href);
const copy = async (target: string) => {
    try {
        await navigator.clipboard.writeText(target);
        ElMessage.success(`Успешно скопировано!`);
    } catch (e) {
        ElMessage.error(`Не удалось скопировать "${target}": ${e}`);
    }
}
const filteredItems = computed(() => {
    if (!shop?.products) return [];
    return shop.products.filter(product => {
        const rating = product.feedbacks?.rating || 0;
        const price = product.price?.final_price || 0;
        const tags = JSON.parse(product.tags || '[]');
        
        if (rating <= (filters.min_rating || 0)) return false;
        
        if (price <= (filters.min_price || 0)) return false;
        if (price >= (filters.max_price || Infinity)) return false;
        
        if (filters.category_id && product.category?.id !== filters.category_id) return false;
        
        if (filters.tags?.length) {
            const hasTag = filters.tags.some(tag => tags.includes(tag));
            if (!hasTag) return false;
        }
        
        return true;
    });
});

onMounted(() => {
    getShop()
})
</script>
<template>
<main>
    <el-card shadow="hover" body-style="padding: .75rem" body-class="flex low gap" style="border: none; background: var(--el-color-primary-light-9)">
        <el-skeleton
            :loading="loading"
            animated
        >
            <template #template>
                <div class="flex gap">
                    <el-skeleton-item
                        variant="rect"
                        style="width: 75px; height: 75px;"
                    />
                    <div class="flex col gap" style="flex: 1; align-self: flex-start;align-items: flex-start;">
                        <div class="flex gap">
                            <el-skeleton-item variant="button"/>
                        </div>
                        <el-skeleton-item variant="h1" style="width: 50%"/>
                    </div>
                </div>
            </template>
            <el-avatar
                size="large"
                shape="square"
                style="width: 75px; height: 75px;"
                :src="shop.picture"
                lazy
            >
                <el-icon :size="24"><shop-icon/></el-icon>
            </el-avatar>
            <div class="flex col gap" style="align-items: start">
                <h1 class="section-header">
                    {{ shop.name }}
                    <el-popover
                        placement="top"
                        :width="175"
                    >
                        <template #reference>
                            <el-icon :size="20" style="margin-left: .5rem"><info-filled/></el-icon>
                        </template>
                        <div style="text-align: center">
                            <b>{{ `${translate(shop?.seller?.type || '')}: ${shop?.seller?.full_name}` }}</b><br>
                            {{ `ИНН/ОГРН: ${shop?.seller?.code}` }}
                        </div>
                    </el-popover>
                </h1>
                <div class="flex gap">
                    <el-tag v-if="shop.rating">Рейтинг: {{ shop.rating.toFixed(1) }}</el-tag>
                    <el-tag v-if="shop.products">Товары: {{ shop.products.length }}</el-tag>
                    <el-tag v-if="shop.reviewsCount">Отзывы: {{ shop.reviewsCount }}</el-tag>
                </div>
            </div>
            <div class="flex col gap" style="margin-left: auto; align-items: end">
                <el-button
                    type="primary" plain
                    @click="copyURL"
                >
                    <el-icon class="el-icon--left"><share/></el-icon>
                    Поделиться
                </el-button>
                <el-button
                    style="margin: 0"
                    type="danger" plain
                    @click="claimFormVisible = true"
                    v-if="userStore.isAuth"
                >
                    <el-icon class="el-icon--left"><service/></el-icon>
                    Пожаловаться
                </el-button>
            </div>
        </el-skeleton>
    </el-card>
    <div class="container">
        <aside>
            <h2 class="section-header">Фильтры</h2>
            <search-filters
                v-model="filters"
            />
            <el-button @click="(Object.keys(filters) as Array<keyof typeof filters>).forEach(filter => delete filters[filter])">
                <el-icon class="el-icon--left"><refresh/></el-icon>
                Сбросить фильтры
            </el-button>
        </aside>
        <div class="main">
            <section v-if="filteredItems.length">
                <product-card
                    v-for="product in filteredItems"
                    :product="product"
                    :key="product?.id"
                />
            </section>
            <el-empty v-else description="У этого магазина нет товаров"/>
        </div>
        <el-backtop>
            <el-icon :size="24"><top/></el-icon>
        </el-backtop>
    </div>
    <claim-modal
        v-model="claimFormVisible"
        entity="shop"
        :entity_id="shop.id"
    />
</main>
</template>
<style scoped>
main {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding-inline: 1rem;
}
.el-card {
    border-radius: .75rem;
}
.el-tag {
    border-radius: 1rem;
}
.container {
    display: grid;
    grid-template-columns: 280px 1fr;
    margin-bottom: 1rem;
    gap: 1rem;
}
aside, .main {
    background: var(--el-color-primary-light-9);
    border-radius: 1rem;
    padding: 1rem;
    height: min-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.main section {
    gap: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 25%, 250px), 1fr));
    content-visibility: auto;
    contain-intrinsic-size: auto 750px;
}
</style>