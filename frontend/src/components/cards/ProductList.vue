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

const API = inject('API');
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
    const response = await API.get('products', {
      params: {
        ...props.params,
        page: paginate.value.page,
      }
    });

    if (!response.data) {
      ElMessage({ message: 'Произошла ошибка при загрузке каталога', type: 'error' });
      return;
    }

    const data = response.data;
    products.value = [...products.value, ...data.data];

    if (props.lastPage && paginate.value.page >= props.lastPage) {
      paginate.value.hasMore = false;
    } else if (paginate.value.page >= data.last_page) {
      paginate.value.hasMore = false;
    } else {
      paginate.value.page++;
    }
  } catch (e) {
    ElMessage({ message: `Фатальная ошибка: Нет связи с сервером`, type: 'error' });
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
  <div class="cards">
    <ProductCard
      v-for="product in products"
      :key="product.id"
      :id="product.id"
      :img="product.photo"
      :price="Number(product.base_price)"
      :fee="Number(product.discount)"
      :title="product.name"
      :rating="product.rating"
      :quantity="product.quantity"
      v-if="products.length > 0"
    />
    <el-empty v-else description="Каталог пуст" style="grid-column: 1 / -1"/>
    <div ref="sentinel" style="height:1px"></div>
  </div>
</template>

<style scoped>
.cards {
  gap: 1rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 25%, 250px), 1fr));
}
</style>