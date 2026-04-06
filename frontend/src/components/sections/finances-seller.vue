<template>
<div class="section">
    <h2 class="section-header">Финансы</h2>
    <div class="section-body">
        <div class="w-full">
            <el-empty description="У вас нет доходов"/>
        </div>
        <div class="w-full">
            <el-empty description="У вас нет расходов"/>
        </div>
    </div>
</div>
</template>
<script setup lang="ts">
import { ElMessage } from 'element-plus';
import { onMounted } from 'vue';

onMounted(() => {
    ElMessage.warning('Не реализовано')
})
</script>