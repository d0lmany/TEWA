<script setup>
import { ref, inject, onMounted } from 'vue';
import { Menu, Search, ShoppingCart, Star, MessageBox, User, StarFilled } from '@element-plus/icons-vue';
import { ElMessage } from 'element-plus';
import { useRouter } from 'vue-router';

const searchQuery = ref('');
const dialogVisibility = ref(false);
const API = inject('API');
const categories = ref({});
const route = useRouter();
const {isAuthenticated, user} = inject('authState');
const authModal = inject('authModal');

const createCategories = async () => {
    try {
        const {data} = await API.get('/categories');
        categories.value.all = data;
        categories.value.parents = data.filter((cat) => cat.parent_id ?? null === null);
    } catch (e) {
        ElMessage({ message: `Произошла ошибка при загрузке категорий: Нет связи с сервером. ${e}`, type: 'error' });
    }
};
const collectChildCategories = (parentId) => {
    categories.value.currentChildren = categories.value.all.filter((cat) => cat.parent_id === parentId);
}
const search = () => {
    const target = searchQuery.value.trim();
    if (target !== '') {
        route.push({
            path: '/search',
            query: { q: target }
        });
    }
}
const goto = (path) => {
    if (isAuthenticated.value) {
        route.push(path);
    } else {
        authModal.open();
    }
}

onMounted(() => createCategories())
</script>
<template>
    <header>
        <router-link class="logo" to="/">
            <el-icon>
                <StarFilled/>
            </el-icon>
            TEWA
        </router-link>
        <el-button @click="dialogVisibility = true" class="categoriesButton">
            <el-icon class="el-icon--left" :size="22">
                <Menu/>
            </el-icon>
            Категории
        </el-button>
        <el-input
            v-model="searchQuery"
            placeholder="Искать в TEWA"
            clearable @keyup.enter="search"
            class="input">
            <template #append>
                <el-button @click="search">
                    <el-icon><Search/></el-icon>
                </el-button>
            </template>
        </el-input>
        <nav>
            <el-button @click="goto('/my/cart')">
                <el-icon class="el-icon--left" :size="22">
                    <ShoppingCart/>
                </el-icon>
                Корзина
            </el-button>
            <el-button @click="goto('/my/favorite')">
                <el-icon class="el-icon--left" :size="22">
                    <Star/>
                </el-icon>
                Избранное
            </el-button>
            <el-button @click="goto('/my/orders')">
                <el-icon class="el-icon--left" :size="22">
                    <MessageBox/>
                </el-icon>
                Заказы
            </el-button>
            <el-button @click="goto('/my')">
                <el-icon class="el-icon--left" :size="22">
                    <User/>
                </el-icon>
                Профиль
            </el-button>
        </nav>
    </header>
    <el-dialog title="Категории" v-model="dialogVisibility" width="80%">
        <el-row>
            <el-col :span="5" class="flex col">
                <el-button
                v-for="category in categories.parents"
                class="categorySelector"
                @click="collectChildCategories(category.id)"
                >{{ category.name }}</el-button>
            </el-col>
            <el-col :span="19" class="links">
                <router-link class="link"
                v-for="category in categories.currentChildren"
                :to="{
                    path: '/search',
                    query: {
                        category_id: category.id,
                        category: category.name
                    }
                }"
                @click="dialogVisibility = !dialogVisibility"
                >{{ category.name }}</router-link>
            </el-col>
        </el-row>
    </el-dialog>
</template>
<style scoped>
header {
    padding: .5rem 2rem;
    border-bottom: solid 1px var(--el-border-color);
    box-shadow: 0 0 5px 2px #00000020;
    background: var(--el-bg-color-overlay);

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;

    box-sizing: border-box;
    position: fixed;
    z-index: 999;
    width: 100%;
    top: 0;
}
.logo {
    font-weight: bold;
    color: var(--el-color-primary) !important;
    font-size: 2rem;
    text-decoration: none;
    display: flex;
    align-items: center;
}
nav {
    display: flex;
    gap: 1rem;
}
header :deep(.el-link__inner) {
    font-size: 1rem;
    user-select: none;
    padding: .25rem .5rem;
    transition: ease-out .2s;
    border: 1px solid var(--el-border-color);
    border-radius: var(--el-border-radius-base);
}
@media (max-width: 1250px) {
    header {
        flex-wrap: wrap;
        gap: .5rem;
    }
    .logo {
        order: 1;
    }
    nav {
        order: 2;
    }
    .categoriesButton {
        order: 3;
    }
    .input {
        order: 4;
    }
}
    .categorySelector {
        width: 100%;
        min-width: 14rem;
        justify-content: flex-start;
    }
    .categorySelector + .categorySelector {
        margin: 0;
        border-top: none;
    }
    .links {
        padding: 0 1rem;
        display: grid;
        grid-template-columns: repeat(5, 1fr);
    }
    .links .link {
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        color: currentColor;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: ease-out .1s;
        text-align: center;
    }
    .links .link:hover {
        color: var(--el-color-primary);
    }
</style>