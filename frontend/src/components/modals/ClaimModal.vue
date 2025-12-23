<script setup lang="ts">
import { ElMessage } from 'element-plus';
import { useUserStore } from '@/stores/userStore';
import { inject, reactive, ref } from 'vue';
import type Services from '@/ts/types/Services';
import { createMaxRule, createMinRule, createRequiredRule, type Rules } from '@/ts/utils/FormRules';

const visible = defineModel({
    type: Boolean,
    required: true,
})
const props = defineProps<{
    entity: 'product',
    entity_id?: number,
}>();
const formData = reactive({
    topic: '', text: ''
});
const formRef = ref();
const rules: Rules = {
    topic: [
        createRequiredRule('Тема'),
        createMinRule(5),
        createMaxRule(255),
    ],
    text: [
        createRequiredRule('Текст'),
        createMinRule(15),
    ]
};
const loading = ref(false);
const ClaimService = (inject('services') as Services).claim;
const userStore = useUserStore();

const handleSubmit = async () => {
    if (!formRef.value) return;

    if (!userStore.isAuth) visible.value = false;

    formData.topic = formData.topic.trim();
    formData.text = formData.text.trim();
    
    const valid = await formRef.value.validate();

    if (valid) {
        loading.value = true;
        try {
            const response = await ClaimService.store({
                entity: props.entity,
                entity_id: props.entity_id ?? 0,
                ...formData,
            });

            if (response.success) {
                ElMessage.success('Жалоба отправлена');
                visible.value = false;
            } else {
                throw response;
            }
        } catch (error) {
            const msg = error instanceof Error ? error.message : error;
            ElMessage.error(`Не удалось отправить жалобу. ${msg}`);
        }
        loading.value = false;
    } else {
        ElMessage.error('Пожалуйста, исправьте ошибки в форме');
    }
}
</script>
<template>
<el-dialog
    title="Пожаловаться"
    v-model="visible"
    center
    width="30%"
    style="border-radius: 1rem"
    :show-close="false"
>
    <el-form
        label-position="top"
        :model="formData"
        ref="formRef"
        :rules="rules"
        @submit.prevent="handleSubmit"
        id="claimForm"
    >
        <el-form-item label="Тема" prop="topic">
            <el-input
                v-model="formData.topic"
                placeholder="Пара слов о вашей проблеме"
            />
        </el-form-item>
        <el-form-item style="margin-bottom: 0" label="Текст" prop="text">
            <el-input
                v-model="formData.text"
                type="textarea"
                placeholder="Расскажите о том что случилось"
            />
        </el-form-item>
    </el-form>
    <template #footer>
        <div class="flex gap">
            <el-button @click="visible = false">Отмена</el-button>
            <el-button
                type="primary"
                :loading="loading"
                native-type="submit"
                form="claimForm"
            >
                Пожаловаться
            </el-button>
        </div>
    </template>
</el-dialog>
</template>