<script setup lang="ts">
import { ref, inject, onMounted, nextTick, onUnmounted, watch, reactive } from 'vue';
import ProductCard from '@/components/cards/ProductCard.vue';
import { ElMessage } from 'element-plus';
import type Services from '@/ts/types/Services';
import type { Product } from '@/ts/entities/Product';

const props = defineProps({
    params: {
        type: Object,
        default: () => {}
    },
    firstPage: {
        type: Number,
        default: 1
    },
    lastPage: {
        type: Number,
        default: null
    },
    bigPage: {
        type: Boolean,
        default: false
    }
});

const ProductService = (inject('services') as Services).product;
const products = reactive<Product[]>([]);
const paginate = reactive<{
    observer: IntersectionObserver | null,
    page: number,
    loading: boolean,
    hasMore: boolean,
}>({
    observer: null,
    page: props.firstPage,
    loading: false,
    hasMore: true,
});
const sentinel = ref(null);

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
    paginate.page = props.firstPage;
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
        :style="{
            'min-height': bigPage ? '75vh': 'max-content'
        }"
    >
        <el-empty v-if="!products.length" :description="paginate.loading ? 'Загружаю...' : 'Каталог пуст'"/>
        <div class="contents">
            <product-card
                v-for="product in products"
                :product="product"
                :key="product.id"
            />
            <div ref="sentinel" style="height:1px"></div>
        </div>
    </section>
</template>
<style scoped>
section {
    gap: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 25%, 250px), 1fr));
    content-visibility: auto;
    contain-intrinsic-size: auto 750px;
    position: relative;
    min-height: 300px;
}

section.empty {
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>