<script setup lang="ts">
import { ref, inject, onMounted, nextTick, onUnmounted, watch, computed } from 'vue';
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
const products = ref<Product[]>([]);
const paginate = ref<{
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
const haveProducts = computed(() => !!products.value.length);

const fetchProducts = async () => {
    if (paginate.value.loading || !paginate.value.hasMore) return;
    
    paginate.value.loading = true;
    try {
        const params = { ...props.params };

        if (!params.q) delete params.q;

        if (!params.min_rating) delete params.min_rating;

        const result = await ProductService.index({
            params: {
                ...params,
                page: paginate.value.page,
            }
        });

        if (result.success && result.data) {
            const { data, meta }: { data: Product[], meta: { last_page: number } } = result.data;

            products.value.push(...data);

            if (props.lastPage && paginate.value.page >= props.lastPage) {
                paginate.value.hasMore = false;
            } else if (paginate.value.page >= meta.last_page) {
                paginate.value.hasMore = false;
            } else {
                paginate.value.page++;
            }
        } else {
            console.error(result);
            throw new Error(result.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Произошла ошибка при загрузке каталога');
    } finally {
        paginate.value.loading = false;
    }
}

watch(() => [props.params, props.firstPage, props.lastPage], () => {
    products.value = [];
    paginate.value.page = props.firstPage;
    paginate.value.hasMore = true;
    fetchProducts();
}, { deep: true });

onMounted(() => {
    fetchProducts();

    paginate.value.observer = new IntersectionObserver((entries: IntersectionObserverEntry[]) => {
        if (!paginate.value.observer && !entries.length) return;

        if (!paginate.value.loading && !paginate.value.hasMore) return;

        fetchProducts();
    }, { threshold: 0.1 });

    nextTick(() => {
        if (sentinel.value && paginate.value.observer) {
            paginate.value.observer.observe(sentinel.value);
        }
    });
});

onUnmounted(() => {
    if (paginate.value.observer) {
        paginate.value.observer.disconnect();
    }
});
</script>
<template>
    <section
        :class="{
            'empty': !haveProducts
        }"
        :style="{
            'min-height': bigPage ? '75vh': 'max-content'
        }"
    >
        <div
            class="contents"
            v-if="haveProducts">
                <product-card
                    v-for="product in products"
                    :product="product"
                    :key="product.id"
                />
            </div>
        <el-empty v-else description="Каталог пуст"/>
        <div ref="sentinel" style="height:1px"></div>
    </section>
</template>
<style scoped>
section {
    gap: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 25%, 250px), 1fr));
    content-visibility: auto;
    contain-intrinsic-size: auto 750px;
}
section.empty {
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>