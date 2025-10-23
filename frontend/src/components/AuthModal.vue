<script setup>
import { ref, inject } from 'vue';
import { useUserStore } from '@/stores/userStore';
import { ElMessage } from 'element-plus';

const AuthService = inject('services').auth;
const userStore = useUserStore();
const formRef = ref();
const form = ref({});
const loading = ref(false);
const rules = ref({
  email: [{ required: true, message: 'Пожалуйста, введите почту', trigger: 'blur' },],
  password: [{ required: true, message: 'Пожалуйста, введите пароль', trigger: 'blur' },]
});
const visible = defineModel({
    type: Boolean,
    required: true,
});
const loadUserData = inject('globalMethods').loadUserData;

const emit = defineEmits(['callReg']);
const callReg = () => emit('callReg');
const close = () => visible.value = false;
const handleSubmit = () => {
  formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true;

      try {
        const result = await AuthService.login(
          form.value.email,
          form.value.password
        );

        if (result.error) {
          throw result.error;
        }

        userStore.setIsAuth(true);
        userStore.setUser(result.data.user);

        close();
        ElMessage.success('Вход выполнен!');
        loadUserData();
      } catch (e) {
        switch (e.response?.status ?? 400) {
          case 401:
            ElMessage.error('Неверный email или пароль');
            break;
          case 429:
            ElMessage.warning('Слишком много попыток, попробуйте позже');
            break;
          default:
            ElMessage.error(`Ошибка входа: ${e.error || e.message || 'Неизвестная ошибка'}`);
        }
      } finally {
        loading.value = false;
      }
    }
  })
}
</script>
<template>
<el-dialog title="Вход"
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
    @submit.prevent="handleSubmit"
    :rules="rules"
  >
  <el-form-item label="Почта" prop="email">
    <el-input v-model="form.email"/>
  </el-form-item>
  <el-form-item label="Пароль" prop="password">
    <el-input v-model="form.password" type="password" show-password/>
  </el-form-item>
  <el-form-item>
    <el-button
      text
      type="primary"
      @click="callReg"
    >Нет аккаунта?</el-button>
  </el-form-item>
  <el-form-item style="margin-bottom:0">
    <el-button
    type="primary"
    class="button"
    :loading="loading"
    native-type="submit">
    Войти
    </el-button>
  </el-form-item>
  </el-form>
</el-dialog>
</template>
<style scoped>
.button {
  margin-inline: auto;
  font-size: 1rem;
  min-width: 50%;
}
</style>