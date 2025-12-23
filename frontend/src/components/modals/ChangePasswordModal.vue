<script setup lang="ts">
import { ElMessage, ElNotification } from 'element-plus';
import { inject, reactive, ref, watch } from 'vue';
import type PasswordChange from "@/ts/types/PasswordChange";
import type Services from '@/ts/types/Services';
import { type Rules, createMaxRule, createMinRule, createRequiredRule } from '@/ts/utils/FormRules';

const visible = defineModel({
    type: Boolean,
    required: true,
});
const {
    user: UserService,
    api: ApiService,
} = (inject('services') as Services);
const loading = ref<boolean>(false);
const formRef = ref();
const rules: Rules = {
    old_password: [
        createRequiredRule('Текущий пароль'),
        createMinRule(8),
        createMaxRule(100),
        {
            validator: (_: any, value: any, callback: Function) => {
                if (!value) {
                    callback();
                    return;
                }
                
                const hasUpperCase = /[A-Z]/.test(value);
                const hasLowerCase = /[a-z]/.test(value);
                const hasNumbers = /\d/.test(value);
                const hasSpecialChar = /[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]/.test(value);
                
                if (!hasUpperCase || !hasLowerCase || !hasNumbers || !hasSpecialChar) {
                    callback(new Error('Пароль должен содержать латинские буквы в верхнем и нижнем регистре, цифры и специальные символы'));
                } else {
                    callback();
                }
            },
            trigger: 'blur'
        }
    ],
    password: [
        createRequiredRule('Новый пароль'),
        createMinRule(8),
        createMaxRule(100),
        {
            validator: (_: any, value: any, callback: Function) => {
                if (!value) {
                    callback();
                    return;
                }
                
                const hasUpperCase = /[A-Z]/.test(value);
                const hasLowerCase = /[a-z]/.test(value);
                const hasNumbers = /\d/.test(value);
                const hasSpecialChar = /[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]/.test(value);
                
                if (!hasUpperCase || !hasLowerCase || !hasNumbers || !hasSpecialChar) {
                    callback(new Error('Пароль должен содержать латинские буквы в верхнем и нижнем регистре, цифры и специальные символы'));
                } else {
                    callback();
                    return;
                }

                if (form.old_password === form.password) {
                    callback(new Error('Новый пароль не должен совпадать со старым'));
                }
            },
            trigger: 'blur'
        }
    ],
    password_confirmation: [
        { required: true, message: 'Подтверждение пароля обязательно', trigger: 'blur' },
        {
            validator: (_: any, value: any, callback: Function) => {
                if (value !== form.password) {
                    callback(new Error('Пароли не совпадают'));
                } else {
                    callback();
                }
            },
            trigger: 'blur'
        }
    ]
}
const form = reactive<PasswordChange>({
    old_password: '',
    password: '',
    password_confirmation: '',
});

const cancel = () => {
    form.old_password = '';
    form.password = '';
    form.password_confirmation = '';

    visible.value = false;
}
const handleSubmit = async () => {
    if (!formRef.value || !await formRef.value.validate()) return;

    try {
        loading.value = true;
        
        const response = await UserService.updatePassword(form);

        if (response.success && response.data) {
            ElMessage.success('Пароль обновлён!');
            ApiService.authToken = response.data.token;
            visible.value = false;
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось обновить пароль');
    } finally {
        loading.value = false;
    }
}

watch(visible, () => {
    if (visible.value) {
        ElNotification({
            title: 'Каким должен быть пароль?',
            message: 'Пароль должен содержать латинские буквы в верхнем и нижнем регистре, цифры и специальные символы. От 8 до 100 символов.',
            type: 'primary',
        });
    }
})
</script>
<template>
<el-dialog
    title="Изменить пароль"
    v-model="visible"
    center
    width="30%"
    style="border-radius: 1rem"
    :show-close="false"
>
    <el-form
        label-position="top"
        :model="form"
        :rules="rules"
        ref="formRef"
        @submit.prevent="handleSubmit"
        id="changePasswordForm"
    >
        <el-form-item label="Текущий пароль" prop="old_password">
            <el-input
                type="password"
                clearable
                v-model="form.old_password"
                show-password
                placeholder="Введите текущий пароль"
            />
        </el-form-item>
        <el-form-item label="Новый пароль" prop="password">
            <el-input
                type="password"
                clearable
                v-model="form.password"
                show-password
                placeholder="Введите новый пароль"
            />
        </el-form-item>
        <el-form-item style="margin-bottom: 0" label="Новый пароль ещё раз" prop="password_confirmation">
            <el-input
                type="password"
                clearable
                v-model="form.password_confirmation"
                show-password
                placeholder="Подтвердите новый пароль"
            />
        </el-form-item>
    </el-form>
    <template #footer>
        <div class="flex gap">
            <el-button @click="cancel">Отмена</el-button>
            <el-button
                type="primary"
                :loading="loading"
                native-type="submit"
                form="changePasswordForm"
            >Изменить пароль</el-button>
        </div>
    </template>
</el-dialog>
</template>