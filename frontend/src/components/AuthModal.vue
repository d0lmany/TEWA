<template>
  <el-dialog 
    :model-value="visible" 
    @update:model-value="$emit('update:visible', $event)"
    title="Вход" width="20%"
    center align-center
    :before-close="handleClose">
    <el-form label-position="top"
      ref="formRef"
      :model="formData"
      :rules="rules"
      @submit.prevent="handleSubmit">
      <el-form-item label="Почта" prop="email">
        <el-input v-model="formData.email"/>
      </el-form-item>
      <el-form-item label="Пароль" prop="password">
        <el-input v-model="formData.password" type="password" show-password/>
      </el-form-item>
      <el-form-item>
        <el-row class="w" justify="space-between">
          <el-col :span="3.5">
            <el-link @click="openReg">Нет аккаунта?</el-link>
          </el-col>
          <el-col :span="3.5">
            <el-link>Забыли пароль?</el-link>
          </el-col>
        </el-row>
      </el-form-item>
      <el-form-item class="margin-bottom:0">
        <div class="w sub">
          <el-button
          type="primary"
          native-type="submit"
          :loading="loading"
          >Войти</el-button>
        </div>
      </el-form-item>
    </el-form>
  </el-dialog>
</template>

<script setup>
import { ElDialog, ElMessage, ElMessageBox } from 'element-plus';
import { ref, inject } from 'vue';

const props = defineProps({
  visible: Boolean
});

const emit = defineEmits(['update:visible', 'close']);

const authState = inject('authState');
const authService = inject('authService');
const authModal = inject('authModal');
const regModal = inject('regModal');

const formRef = ref();
const loading = ref(false);

const formData = ref({
  email: '',
  password: ''
});

const rules = {
  email: [
    { required: true, message: 'Пожалуйста, введите почту', trigger: 'blur' },
  ],
  password: [
    { required: true, message: 'Пожалуйста, введите пароль', trigger: 'blur' },
  ]
};
const openReg = () => {
  closeModal();
  regModal.open();
}

const closeModal = () => {
  emit('update:visible', false);
  authModal.close();
};

const handleClose = (done) => {
  closeModal();
  done();
};

const handleSubmit = () => {
  formRef.value.validate(async (valid) => {
    if (valid) {
      loading.value = true;
      
      try {
        const result = await authService.login(
          formData.value.email,
          formData.value.password
        );
        
        if (result.error) {
          throw new Error(result.error);
        }
        
        authState.isAuthenticated.value = true;
        if (authState.user && result.user) {
          authState.user.value = result.user;
        }
        
        loading.value = false;
        closeModal();
        
        ElMessage.success('Успешный вход!');
        
      } catch (e) {
        loading.value = false;
        
        if (e.response?.status === 401) {
          ElMessageBox.alert('Неверный email или пароль', 'Ошибка авторизации');
        } else if (e.response?.status === 429) {
          ElMessageBox.alert('Слишком много попыток. Попробуйте позже', 'Ошибка');
        } else {
          const errorMessage = e.error || e.message || 'Неверные данные';
          ElMessageBox.alert(`Ошибка входа: ${errorMessage}`, 'Ошибка');
        }
      }
    }
  });
};
</script>
<style scoped>
.w {
  width: 100%;
}
.sub {
  display: flex;
  justify-content: center;
}
.sub button {
  font-size: 1rem !important;
}
</style>