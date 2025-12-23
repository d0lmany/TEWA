<script setup lang="ts">
import { computed, inject } from 'vue';
import { User } from '@element-plus/icons-vue';
import type { Review } from '@/ts/entities/Review';
import type { UI } from '@/ts/types/Provides';

const formatter = (inject('ui') as UI).dateFormatter;
const props = defineProps<{
    review: Review
}>();

const rating = computed(() => {
    return parseFloat(props.review.evaluation.toString());
})
const prettyDate = (date: string) => formatter.format(new Date(date));
</script>
<template>
<el-card shadow="never" body-style="padding:1rem" role="article">
    <div class="header">
        <el-avatar size="large" shape="square" :src="props.review.user?.picture">
            <el-icon :size="24"><User /></el-icon>
        </el-avatar>
        <div class="header-content">
            <b>{{props.review.user?.name}}</b>
            <el-rate
                disabled
                :modelValue="rating"
            />
        </div>
        <div class="created">Создан: {{ prettyDate(props.review.created_at) }}</div>
        <div class="updated" v-if="props.review.created_at != props.review.updated_at">Обновлён: {{prettyDate(props.review.updated_at)}}</div>
    </div>
    <template #footer v-if="props.review.text">
        <p>{{ props.review.text }}</p>
    </template>
</el-card>
</template>
<style scoped>
.header {
    display: flex;
    gap: 1rem;
    position: relative;
}
.header-content {
    display: flex;
    flex-direction: column;
}
p {
    margin: 0;
    text-align: justify;
    line-height: 1.4rem;
    white-space: pre-line;
    color: var(--el-text-color-primary);
}
.created {
    top: 0;
}
.updated {
    bottom: 0;
}
.created, .updated {
    color: var(--el-text-color-regular);
    position: absolute;
    font-size: .75rem;
    right: 0;
}
</style>