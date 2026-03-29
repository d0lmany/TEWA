<script setup lang="ts">
import { useUserStore } from '@/stores/userStore';
import { ElMessage } from 'element-plus';

const userStore = useUserStore();

if (!userStore.user.seller?.verified_at) {
    ElMessage.warning('Ваш аккаунт не был подтверждён, размещение товаров недоступно');
}
</script>
<template>
<div class="section">
    <h2 class="section-header">Главная</h2>
    <div class="section-body">
        welcome!
    </div>
</div>
</template>