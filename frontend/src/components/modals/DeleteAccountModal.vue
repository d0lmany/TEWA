<script setup lang="ts">
import { useUserStore } from '@/stores/userStore';
import type Services from '@/ts/types/Services';
import { ElMessage } from 'element-plus';
import { inject, ref } from 'vue';
import { useRouter, type Router } from 'vue-router';

const {
    user: UserService,
    api: ApiService
} = inject('services') as Services
const visible = defineModel({
    type: Boolean,
    required: true,
});
const router: Router = useRouter();
const userStore = useUserStore();
const loading = ref(false);
const checked = ref(false);

const deleteAccount = async () => {
    try {
        loading.value = true;
        const response = await UserService.destroy();

        if (response.success) {
            ApiService.authToken = null;
            ElMessage.success(`Были рады работать с вами, ${userStore.user.name}. До новых встреч!`);
            userStore.logout();
            router.push({ name: 'Home' });
            visible.value = false;
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось удалить аккаунт');
    } finally {
        loading.value = false;
    }
}
</script>
<template>
<el-dialog
    title="Удалить аккаунта"
    v-model="visible"
    center
    align-center
    width="20%"
    :show-close="false"
>
    <el-text size="large" class="text">
        <!-- где-то прописать какая информация хранится в системе -->
        Это действие необратимо. Сохранённая информация о вас, вашей корзине, списков избранного, адресов и ПВЗ будет безвозвратно удалена.
        <br>
        <el-checkbox v-model="checked" size="large">Продолжить?</el-checkbox>
    </el-text>
<div class="flex gap" style="margin-top: 1rem">
    <el-button @click="visible = false">Отмена</el-button>
    <el-button @click="deleteAccount" type="danger" :loading="loading" :disabled="!checked">Удалить аккаунт</el-button>
</div>
</el-dialog>
</template>
<style scoped>
.text {
    display: block;
    line-height: 1.5rem;
    text-align: center;
}
</style>