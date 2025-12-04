<script setup lang="ts">
import { inject, computed } from 'vue';
import { User } from '@element-plus/icons-vue';
import type { Links } from '@/ts/types/Provides';

const props = defineProps({
   review: {
      type: Object,
      required: true,
   }
});
const storageURL = (inject('links') as Links).storageURL;

const getRating = computed(() => {
   return parseFloat(props.review.evaluation);
})
const profilePicture = computed(() => {
   if (props?.review?.user?.picture) return `${storageURL}/${props?.review?.user?.picture}`;
   else return '';
});

const prettyDate = (date: string) => {
   return new Intl.DateTimeFormat(navigator.language, {
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
   }).format(new Date(date));
}
</script>
<template>
<el-card shadow="never" body-style="padding:1rem">
   <template #header>
      <div class="header">
         <el-avatar size="large" shape="square" :src="profilePicture">
            <el-icon :size="24"><User /></el-icon>
         </el-avatar>
         <div class="header-content">
            <b>{{props.review.user.name}}</b>
            <el-rate
               disabled
               :modelValue="getRating"
            />
         </div>
         <div class="created">Создан: {{ prettyDate(props.review.created_at) }}</div>
         <div class="updated" v-if="props.review.created_at != props.review.updated_at">Обновлён: {{prettyDate(props.review.updated_at)}}</div>
      </div>
   </template>
   <p>{{ props.review.text }}</p>
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