<script setup>
import ProductList from '@/components/cards/ProductList.vue';
import { Top } from '@element-plus/icons-vue';
</script>
<template>
<main>
   <product-list :bigPage="true"/>
   <el-backtop>
      <el-icon :size="24"><Top/></el-icon>
   </el-backtop>
</main>
</template>
<style scoped>
main {
   background: var(--el-color-primary-light-9);
   padding: 1rem;
   border-radius: 1rem;
   margin: 1rem;
}
</style>