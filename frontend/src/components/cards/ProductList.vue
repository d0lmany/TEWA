<script setup>
import { ref, inject, onMounted, nextTick, onUnmounted, watch } from 'vue';
import ProductCard from './ProductCard.vue';
import { ElMessage } from 'element-plus';

const props = defineProps({
  params: {
    type: Object,
    default: () => ({})
  },
  firstPage: {
    type: Number,
    default: 1
  },
  lastPage: {
    type: Number,
    default: null
  }
});

const ProductService = inject('services').product;
const products = ref([]);
const paginate = ref({
  observer: null,
  page: props.firstPage,
  loading: false,
  hasMore: true,
});
const sentinel = ref(null);

const fetchProducts = async () => {
  if (paginate.value.loading || !paginate.value.hasMore) return;

  paginate.value.loading = true;
  try {
    const result = await ProductService.index(props.params, paginate.value.page);

    if (!result.success) {
      ElMessage.error(result.message || 'Произошла ошибка при загрузке каталога');
      return;
    }

    const data = result.data;
    const pagination = result.pagination;

    products.value = [...products.value, ...data];

    if (props.lastPage && paginate.value.page >= props.lastPage) {
      paginate.value.hasMore = false;
    } else if (paginate.value.page >= pagination.last_page) {
      paginate.value.hasMore = false;
    } else {
      paginate.value.page++;
    }
  } catch (e) {
    ElMessage.error(`Фатальная ошибка: Нет связи с сервером. ${e}`);
    console.error(e);
  } finally {
    paginate.value.loading = false;
  }
};

watch(() => [props.params, props.firstPage, props.lastPage], () => {
  products.value = [];
  paginate.value.page = props.firstPage;
  paginate.value.hasMore = true;
  fetchProducts();
}, { deep: true });

onMounted(() => {
  fetchProducts();

  paginate.value.observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting && !paginate.value.loading && paginate.value.hasMore) {
      fetchProducts();
    }
  }, { threshold: 0.1 });

  nextTick(() => {
    if (sentinel.value) {
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
  <section>
    <ProductCard
      v-for="(product, i) in products"
      :key="product.id"
      v-model="products[i]"
      v-if="products.length > 0"
    />
    <el-empty v-else description="Каталог пуст" style="grid-column: 1 / -1"/>
    <div ref="sentinel" style="height:1px"></div>
  </section>
</template>
<style scoped>
section {
  gap: 1rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 25%, 250px), 1fr));
}
</style>