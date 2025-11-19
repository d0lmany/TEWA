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
  email: [
    { required: true, message: 'Пожалуйста, введите почту', trigger: 'blur' },
    { max: 255, message: 'Максимум 255 символов', trigger: 'blur' },
  ],
  password: [
    { required: true, message: 'Пожалуйста, введите пароль', trigger: 'blur' },
    { max: 100, message: 'Максимум 100 символов', trigger: 'blur' },
    { min: 8, message: 'Минимум 8 символов', trigger: 'blur' },
  ]
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

        if (result.success) {
          userStore.setUser(result.data);

          ElMessage.success('Вход выполнен!');

          close();
          loadUserData();
        } else {
          throw result;
        }
      } catch (error) {
        let msg;
        switch (error.status) {
          case 401:
            msg = 'Неверный email или пароль';
            break;
          case 429:
            msg = 'Слишком много попыток, попробуйте позже';
            break;
          default:
            msg = `Неизвестная ошибка. ${error.message}`;
        }

        ElMessage.error(msg);
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
  width="25%"
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
  <el-form-item></el-form-item>
  <el-form-item label="Пароль" prop="password">
    <el-input v-model="form.password" type="password" show-password/>
  </el-form-item>
  <el-form-item></el-form-item>
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