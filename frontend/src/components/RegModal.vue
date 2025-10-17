<script setup>
import { ElMessage } from 'element-plus';
import { useUserStore } from '@/stores/userStore';
import { ref, inject } from 'vue';

const AuthService = inject('services').auth;
const userStore = useUserStore();
const formRef = ref();
const form = ref({
    name: '',
    email: '',
    birthday: '',
    password: '',
    password2: ''
});
const rules = {
    name: [
        { required: true, message: '"Имя" - обязательное поле', trigger: 'blur' },
        { min: 2, message: 'Не менее двух символов', trigger: 'blur' }
    ],
    email: [
        { required: true, message: '"Почта" - обязательное поле', trigger: 'blur' },
        { type: 'email', message: 'Нужен корректный email', trigger: 'blur' }
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
        { min: 8, message: 'Пароль должен иметь длину не менее 8 символов', trigger: 'blur' }
    ],
    password2: [
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
    ]
};

const loading = ref(false);
const props = defineProps({
    visible: Boolean
});
const emit = defineEmits(['close', 'callAuth']);
const callAuth = () => emit('callAuth');
const close = () => {
    if (formRef.value) {
        formRef.value.resetFields();
    }
    emit('close');
};

const handleSubmit = async () => {
    if (!formRef.value) return;
    
    try {
        const valid = await formRef.value.validate();
        
        if (valid) {
            loading.value = true;
            try {
                const result = await AuthService.register(form.value);

                if (result.user) {
                    userStore.setIsAuth(true);
                    userStore.setUser(result.user);
                    close();
                    ElMessage.success('Регистрация и вход выполнены успешно!');
                } 
                else if (result.needLogin) {
                    close();
                    ElMessage.success('Регистрация успешна! Теперь вы можете войти в систему.');
                    callAuth();
                }
                
            } catch (e) {
                ElMessage.error(e.message || 'Ошибка при регистрации');
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
        v-model="props.visible"
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
            <el-form-item label="Повторите пароль" prop="password2">
                <el-input
                    v-model="form.password2"
                    type="password"
                    placeholder="Пароль ещё раз"
                    show-password
                />
            </el-form-item>
            <el-form-item>
                <el-button
                    type="primary"
                    text
                    @click="callAuth"
                >
                    Уже есть аккаунт?
                </el-button>
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