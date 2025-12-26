<script setup lang="ts">
import { inject, reactive, ref } from 'vue';
import { useUserStore } from '@/stores/userStore';
import { ElMessage } from 'element-plus';
import type { LoginData } from '@/ts/types/UserR-R';
import { default as US } from '@/ts/services/UserService';
import type { default as AS } from '@/ts/services/ApiService';
import type Services from '@/ts/types/Services';
import { AuthState } from '@/ts/types/AuthState';
import { createRequiredRule, type Rules } from '@/ts/utils/FormRules';

const userStore = useUserStore();
const formRef = ref();
const form: LoginData = reactive<LoginData>({
    email: '', password: ''
});
const loading = ref(false);
const UserService: US = (inject('services') as Services).user;
const ApiService: AS = (inject('services') as Services).api;
const rules: Rules = {
    email: [ createRequiredRule('Почта') ],
    password: [ createRequiredRule('Пароль') ]
};
const visible = defineModel({
    type: Boolean,
    required: true,
});

const emit = defineEmits(['callReg']);
const callReg = () => emit('callReg');
const loadUser = async (): Promise<AuthState> => {
    try {
        const userData = await UserService.loadUserData();

        if (userData.success && userData.data) {
            const {cart, favorite, user, errors} = userData.data;
            if (!cart || !favorite || !user) {
                ElMessage.warning([errors.cart, errors.favorite, errors.user].join('. '));
                console.error(errors.cart, errors.favorite, errors.user)
            }

            userStore.login(user);
            userStore.setCart(cart);
            userStore.setFavorite(favorite);

            return AuthState.Accept;
        }

        return AuthState.Reject;
    } catch (error) {
        console.error(error);
        return AuthState.Reject;
    }
}
const handleSubmit = async () => {
    if (formRef.value && await formRef.value.validate()) {
        loading.value = true;

        try {
            const result = await UserService.login(form);

            if (result.success && result.data) {
                ApiService.authToken = result.data.token;

                loadUser()
                    .then(state => {
                        if (state === AuthState.Reject) {
                            ElMessage.warning('Не удалось загрузить данные вашего профиля');
                        } else {
                            ElMessage.success('Вход выполнен!');
                            visible.value = false;
                        }
                    })
                    .catch(error => {
                        console.error('Unexpected error in loadUser:', error);
                        ElMessage.error('Произошла непредвиденная ошибка');
                    });
            } else {
                console.error(result);
                throw new Error(result.message);
            }
        } catch (error) {
            ElMessage.error(error instanceof Error ? error.message : 'Не удалось войти');
        } finally {
            loading.value = false;
        }
    }
}
</script>
<template>
<el-dialog title="Вход"
    v-model="visible"
    center
    align-center
    width="25%"
    :show-close="false"
>
    <el-form
        label-position="top"
        ref="formRef"
        :model="form"
        @submit.prevent="handleSubmit"
        :rules="rules"
    >
    <el-form-item label="Почта" prop="email">
        <el-input v-model="form.email"/>
    </el-form-item>
    <el-form-item></el-form-item>
    <el-form-item label="Пароль" prop="password">
        <el-input v-model="form.password" type="password" show-password/>
    </el-form-item>
        <el-form-item style="margin-bottom:0">
            <div class="flex" style="width:100%">
                <el-button
                    text
                    type="primary"
                    @click="callReg"
                >
                Нет аккаунта?
                </el-button>
                <el-button
                    type="primary"
                    class="button"
                    :loading="loading"
                    native-type="submit"
                >
                Войти
                </el-button>
            </div>
        </el-form-item>
    </el-form>
</el-dialog>
</template>
<style scoped>
.button {
    font-size: 1rem;
    min-width: 35%;
    padding: .5rem .25rem;
}
</style>