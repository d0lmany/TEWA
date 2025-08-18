<template>
    <el-space direction="vertical" alignment="flex-start" class="container">
    <el-text size="large">Минимальный рейтинг</el-text>
    <el-rate v-model="filters.rate" clearable />
    <el-text size="large">Цена</el-text>
    <div class="flex gap">
        <el-input 
        placeholder="Минимум" 
        v-model="filters.min" 
        inputmode="numeric" 
        @input="handleNumericInput('min')" 
        clearable
        />
        <el-input 
        placeholder="Максимум" 
        v-model="filters.max" 
        inputmode="numeric" 
        @input="handleNumericInput('max')" 
        clearable
        />
    </div>
    <el-text size="large">Категория</el-text>
    <el-select 
    v-model="filters.category_id"
    style="width:100%" 
    placeholder="Выберите категорию"
    clearable
    >
    <el-option-group
        v-for="group in options"
        :key="group.id"
        :label="group.name"
    >
        <el-option
        v-for="item in group.options"
        :key="item.id"
        :label="item.name"
        :value="item.id"
        />
    </el-option-group>
    </el-select>
    <el-text size="large">Теги</el-text>
    <el-select
    v-model="filters.tags" style="width:100%"
    multiple collapse-tags-tooltip clearable
    collapse-tags placeholder="Выберите теги">
        <el-option v-for="tag in tags"
        :key="tag.title" :value="tag.title">
            <div class="flex">
                <span>{{ tag.title }}</span>
                <el-tooltip :content="tag.about" placement="right">
                    <el-icon>
                        <InfoFilled/>
                    </el-icon>
                </el-tooltip>
            </div>
        </el-option>
    </el-select>
    </el-space>
</template>
<script setup>
import { ref, watch, inject, onMounted } from 'vue';
import { InfoFilled } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';

const props = defineProps({
    modelValue: {
    type: Object,
    default: () => ({
        rate: null,
        min: '',
        max: '',
        tags: [],
        category_id: null,
    })
    }
});

const emit = defineEmits(['update:modelValue']);
const options = ref([]);
const API = inject('API');
const tags = ref([
    {
        title: 'Изготовление на заказ',
        about: 'Товар создаётся под ваш заказ (например, мебель, украшения). Сроки и детали уточняйте у продавца.'  
    },
    {
        title: 'Открыт предзаказ',
        about: 'Товар появится позже, но можно забронировать его заранее (например, новый гаджет или коллекционная вещь).'  
    },
    {
        title: 'Есть возврат',
        about: 'Можно вернуть товар в указанный продавцом срок, если он не подошёл.'  
    },
    {
        title: 'Экологичный товар',
        about: 'Изготовлен из переработанных материалов или с минимизацией вреда для природы (например, биоразлагаемая упаковка).'  
    },
    {
        title: 'Б/У',
        about: 'Товар в использованном состоянии. Состояние уточняйте в описании или фото.'  
    },
    {
        title: 'DIY',
        about: 'Уникальная вещь, созданная продавцом вручную (например, вязаный свитер или керамика).'  
    },
    {
        title: 'Оригинал',
        about: 'Подлинность товара проверена маркетплейсом (например, фирменная электроника или парфюм).'  
    },
    {
        title: 'Уценён',
        about: 'Небольшой дефект или следы использования, не влияющие на функционал (например, царапина на корпусе).'  
    },
    {
        title: 'Без упаковки',
        about: 'Товар продаётся без оригинальной коробки или плёнки (например, телефон только с зарядным устройством).'  
    },
    {
        title: 'Веганский',
        about: 'Продукт не содержит компонентов животного происхождения (например, косметика или одежда).'  
    },
]);
const filters = ref({...props.modelValue});

watch(filters, (newVal) => {
    emit('update:modelValue', {...newVal});
}, { deep: true });

const handleNumericInput = (field) => {
    filters.value[field] = (filters.value[field] || '').replace(/\D/g, '');
};

const setOptions = async () => {
    try {
        const {data} = await API.get('/categories');
        const cluster = data
            .filter(cat => cat.parent_id === null)
            .map(cat => ({
                name: cat.name,
                options: data.filter(subCat => subCat.parent_id === cat.id),
            }))
        options.value = cluster;
    } catch (e) {
        ElMessage({ message: `Произошла ошибка при загрузке категорий: Нет связи с сервером. ${e}`, type: 'error' });
    }
};

onMounted(() => setOptions());
</script>

<style scoped>
.container {
    padding-block: 1rem;
    width: 100%;
}
.container :deep(.el-space__item):is(:last-child, :nth-child(6)) {
    width: 100%;
}
</style>