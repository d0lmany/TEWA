<script setup>
import { inject, ref, computed } from 'vue';
import { User } from '@element-plus/icons-vue';

const props = defineProps({
    review: {
        type: Object,
        required: true,
    }
});
const storageURL = inject('storageURL');
const review = ref({...props.review});

const getRating = computed(() => {
    return parseFloat(review.value.evaluation);
})
const prettyDate = (date) => {
    return new Intl.DateTimeFormat(navigator.language, {
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(date));
}

const getAvatarPath = (filename) => {
    return filename ? `${storageURL}/avatars/${filename}`: '';
}
</script>
<template>
<el-card shadow="never" body-style="padding:1rem">
    <template #header>
        <div class="header">
            <el-avatar size="large" shape="square" :src="getAvatarPath(review.user.avatar)">
                <el-icon :size="24"><User /></el-icon>
            </el-avatar>
            <div class="header-content">
                <b>{{review.user.name}}</b>
                <el-rate disabled v-model="getRating"/>
            </div>
            <div class="created">Создан: {{ prettyDate(review.created_at) }}</div>
            <div class="updated" v-if="review.created_at != review.updated_at">Обновлён: {{prettyDate(review.updated_at)}}</div>
        </div>
    </template>
    <p>{{ review.text }}</p>
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