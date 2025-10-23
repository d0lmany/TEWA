<script setup>
import Filters from '@/components/Filters.vue';
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import ProductList from '@/components/cards/ProductList.vue';
import {Top} from '@element-plus/icons-vue';

const route = useRoute();
const filters = ref({
  category_id: {
    value: route.query.category_id,
    key: route.query.category_id,
    label: route.query.category,
  }
});
const appliedFilters = ref({
  category_id: {
    value: route.query.category_id,
    key: route.query.category_id,
    label: route.query.category,
  }
});
const sorts = ref({
  field: 'reviews_count',
  direction: 'DESC',
});
const appliedSorts = ref({
  field: 'reviews_count',
  direction: 'DESC',
});
const options = [
  { value: 'reviews_count', label: 'Количеству отзывов' },
  { value: 'rating', label: 'Рейтингу' },
  { value: 'base_price', label: 'Цене' },
  { value: 'created_at', label: 'Новизне' }
];
const request = computed(() => ({
  q: route.query.q ?? null,
  sort: appliedSorts.value.field,
  direction: appliedSorts.value.direction,
  ...appliedFilters.value
}));

const reset = () => {
  filters.value = {};
  sorts.value = {
    field: 'reviews_count',
    direction: 'DESC',
  };
  appliedFilters.value = {};
  appliedSorts.value = {
    field: 'reviews_count',
    direction: 'DESC',
  };
}

const set = () => {
  const cleanFilters = {};
  for (const [key, value] of Object.entries(filters.value)) {
    if (value !== null && value !== undefined && value !== '') {
      cleanFilters[key] = value;
    }
  }
  appliedFilters.value = cleanFilters;
  appliedSorts.value = {...sorts.value};
}
</script>
<template>
<el-container>
  <el-main class="main">
    <div class="main-header">
      <h1>{{ route.query.q ?? 'Поиск' }}</h1>
      <div class="sorts">
        <el-select v-model="sorts.field">
          <el-option
            v-for="option in options"
            :value="option.value"
            :label="`по ${option.label}`"
          />
        </el-select>
        <el-radio-group v-model="sorts.direction">
          <el-radio-button value="ASC">По возрастанию</el-radio-button>
          <el-radio-button value="DESC">По убыванию</el-radio-button>
        </el-radio-group>
      </div>
    </div>
    <product-list :params="request"/>
    <el-backtop>
      <el-icon :size="24"><Top/></el-icon>
    </el-backtop>
  </el-main>
  <el-aside class="aside">
    <h2>Фильтры</h2>
    <Filters v-model="filters"/>
    <el-button-group class="buttons">
      <el-button
        @click="reset"
      >Очистить</el-button>
      <el-button
        type="primary"
        @click="set"
      >Применить</el-button>
    </el-button-group>
  </el-aside>
</el-container>
</template>
<style scoped>
.aside, .main {
  background: var(--el-color-primary-light-9);
  margin-inline: 1rem;
  margin-bottom: 1rem;
  padding: 1rem;
  border-radius: 1rem;
  order: 2;
}
.aside {
  height: min-content;
  margin-right: 0;
  order: 1;
}
.main-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 1rem;
}
.sorts {
  display: flex;
  width: 50%;
  gap: 1rem;
}
.sorts :deep(.el-radio-group) {
  flex-wrap: nowrap;
}
h1, h2 {
  font-size: 1.5rem;
  margin: 0;
  color: var(--el-text-color-primary);
}
h2 {
  margin-bottom: 1rem;
}
.buttons {
  margin-top: 1rem;
  width: 100%;
}
.buttons > * {
  width: 50%;
}
</style>