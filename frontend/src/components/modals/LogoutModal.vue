<script setup lang="ts">
import { useUserStore } from '@/stores/userStore';
import { type Services } from '@/ts/services';
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

const logout = async () => {
    try {
        loading.value = true;
        const response = await UserService.logout();

        if (response.success) {
            ApiService.authToken = null;
            userStore.logout();
            ElMessage.success('Выхожу...');
            router.push({ name: 'Home' });
            visible.value = false;
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось выйти из аккаунта');
    } finally {
        loading.value = false;
    }
}
</script>
<template>
<el-dialog
    title="Выйти из аккаунта?"
    v-model="visible"
    center
    align-center
    width="20%"
    :show-close="false"
>
<el-text size="large" style="display: block; text-align: center">Хотим убедиться, что вы нажали не случайно 😄</el-text>
<div class="flex gap" style="margin-top: 1rem">
    <el-button @click="visible = false">Отмена</el-button>
    <el-button @click="logout" type="danger" :loading="loading">Выйти</el-button>
</div>
</el-dialog>
</template>