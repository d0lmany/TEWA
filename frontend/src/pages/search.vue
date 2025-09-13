<script setup>
import { ref, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import Filters from '../components/Filters.vue';
import ProductList from '../components/cards/ProductList.vue';
import { Top } from '@element-plus/icons-vue';

const route = useRoute();

const createInitialFilters = () => ({
  rate: 0,
  min: '',
  max: '',
  tags: [],
  category_id: route.query.category_id ?? null,
});

const activeFilters = ref(createInitialFilters());
const appliedFilters = ref(createInitialFilters());

const sortOptions = [
  { value: 'feedback_count', label: 'Количеству отзывов' },
  { value: 'rating', label: 'Рейтингу' },
  { value: 'base_price', label: 'Цене' },
  { value: 'created_at', label: 'Новизне' }
];

const sortSettings = ref({
  current: 'feedback_count',
  direction: 'DESC',
  applied: 'feedback_count',
  appliedDirection: 'DESC'
});

const requestParams = computed(() => {
  const params = {
    q: route.query.q ?? null,
    sort: sortSettings.value.applied,
    direction: sortSettings.value.appliedDirection
  };

  if (appliedFilters.value.rate > 0) params.min_rating = appliedFilters.value.rate;
  if (appliedFilters.value.min) params.min_price = appliedFilters.value.min;
  if (appliedFilters.value.max) params.max_price = appliedFilters.value.max;
  if (appliedFilters.value.tags.length) params.tags = appliedFilters.value.tags;
  if (appliedFilters.value.category_id) params.category_id = appliedFilters.value.category_id;

  return params;
});

const resetAllFilters = () => {
  activeFilters.value = createInitialFilters();
  appliedFilters.value = createInitialFilters();
  sortSettings.value = {
    current: 'feedback_count',
    direction: 'DESC',
    applied: 'feedback_count',
    appliedDirection: 'DESC'
  };
};

const applyCurrentFilters = () => {
  appliedFilters.value = { ...activeFilters.value };
  sortSettings.value.applied = sortSettings.value.current;
  sortSettings.value.appliedDirection = sortSettings.value.direction;
};

watch(
  () => route.query.category_id,
  (newCategoryId) => {
    activeFilters.value.category_id = newCategoryId ?? null;
    appliedFilters.value.category_id = newCategoryId ?? null;
  }
);
</script>

<template>
  <el-container class="root">
    <el-aside class="aside">
      <h2>Фильтры</h2>
      <Filters v-model="activeFilters"/>
      <el-button-group class="buttons">
        <el-button @click="resetAllFilters" round>Очистить</el-button>
        <el-button @click="applyCurrentFilters" round type="primary">Применить</el-button>
      </el-button-group>
    </el-aside>

    <el-main>
      <div class="header">
        <h2>{{ route.query.q ?? route.query.category }}</h2>
        <div class="flex">
          <span>Сортировать:</span>
          <el-select
            v-model="sortSettings.current"
            placeholder="Сортировать"
            style="width:50%"
          >
            <el-option
              v-for="opt in sortOptions"
              :key="opt.value"
              :value="opt.value"
              :label="`по ${opt.label}`"
            />
          </el-select>
          <el-radio-group v-model="sortSettings.direction">
            <el-radio-button value="ASC">По возрастанию</el-radio-button>
            <el-radio-button value="DESC">По убыванию</el-radio-button>
          </el-radio-group>
        </div>
      </div>

      <ProductList :params="requestParams"/>
      <el-backtop>
        <el-icon :size="24"><Top/></el-icon>
      </el-backtop>
    </el-main>
  </el-container>
</template>

<style scoped>
.header {
    margin-bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
h2 {
    margin: 0;
}
.aside {
    margin: 20px;
    padding: 1rem;
    margin-right: 0;
    height: min-content;
    border-radius: 1rem;
    background: var(--el-color-primary-light-9);
}
.buttons > * {
    width: 50%;
}
.buttons {
    width: 100%;
}
span {
    border: var(--el-border);
    border-radius: var(--el-border-radius-base);
    padding: 4px 12px;
}
.root {
  padding-top: 3rem !important;
}
</style>