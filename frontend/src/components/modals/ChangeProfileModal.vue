<script setup lang="ts">
import { computed, inject, reactive, ref } from 'vue';
import { useUserStore } from '@/stores/userStore';
import type { User } from '@/ts/entities/User';
import { Plus } from '@element-plus/icons-vue';
import { ElMessage, type UploadFile, type UploadRawFile } from 'element-plus';
import type Services from '@/ts/types/Services';
import { createMaxRule, createMinRule, type Rules } from '@/ts/utils/FormRules';

const types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
const selectedFile = ref<UploadRawFile>();
const loading = ref<boolean>(false);
const userStore = useUserStore();
const visible = defineModel({
    type: Boolean,
    required: true,
});
const formRef = ref();
const form = reactive<User>({
    name: userStore.user.name,
    birthday: userStore.user.birthday,
    picture: userStore.user.picture,
});
const rules: Rules = {
    name: [
        createMinRule(2),
        createMaxRule(255),
    ],
    birthday: [
        {
            validator: (_: any, value: any, callback: Function) => {
                if (!value) {
                    callback();
                    return;
                }
                
                const today = new Date();
                const birthday = new Date(value);
                let age = today.getFullYear() - birthday.getFullYear();
                const monthDiff = today.getMonth() - birthday.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
                    age--;
                }

                if (age < 13) {
                    callback(new Error('Вам должно быть не менее 13'));
                } else {
                    callback();
                }
            },
            trigger: 'change'
        }
    ],
};
const UserService = (inject('services') as Services).user;

const cancel = () => {
    form.name = userStore.user.name;
    form.birthday = userStore.user.birthday;
    form.picture = userStore.user.picture;
    selectedFile.value = undefined;

    visible.value = false;
}
const handleFileChange = (file: UploadFile) => {
    selectedFile.value = file.raw;
}
const handleSubmit = async () => {
    if (!formRef.value || !await formRef.value.validate()) return;

    try {
        loading.value = true;
        const formData = new FormData();

        if (selectedFile.value) {
            if (!types.includes(selectedFile.value.type)) {
                ElMessage.warning('Разрешены только фото формата JPEG, JPG, PNG и WEBP');
                return;
            }
            if (selectedFile.value.size / 1024 / 1024 > 2) {
                ElMessage.warning('Размер фото - не более 2Мб')
                return;
            }
            formData.append('picture', selectedFile.value);
        }

        if (form.name && form.name !== userStore.user.name) {
            formData.append('name', form.name);
        }

        if (form.birthday && form.birthday !== userStore.user.birthday) {
            formData.append('birthday', form.birthday);
        }

        const response = await UserService.updatePersonalData(formData);

        if (response.success) {
            ElMessage.success('Успешно обновлено!');
            userStore.login(response.data.data);
            visible.value = false;
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось обновить профиль');
    } finally {
        loading.value = false;
    }
}

const imageUrl = computed<string>(() => (selectedFile.value ? URL.createObjectURL(selectedFile.value) : form.picture) ?? '')

const emit = defineEmits(['openChangePassword', 'deleteAccount']);
</script>
<template>
<el-dialog
    title="Изменить профиль"
    v-model="visible"
    center
    width="30%"
    :show-close="false"
>
    <el-form
        label-position="top"
        :model="form"
        :rules="rules"
        ref="formRef"
        @submit.prevent="handleSubmit"
        id="changeProfileForm"
    >
        <el-form-item label="Аватар" prop="picture">
            <div class="flex gap">
                <el-upload
                    :show-file-list="false"
                    :auto-upload="false"
                    :drag="true"
                    :accept="types.join(', ')"
                    class="uploader"
                    :limit="1"
                    :on-change="handleFileChange"
                >
                    <el-avatar
                        size="large"
                        shape="square"
                        :src="imageUrl"
                        style="width: 150px; height: 150px;"
                    ><el-icon :size="48"><Plus/></el-icon></el-avatar>
                </el-upload>
                <div>
                    <el-text size="large">Разрешены только фото формата JPEG, JPG, PNG и WEBP.</el-text><br>
                    <el-text size="large">Размер фото - не более 2Мб.</el-text>
                </div>
            </div>
        </el-form-item>
        <el-form-item label="Имя" prop="name">
            <el-input
                v-model="form.name"
                placeholder="Как вас называть?"
            />
        </el-form-item>
        <el-form-item label="Дата рождения" prop="birthday">
            <el-input
                type="date"
                format="DD.MM.YYYY"
                v-model="form.birthday"
                value-format="YYYY-MM-DD"
                placeholder="Когда вы родились?"
            />
        </el-form-item>
        <el-form-item label="Действия" style="margin-bottom: 0">
            <el-button-group>
                <el-button @click="emit('openChangePassword')">Изменить пароль</el-button>
                <el-button
                    @click="emit('deleteAccount')"
                    type="danger"
                    plain
                >Удалить аккаунт</el-button>
            </el-button-group>
        </el-form-item>
    </el-form>
    <template #footer>
        <div class="flex gap">
            <el-button @click="cancel">Отмена</el-button>
            <el-button
                type="primary"
                :loading="loading"
                native-type="submit"
                form="changeProfileForm"
            >
                Изменить
            </el-button>
        </div>
    </template>
</el-dialog>
</template>
<style scoped>
.uploader:deep(.el-upload-dragger) {
    padding: 0;
    height: 150px;
}
</style>