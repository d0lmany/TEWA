<template>
<div class="section">
    <div class="flex">
        <h2 class="section-header">Категории</h2>
        <el-button @click="ui.categoriesVisible = true">Показать категории</el-button>
    </div>
    <div class="section-body flex gap">
        <el-form
            label-position="top"
            :model="context.createForm"
            :rules="rules.createForm"
            @submit.prevent="handleCreate"
        >
            <el-text size="large">Создать категорию</el-text>
            <el-form-item label="Имя категории" prop="name">
                <el-input
                    @blur="context.createForm.name = context.createForm.name.trim()"
                    v-model="context.createForm.name"
                    placeholder="Например, Электроника"
                    clearable
                />
            </el-form-item>
            <el-form-item label="Родительская категория" prop="parent_id">
                <el-select
                    v-model="context.createForm.parent_id"
                    clearable
                >
                    <el-option
                        v-for="opt in options.filter(cat => !cat.parent)"
                        :key="opt.id"
                        :label="opt.name"
                        :value="opt.id"
                    />
                </el-select>
            </el-form-item>
            <el-form-item style="margin-bottom: 0">
                <el-button
                    type="primary"
                    native-type="submit"
                    :loading="context.loading.createForm"
                    :disabled="!context.createForm.name"
                >Создать категорию</el-button>
            </el-form-item>
        </el-form>
        <el-form
            label-position="top"
            :model="context.updateForm"
            :rules="rules.updateForm"
            @submit.prevent="handleUpdate"
        >
            <el-text size="large">Обновить категорию</el-text>
            <el-form-item label="Выберите категорию" prop="parent_id">
                <el-select
                    v-model="context.updateForm.selectedCategoryId"
                    @change="selectedCategoryChanged"
                    clearable
                >
                    <el-option
                        v-for="opt in options"
                        :key="opt.id"
                        :value="opt.id"
                        :label="opt.name"
                    >
                        <div class="flex">
                            <el-text size="large">{{ opt.name }}</el-text>
                            <el-text v-if="!opt.parent">Родительская</el-text>
                        </div>
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="Имя категории" prop="name">
                <el-input
                    @blur="context.updateForm.name = context.updateForm.name.trim()"
                    v-model="context.updateForm.name"
                    placeholder="Например, Электроника"
                    clearable
                    :disabled="!context.updateForm.selectedCategoryId"
                />
            </el-form-item>
            <el-form-item label="Родительская категория" prop="parent_id">
                <el-select
                    v-model="context.updateForm.parent_id"
                    clearable
                    :disabled="!context.updateForm.selectedCategoryId || !context.updateForm.selectedCategory?.parent"
                >
                    <el-option
                        v-for="opt in options.filter(cat => !cat.parent)"
                        :key="opt.id"
                        :label="opt.name"
                        :value="opt.id"
                    />
                </el-select>
            </el-form-item>
            <el-form-item style="margin-bottom: 0">
                <el-button
                    type="primary"
                    native-type="submit"
                    :loading="context.loading.updateForm"
                    :disabled="isUpdateDisabled"
                >Обновить категорию</el-button>
            </el-form-item>
        </el-form>
        <el-form
            label-position="top"
            :model="context.deleteForm"
            :rules="rules.deleteForm"
            @submit.prevent="handleDelete"
        >
            <el-text size="large">Удалить категорию</el-text>
            <el-form-item label="Выберите категорию" prop="parent_id">
                <el-select
                    v-model="context.deleteForm.id"
                    @change="selectedCategoryChanged"
                    clearable
                >
                    <el-option
                        v-for="opt in options"
                        :key="opt.id"
                        :value="opt.id"
                        :label="opt.name"
                    >
                        <div class="flex">
                            <el-text size="large">{{ opt.name }}</el-text>
                            <el-text v-if="!opt.parent">Родительская</el-text>
                        </div>
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item style="margin-bottom: 0">
                <el-button
                    type="danger"
                    native-type="submit"
                    :loading="context.loading.deleteForm"
                    :disabled="!context.deleteForm.id"
                >Удалить категорию</el-button>
            </el-form-item>
        </el-form>
    </div>
