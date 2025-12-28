<script setup lang="ts">
import { ref, inject, onMounted } from 'vue';
import { Menu, Search, ShoppingCart, Star, User } from '@element-plus/icons-vue';
import { ElButton, ElMessage } from 'element-plus';
import { useRouter, type Router } from 'vue-router';
import { useUserStore } from '@/stores/userStore';
import type { default as CS } from '@/ts/services/CategoryService';
import type Services from '@/ts/types/Services';
import type { UI } from '@/ts/types/Provides';
import type { GroupedCategories } from '@/ts/entities/Category';

const searchQuery = ref<string>('');
const dialogVisibility = ref<boolean>(false);
const categories = ref<GroupedCategories>({});
const router: Router = useRouter();
const CategoryService: CS = (inject('services') as Services).category;
const userStore = useUserStore();
const ui = inject('ui') as UI;

const search = () => {
    const target = searchQuery.value.trim();
    if (target !== '') {
        const article = parseInt(target);
        if (article && article > 0) {
            router.push({
                name: 'Product',
                params: { id: article }
            });
        } else {
            router.push({
                name: 'Search',
                query: { q: target }
            });
        }
    }
}
const goto = (where: string) => {
    if (userStore.isAuth) {
        router.push({ name: where })
    } else {
        ui.loginVisible = true;
    }
}
const loadCategories = async () => {
    try {
        const result = await CategoryService.preparedIndex();

        if (result.success && result.data) {
            categories.value = result.data;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Произошла ошибка при загрузке категорий');
    }
}

onMounted(() => loadCategories())
</script>
<template>
    <header>
        <router-link class="logo" :to="{ name: 'Home' }">
            TEWA
        </router-link>
        <el-button @click="dialogVisibility = true" class="categoriesButton">
            <el-icon class="el-icon--left" :size="22">
                <Menu />
            </el-icon>
            Категории
        </el-button>
        <el-input class="input" v-model="searchQuery" clearable placeholder="Искать в TEWA" @keyup.enter="search">
            <template #append>
                <el-button @click="search">
                    <el-icon>
                        <Search />
                    </el-icon>
                </el-button>
            </template>
        </el-input>
        <nav>
            <el-badge :value="userStore.getFavoriteTotalLength()" color="#fe4e7f">
                <el-button @click="goto('Favorite')">
                    <el-icon class="el-icon--left" :size="22">
                        <star/>
                    </el-icon>
                    Избранное
                </el-button>
            </el-badge>
            <el-badge type="primary" :value="userStore.cart.length">
                <el-button @click="goto('Cart')">
                    <el-icon class="el-icon--left" :size="22">
                        <shopping-cart/>
                    </el-icon>
                    Корзина
                </el-button>
            </el-badge>
            <el-button @click="goto('Profile')">
                <el-icon class="el-icon--left" :size="22">
                    <User />
                </el-icon>
                Профиль
            </el-button>
        </nav>
    </header>
    <el-dialog title="Категории" v-model="dialogVisibility" width="75%" top="5vh" center>
        <div class="categoriesDialog">
            <ul class="categories" v-for="(subs, name, i) in categories" :key="i">
                <li>{{ name }}</li>
                <li v-for="sub in subs" :key="sub.id">
                    <router-link :to="{
                        name: 'Search',
                        query: {
                            category_id: sub.id,
                            category: sub.name
                        }
                    }" @click="dialogVisibility = false">{{ sub.name }}</router-link>
                </li>
            </ul>
        </div>
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
.categoriesDialog {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
}
.categories {
    margin: 0;
    padding: 0;
    list-style: none;
    font-size: 1rem;
}
.categories li:first-child {
    font-weight: 600;
    font-size: 1.25rem;
    letter-spacing: 5%;
    color: var(--el-text-color-primary);
    border-bottom: 4px solid var(--el-color-primary);
    padding-top: 0 !important;
    min-height: 55px;

    display: flex;
    align-items: center;
}
.categories li {
    padding-top: .25rem;
}
.categories li a {
    color: var(--el-text-color-regular) !important;
    text-decoration: none;
    transition: .2s ease-out;
}
.categories li a:hover {
    color: var(--el-color-primary) !important;
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
    .categoriesDialog {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>