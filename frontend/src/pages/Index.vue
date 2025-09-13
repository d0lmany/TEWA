<script setup>
import ProductList from '../components/cards/ProductList.vue';
import { Top } from '@element-plus/icons-vue';
</script>
<template>
<main>
    <ProductList />
    <el-backtop>
        <el-icon :size="24"><Top/></el-icon>
    </el-backtop>
</main>
</template>
<style scoped>
main {
    padding: 1rem 2rem;
}
</style>