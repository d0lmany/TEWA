<script setup>
import { ElMessage } from 'element-plus';
import { useUserStore } from '@/stores/userStore';
import { ref, inject } from 'vue';
import { InfoFilled } from '@element-plus/icons-vue';

const AuthService = inject('services').auth;
const userStore = useUserStore();
const formRef = ref();
const form = ref({
    name: '',
    email: '',
    birthday: '',
    password: '',
    passwordConfirmation: '',
    agree: false
});
const rules = {
    name: [
        { required: true, message: '"Имя" - обязательное поле', trigger: 'blur' },
        { min: 2, message: 'Не менее двух символов', trigger: 'blur' },
        { max: 255, message: 'Не более 255 символов', trigger: 'blur' },
    ],
    email: [
        { required: true, message: '"Почта" - обязательное поле', trigger: 'blur' },
        { type: 'email', message: 'Нужен корректный email', trigger: 'blur' },
        { max: 255, message: 'Не более 255 символов', trigger: 'blur' },
    ],
    birthday: [
        { required: true, message: '"Дата рождения" - обязательное поле', trigger: 'change' },
        {
            validator: (_, value, callback) => {
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
    password: [
    { required: true, message: '"Пароль" - обязательное поле', trigger: 'blur' },
    { min: 8, message: 'Пароль должен содержать минимум 8 символов', trigger: 'blur' },
    { max: 100, message: 'Не более 255 символов', trigger: 'blur' },
    {
        validator: (rule, value, callback) => {
        if (!value) {
            callback();
            return;
        }
        
        const hasUpperCase = /[A-Z]/.test(value);
        const hasLowerCase = /[a-z]/.test(value);
        const hasNumbers = /\d/.test(value);
        const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value);
        
        if (!hasUpperCase || !hasLowerCase || !hasNumbers || !hasSpecialChar) {
            callback(new Error('Пароль должен содержать латинские буквы в верхнем и нижнем регистре, цифры и специальные символы'));
        } else {
            callback();
        }
        },
        trigger: 'blur'
    }
    ],
    passwordConfirmation: [
        { required: true, message: 'Подтверждение пароля обязательно', trigger: 'blur' },
        {
            validator: (_, value, callback) => {
                if (value !== form.value.password) {
                    callback(new Error('Пароли не совпадают'));
                } else {
                    callback();
                }
            },
            trigger: 'blur'
        }
    ],
    agree: [
    {
        validator: (_, value, callback) => {
            if (!value) {
                callback(new Error('Необходимо согласиться с условиями использования'));
            } else {
                callback();
            }
        },
        trigger: 'change'
    }
]
};
const loading = ref(false);
const visible = defineModel({
    type: Boolean,
    required: true,
});
const emit = defineEmits(['callAuth']);
const callAuth = () => emit('callAuth');
const close = () => {
    if (formRef.value) {
        formRef.value.resetFields();
    }
    visible.value = false;
};
const loadUserData = inject('globalMethods').loadUserData;

const handleSubmit = async () => {
    if (!formRef.value) return;
    
    try {
        const valid = await formRef.value.validate();
        
        if (valid) {
            loading.value = true;
            try {
                const result = await AuthService.register(form.value);

                if (result.success) {
                    userStore.setIsAuth(true);
                    ElMessage.success('Регистрация выполнена успешно!');

                    loadUserData();
                    close();
                } else {
                    throw result.message;
                }
            } catch (e) {
                ElMessage.error(e || 'Ошибка регистрации');
            } finally {
                loading.value = false;
            }
        }
    } catch (e) {
        ElMessage.warning('Пожалуйста, исправьте ошибки в форме');
    }
};
</script>
<template>
    <el-dialog 
        title="Регистрация"
        v-model="visible"
        center
        align-center
        width="30%"
        style="border-radius: 1rem"
        :before-close="close"
    >
        <el-form
            label-position="top"
            ref="formRef"
            :model="form"
            :rules="rules"
        >
            <el-form-item label="Имя" prop="name">
                <el-input
                    v-model="form.name"
                    placeholder="Введите ваше имя"
                />
            </el-form-item>
            <el-form-item label="Почта" prop="email">
                <el-input
                    v-model="form.email"
                    placeholder="Введите вашу почту"
                />
            </el-form-item>
            <el-form-item label="Дата рождения" prop="birthday">
                <el-date-picker
                    v-model="form.birthday"
                    type="date"
                    placeholder="Укажите свою дату рождения"
                    format="DD.MM.YYYY"
                    value-format="YYYY-MM-DD"
                    style="width: 100%"
                />
            </el-form-item>
            <el-form-item label="Пароль" prop="password">
                <el-input
                    v-model="form.password"
                    type="password"
                    placeholder="Придумайте пароль"
                    show-password
                />
            </el-form-item>
            <el-form-item label="Повторите пароль" prop="passwordConfirmation">
                <el-input
                    v-model="form.passwordConfirmation"
                    type="password"
                    placeholder="Пароль ещё раз"
                    show-password
                />
            </el-form-item>
            <el-form-item prop="agree">
                    <el-checkbox
                        v-model="form.agree"
                        label="Соглашаюсь с условиями использования"
                    />
            </el-form-item>
            <el-form-item>
                <div class="flex" style="width: 100%">
                    <el-button
                        type="primary"
                        text
                        @click="callAuth"
                    >
                        Уже есть аккаунт?
                    </el-button>
                    <el-button
                        @click="$router.push({name: 'Legal'}); visible = false"
                        text
                    >
                        Условия использования
                        <el-icon class="el-icon--right">
                            <info-filled/>
                        </el-icon>
                    </el-button>
                </div>
            </el-form-item>
        </el-form>
        <template #footer>
            <div class="flex gap">
                <el-button @click="close">Отмена</el-button>
                <el-button
                    type="primary"
                    :loading="loading"
                    @click="handleSubmit"
                >
                    Зарегистрироваться
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>