<script setup lang="ts">
import { ref, inject, onMounted, nextTick, onUnmounted, watch, reactive, type Component } from 'vue';
import ProductCard from '@/components/cards/ProductCard.vue';
import { ElMessage } from 'element-plus';
import type { Services } from '@/ts/services';
import type { Product } from '@/ts/entities';
import type { Filters } from '@/ts/types';

const props = withDefaults(defineProps<{
    params?: {
        q?: string
        sort?: string
        direction?: string
        shop_id?: number
    } & Filters
    firstPage?: number
    lastPage?: number
    bigPage?: boolean
    card?: Component
}>(), {
    card: ProductCard,
    firstPage: 1,
})
const ProductService = (inject('services') as Services).product;
const products = reactive<Product[]>([]);
const paginate = reactive<{
    observer: IntersectionObserver | null
    page: number
    loading: boolean
    hasMore: boolean
}>({
    observer: null,
    page: props.firstPage || 0,
    loading: false,
    hasMore: true,
});
const sentinel = ref(null);

defineExpose({
    products,
})

const fetchProducts = async () => {
    if (paginate.loading || !paginate.hasMore) return;
    
    paginate.loading = true;
    
    try {
        const params = { ...props.params };

        if (!params.q) delete params.q;
        if (!params.min_rating) delete params.min_rating;

        const result = await ProductService.index({
            params: {
                ...params,
                page: paginate.page,
            }
        });

        if (result.success && result.data) {
            const { data, last_page } = result;

            products.push(...data);

            if (props.lastPage && paginate.page >= props.lastPage) {
                paginate.hasMore = false;
            } else if (paginate.page >= last_page) {
                paginate.hasMore = false;
            } else {
                paginate.page++;
            }
        } else {
            console.error(result);
            throw new Error(result.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Произошла ошибка при загрузке каталога');
    } finally {
        paginate.loading = false;
    }
}

watch(() => [props.params, props.firstPage, props.lastPage], () => {
    products.splice(0);
    paginate.page = props.firstPage || 0;
    paginate.hasMore = true;
    paginate.loading = false;
    if (paginate.observer) {
        paginate.observer.disconnect();
        paginate.observer = null;
    }
    fetchProducts();
}, { deep: true });

onMounted(() => {
    fetchProducts();

    paginate.observer = new IntersectionObserver((entries: IntersectionObserverEntry[]) => {
        if (!paginate.observer && !entries.length) return;
        
        if (paginate.loading || !paginate.hasMore) return;
        
        fetchProducts();
    }, { threshold: 0.1 });

    nextTick(() => {
        if (sentinel.value && paginate.observer) {
            paginate.observer.observe(sentinel.value);
        }
    });
});

onUnmounted(() => {
    if (paginate.observer) {
        paginate.observer.disconnect();
    }
});
</script>
<template>
    <section
        :class="{
            'empty': !products.length,
            'loading-initial': paginate.loading
        }"
        :style="{ 'min-height': bigPage ? '75vh': 'max-content' }"
    >
        <el-empty v-if="!products.length" :description="paginate.loading ? 'Загружаю...' : 'Каталог пуст'"/>
        <div class="contents">
            <component
                v-for="product in products"
                :product="product"
                :key="product.id"
                :is="card"
            />
            <div ref="sentinel" style="height:1px"></div>
        </div>
    </section>
</template>
<style scoped>
section {
    gap: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(clamp(250px, 25%, 300px), 1fr));
    content-visibility: auto;
    contain-intrinsic-size: auto 750px;
    position: relative;
}

section.empty {
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>