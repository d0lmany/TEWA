<script setup>
import { ElMessage } from 'element-plus';
import { ArrowRight, Share } from '@element-plus/icons-vue'
import { inject, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const API = inject('API');
const product = ref(null);
const loading = ref(true);

const copyToClipBoard = async (target) => {
    try {
        await navigator.clipboard.writeText(target);
        ElMessage.success(`Успешно скопировано!`);
    } catch (e) {
        ElMessage.error(`Не удалось скопировать "${target}": ${e}`);
    }
}
const copyURL = () => {
    copyToClipBoard(window.location.href);
}

onMounted(async () => {
    try {
        const id = route.params.id;
        
        if (!id) {
            ElMessage.error('ID товара не указан');
            return;
        }

        const response = await API.get(`/products/${id}`);

        if (!response.data) {
            throw new Error('Данные продукта не получены');
        }

        product.value = await response.data.data;
        console.log(product.value)
    } catch (error) {
        console.error('Ошибка загрузки продукта:', error);
        ElMessage.error('Произошла ошибка при загрузке товара');
    } finally {
        loading.value = false;
    }
});
</script>
<template>
<main>
    <div class="content" v-if="product">
        <el-breadcrumb :separator-icon="ArrowRight">
            <el-breadcrumb-item :to="{ path: '/' }">Главная</el-breadcrumb-item>
            <el-breadcrumb-item
            v-if="product.category?.parent"
            :to="{
                path: '/search',
                query: {
                    category_id: product.category.parent.id,
                    category: product.category.parent.name
                }
            }">{{ product.category.parent.name }}</el-breadcrumb-item>
            <el-breadcrumb-item
            :to="{
                path: '/search',
                query: {
                    category_id: product.category.id,
                    category: product.category.name
                }
            }">{{ product.category.name }}</el-breadcrumb-item>
            <el-breadcrumb-item>{{ product.name }}</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <aside v-if="product">
        <div class="flex gap">
            <el-button @click="copyToClipBoard(product.id)">
                Артикул: {{ product.id }}
            </el-button>
            <el-button @click="copyURL()">
                Поделиться
                <el-icon class="el-icon--right">
                    <Share/>
                </el-icon>
            </el-button>
        </div>
    </aside>
    <el-empty v-else description="Загружается..."/>
</main>

</template>
<style scoped>
main {
    padding: 1rem 2rem;
    display: grid;
    grid-template-columns: 75% 25%;
}
</style>