</div>
</template>
<script setup lang="ts">
import type { Category } from '@/ts/entities';
import type { Services } from '@/ts/services';
import type { CategoriesContext, UI } from '@/ts/types';
import { createMaxRule, createRequiredRule, type Rules } from '@/ts/utils';
import { ElMessage } from 'element-plus';
import { computed, inject, onMounted, reactive } from 'vue';

const ui = inject('ui') as UI;
const CategoryService = (inject('services') as Services).category;
const context = reactive<CategoriesContext>({
    createForm: { name: '' },
    updateForm: { name: '' },
    deleteForm: {},
    loading: {
        createForm: false,
        updateForm: false,
        deleteForm: false,
    }
})
const rules: Record<string, Rules> = {
    createForm: {
        name: [ createRequiredRule('Имя категории'), createMaxRule(255) ],
    },
    updateForm: {
        selectedCategoryId: [ createRequiredRule('Выбранная категория') ],
        name: [ createMaxRule(255) ],
    },
    deleteForm: {
        id: [ createRequiredRule('Категория') ]
    }
}
const options = reactive<Omit<Category, 'parent_id'>[]>([]);

const isUpdateDisabled = computed(() => {
    if (!Object.keys(context.updateForm.selectedCategory || {}).length) return true;

    const sameName = context.updateForm?.selectedCategory?.name.trim() === context.updateForm.name.trim();
    const sameParent = context.updateForm?.selectedCategory?.parent?.id === context.updateForm.parent_id;

    return !(sameName === false || sameParent === false);
})

const handleCreate = async () => {
    try {
        context.loading.createForm = true;
        const response = await CategoryService.store(context.createForm);

        // позднее, пусть категория добавится в какой-нибудь местный стор
        if (response.success) {
            ElMessage.success('Категория создана!');
            clearForm('createForm');
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (e) {
        ElMessage.error(`Не удалось создать категорию: ${e instanceof Error ? e.message : 'Неизвестная ошибка'}`)
    } finally {
        context.loading.createForm = false;
    }
}
const selectedCategoryChanged = () => {
    const target = options.find(cat => cat.id === context.updateForm.selectedCategoryId);
    if (target) {
        context.updateForm.selectedCategory = target;
        context.updateForm.name = target.name;
        context.updateForm.parent_id = target.parent?.id;
    }
}
const handleUpdate = async () => {
    if (!context.updateForm.selectedCategoryId) return;

    try {
        context.loading.updateForm = true;
        const response = await CategoryService.update(context.updateForm.selectedCategoryId, {
            name: context.updateForm.name.trim(),
            parent_id: context.updateForm.parent_id
        });

        if (response.success) {
            ElMessage.success('Категория обновлена!');
            clearForm('updateForm');
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (e) {
        ElMessage.error(`Не удалось обновить категорию: ${e instanceof Error ? e.message : 'Неизвестная ошибка'}`)
    } finally {
        context.loading.updateForm = false;
    }
}
const clearForm = (targetForm: 'updateForm' | 'createForm' | 'deleteForm') => {
    const form = context[targetForm];

    (Object.keys(form) as Array<keyof typeof form>).forEach(key => {
        (form[key] as any) = undefined;
    });
}
const handleDelete = async () => {
    if (!context.deleteForm.id) return;

    try {
        context.loading.deleteForm = true;
        const response = await CategoryService.destroy(context.deleteForm.id);

        if (response.success) {
            ElMessage.success('Категория удалена!');
            clearForm('deleteForm');
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (e) {
        ElMessage.error(`Не удалось удалить категорию категорию: ${e instanceof Error ? e.message : 'Неизвестная ошибка'}`)
    } finally {
        context.loading.deleteForm = false;
    }
}

onMounted(async () => {
    const response = await CategoryService.index();

    if (response.success && response.data && !options.length) {
        options.push(...response.data)
    }
})
</script>
<style scoped>
.el-form {
    border: 1px solid var(--el-border-color);
    padding: 1rem;
    border-radius: .5rem;
    flex: 1;
    background: var(--el-bg-color-overlay);
}
.el-form .el-text {
    display: block;
    text-align: center;
    padding-bottom: 1rem;
}
.el-button:only-child {
    width: 100%;
}
.section-body {
    align-items: baseline;
}
</style>