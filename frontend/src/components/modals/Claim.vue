<script setup>
import { ElMessage } from 'element-plus';
import { useUserStore } from '@/stores/userStore';
import { inject, ref } from 'vue';
import { storeToRefs } from 'pinia';

const visible = defineModel({
    type: Boolean,
    required: true,
})
const props = defineProps({
    entity: { type: String, required: true },
    entity_id: { type: Number, required: true },
})
const formData = ref({});
const formRef = ref();
const rules = {
    topic: [
        { required: true, message: '"Тема" - обязательное поле', trigger: 'blur' },
        { min: 5, message: 'Не менее 5 символов', trigger: 'blur' },
        { max: 255, message: 'Не более 255 символов', trigger: 'blur' },
    ],
    text: [
        { required: true, message: '"Текст" - обязательное поле', trigger: 'blur' },
        { min: 15, message: 'Не менее 15 символов', trigger: 'blur' },
    ]
};
const loading = ref(false);
const ClaimService = inject('services').claim;
const { isAuth } = storeToRefs(useUserStore());

const handleSubmit = async () => {
    if (!formRef.value) return;

    if (!isAuth.value) visible.value = false;

    formData.value.topic = formData.value.topic.trim();
    formData.value.text = formData.value.text.trim();
    
    const valid = await formRef.value.validate();

    if (valid) {
        loading.value = true;
        try {
            const response = await ClaimService.store({
                entity: props.entity,
                entity_id: props.entity_id,
                ...formData.value,
            });

            console.log(response)

            if (response.success) {
                ElMessage.success('Жалоба отправлена');
                visible.value = false;
            } else {
                throw response;
            }
        } catch (error) {
            ElMessage.error(`Не удалось отправить жалобу. ${error.message}`);
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
>
    <el-form
        label-position="top"
        :model="formData"
        ref="formRef"
        :rules="rules"
    >
        <el-form-item label="Тема" prop="topic">
            <el-input
                v-model="formData.topic"
                placeholder="Пара слов о вашей проблеме"
            />
        </el-form-item>
        <el-form-item label="Текст" prop="text">
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
                @click="handleSubmit"
            >
                Пожаловаться
            </el-button>
        </div>
    </template>
</el-dialog>
</template>