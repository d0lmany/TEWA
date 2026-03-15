<script setup lang="ts">
import { inject, onMounted, ref } from 'vue';
import { ElMessage } from 'element-plus';
import { InfoFilled, Refresh } from '@element-plus/icons-vue';
import { type Services } from '@/ts/services';
import type { GroupedCategories, Tag } from '@/ts/entities';
import type { Filters } from '@/ts/types';

defineProps<{ showReset?: boolean }>()

const {
    category: CategoryService,
    tag: TagService,
} = inject('services') as Services;
const categories = ref<GroupedCategories>({});
const tags = ref<Tag[]>([]);
const filters = defineModel<Filters>({ required: true });

const handleNumericInput = (field: 'min_price' | 'max_price') => {
    if (!filters.value) return;
    filters.value[field] = parseInt((filters.value[field]?.toString() || '').replace(/\D/g, ''));
};
const setCategories = async () => {
    try {
        const data = await CategoryService.preparedIndex();

        if (data.success && data.data) {
            categories.value = data.data;
        } else {
            throw data.message;
        }
    } catch (e) {
        ElMessage.error(`Произошла ошибка при загрузке категорий: ${e instanceof Error ? e.message : e}`);
    }
};
const setTags = async () => {
    try {
        const response = await TagService.index();

        if (response.success && response.data) {
            tags.value = response.data;
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (e) {
        ElMessage.error(`Произошла ошибка при загрузке тегов: ${e instanceof Error ? e.message : e}`);
    }
}

onMounted(() => {
    setCategories();
    setTags();
});
</script>
<template>
<el-space direction="vertical" alignment="flex-start" class="container-filters">
    <el-text size="large">Минимальный рейтинг</el-text>
    <el-rate v-model="filters.min_rating" clearable/>
    <el-text size="large">Цена</el-text>
    <div class="flex gap">
        <el-input
            placeholder="Минимум"
            v-model="filters.min_price"
            inputmode="numeric"
            @input="handleNumericInput('min_price')"
            clearable
        />
        <el-input
            placeholder="Максимум"
            v-model="filters.max_price"
            inputmode="numeric"
            @input="handleNumericInput('max_price')"
            clearable
        />
    </div>
    <el-text size="large">Категория</el-text>
    <el-select
        v-model="filters.category_id"
        placeholder="Выберите категорию"
        clearable
    >
        <el-option-group
            v-for="(value, key, i) in categories"
            :key="i"
            :label="key"
        >
            <el-option
                v-for="category in value"
                :key="category.id"
                :label="category.name"
                :value="category.id"
            />
        </el-option-group>
    </el-select>
    <el-text size="large">Теги</el-text>
    <el-select
        v-model="filters.tags"
        multiple
        collapse-tags
        collapse-tags-tooltip
        clearable
        placeholder="Выберите теги"
    >
        <el-option
            v-for="tag in tags"
            :key="tag.id"
            :value="tag.name"
        >
        <div class="flex">
            <span>{{ tag.name }}</span>
            <el-popover width="200">
                <div style="text-align: justify">{{ tag.description }}</div>
                <template #reference>
                    <el-icon><info-filled/></el-icon>
                </template>
            </el-popover>
        </div>
        </el-option>
    </el-select>
    <el-button
        v-if="showReset"
        style="margin-top: .75rem"
        @click="(Object.keys(filters) as Array<keyof typeof filters>).forEach(filter => delete filters[filter])"
    >
        <el-icon class="el-icon--left"><refresh/></el-icon>
        Сбросить фильтры
    </el-button>
</el-space>
</template>
<style scoped>
.container-filters {
    width: 100%;
}
.container-filters :deep(.el-space__item):is(:last-child, :nth-child(6), :nth-child(8)) {
    width: 100%;
}
</style>