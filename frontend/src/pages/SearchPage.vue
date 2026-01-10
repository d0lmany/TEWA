<script setup lang="ts">
import SearchFilters from '@/components/SearchFilters.vue';
import { computed, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import ProductList from '@/components/cards/ProductList.vue';
import { Top } from '@element-plus/icons-vue';
import type Filters from '@/ts/types/Filters';

const route = useRoute();
const routeQueries = reactive({
    category_id: {
        value: route.query.category_id as unknown as number,
        key: route.query.category_id as unknown as number,
        label: route.query.category as unknown as string,
    },
    query: route.query.q as unknown as string,
});
const filters = ref<Filters>({
    category_id: routeQueries.category_id
});
const appliedFilters = ref<Filters>({ ...filters.value });
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
    { value: 'final_price', label: 'Цене' },
    { value: 'created_at', label: 'Новизне' }
];

const request = computed(() => ({
    q: routeQueries.query,
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
    const cleanFilters: Partial<Filters> = {};
    
    (Object.keys(filters.value) as Array<keyof Filters>).forEach(key => {
        if (filters.value[key]) {
            // @ts-ignore-next-line
            cleanFilters[key] = filters.value[key];
        }
    });

    appliedFilters.value = cleanFilters;
    appliedSorts.value = { ...sorts.value };
}
// TODO: если поиск на той же странице - перенаправления не происходит!
</script>
<template>
<el-container>
    <el-main class="main">
        <div class="main-header">
            <h1 class="section-header">{{ routeQueries.query ?? 'Поиск' }}</h1>
            <div class="sorts">
                <el-select v-model="sorts.field">
                    <el-option
                        v-for="(option, i) in options"
                        :value="option.value"
                        :label="`по ${option.label}`"
                        :key="i"
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
        <h2 class="section-header">Фильтры</h2>
        <search-filters v-model="filters"/>
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