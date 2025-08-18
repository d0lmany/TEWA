<script setup>
import { ElDialog, ElForm, ElFormItem, ElInput, ElDatePicker, ElButton, ElMessage } from 'element-plus';
import { ref, inject } from 'vue';

const props = defineProps({
    visible: Boolean
});

const emit = defineEmits(['update:visible', 'login-click']);

const authService = inject('authService');
const authState = inject('authState');
const formRef = ref();
const formData = ref({
    name: '',
    email: '',
    birthday: '',
    password: '',
    password2: ''
});
const modal = inject('regModal');
const loading = ref(false);
const rules = {
    name: [
        { 
            required: true, 
            message: 'Пожалуйста, введите ваше имя', 
            trigger: 'blur' 
        },
        { 
            min: 2, 
            max: 50, 
            message: 'Имя должно содержать от 2 до 50 символов', 
            trigger: 'blur' 
        }
    ],
    email: [
        { 
            required: true, 
            message: 'Пожалуйста, введите ваш email', 
            trigger: 'blur' 
        },
        { 
            type: 'email', 
            message: 'Введите корректный email', 
            trigger: 'blur' 
        }
    ],
    birthday: [
        { 
            required: true, 
            message: 'Пожалуйста, выберите дату рождения', 
            trigger: 'change' 
        },
        { 
            validator: (rule, value, callback) => {
                if (value) {
                    const today = new Date();
                    const birthDate = new Date(value);
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const monthDiff = today.getMonth() - birthDate.getMonth();
                    
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }
                    
                    if (age < 13) {
                        callback(new Error('Вам должно быть не менее 13 лет для регистрации'));
                    } else {
                        callback();
                    }
                } else {
                    callback();
                }
            }, 
            trigger: 'change' 
        }
    ],
    password: [
        { 
            required: true, 
            message: 'Пожалуйста, введите пароль', 
            trigger: 'blur' 
        },
        { 
            min: 6, 
            message: 'Пароль должен содержать не менее 6 символов', 
            trigger: 'blur' 
        }
    ],
    password2: [
        { 
            required: true, 
            message: 'Пожалуйста, повторите пароль', 
            trigger: 'blur' 
        },
        { 
            validator: (rule, value, callback) => {
                if (value !== formData.value.password) {
                    callback(new Error('Пароли не совпадают'));
                } else {
                    callback();
                }
            }, 
            trigger: 'blur' 
        }
    ]
};

const handleSubmit = async () => {
    try {
        await formRef.value.validate();
        
        loading.value = true;
        
        try {
            const response = await authService.register(formData.value);
            
            authState.isAuthenticated.value = true;
            authState.user.value = response.user;
            
            loading.value = false;
            
            ElMessage.success('Регистрация прошла успешно!');
            ElMessage.success('Вход выполнен');
            handleClose();
            
        } catch (error) {
            loading.value = false;
            ElMessage.error('Ошибка регистрации: ' + error);
        }
        
    } catch (error) {
        loading.value = false;
        ElMessage.warning('Форма содержит ошибки: ' + error);
    }
};

const handleClose = () => {
    emit('update:visible', false);
    formRef.value?.resetFields();
    modal.close();
};

const handleLoginClick = () => {
    handleClose();
    emit('login-click');
};
</script>

<template>
<el-dialog 
    :model-value="visible" 
    center
    align-center 
    title="Регистрация в TEWA"
    width="25%"
    @close="handleClose"
>
    <el-form 
        label-position="top"
        ref="formRef" 
        :model="formData"
        :rules="rules"
        @submit.prevent="handleSubmit"
    >
        <el-form-item label="Имя" prop="name">
            <el-input 
                v-model="formData.name" 
                placeholder="Введите ваше имя"
            />
        </el-form-item>
        
        <el-form-item label="Почта" prop="email">
            <el-input 
                v-model="formData.email" 
                type="email"
                placeholder="Введите ваш email"
            />
        </el-form-item>
        
        <el-form-item label="Дата рождения" prop="birthday">
            <el-date-picker
                v-model="formData.birthday"
                type="date"
                placeholder="Выберите дату"
                format="DD.MM.YYYY"
                value-format="YYYY-MM-DD"
                style="width: 100%"
            />
        </el-form-item>
        
        <el-form-item label="Пароль" prop="password">
            <el-input 
                v-model="formData.password" 
                type="password"
                placeholder="Введите пароль"
                show-password
            />
        </el-form-item>
        
        <el-form-item label="Повторите пароль" prop="password2">
            <el-input 
                v-model="formData.password2" 
                type="password"
                placeholder="Повторите пароль"
                show-password
            />
        </el-form-item>
    </el-form>
    <template #footer>
        <div class="flex gap">
            <el-button @click="handleClose">
                Отмена
            </el-button>
            <el-button 
                type="primary" 
                @click="handleSubmit"
                :loading="loading">
                Зарегистрироваться
            </el-button>
        </div>
        <el-button @click="handleLoginClick" type="text">
            У меня есть аккаунт
        </el-button>
    </template>
</el-dialog>
</template>

<style scoped>
.dialog-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>