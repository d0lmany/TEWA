<script setup lang="ts">
import { ElMessage } from 'element-plus';
import { useUserStore } from '@/stores/userStore';
import { ref, inject, reactive } from 'vue';
import type { RegistrationData } from '@/ts/types/UserR-R';
import type { default as US } from '@/ts/services/UserService';
import type { default as AS } from '@/ts/services/ApiService';
import type Services from '@/ts/types/Services';
import { AuthState } from '@/ts/types/AuthState';

const userStore = useUserStore();
const formRef = ref();
const UserService: US = (inject('services') as Services).user;
const ApiService: AS = (inject('services') as Services).api;
const form = reactive<RegistrationData>({
   name: '',
   email: '',
   birthday: '',
   password: '',
   password_confirmation: '',
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
   password: [
   { required: true, message: '"Пароль" - обязательное поле', trigger: 'blur' },
   { min: 8, message: 'Пароль должен содержать минимум 8 символов', trigger: 'blur' },
   { max: 100, message: 'Не более 255 символов', trigger: 'blur' },
   {
      validator: (rule: any, value: any, callback: Function) => {
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
};
const loading = ref(false);
const visible = defineModel({
   type: Boolean,
   required: true,
});
const emit = defineEmits(['callLog']);
const callLog = () => emit('callLog');

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
         const result = await UserService.registration(form);

         if (result.success && result.data) {
            ApiService.authToken = result.data.token;

            loadUser()
               .then(state => {
                  if (state === AuthState.Reject) {
                     ElMessage.error('Не удалось войти');
                  } else {
                     ElMessage.success('Регистрация выполнена!');
                     
                     if (formRef.value) {
                        formRef.value.resetFields();
                     }
                     visible.value = false;
                  }
               })
               .catch(error => {
                  console.error('Unexpected error in loadUser:', error);
                  ElMessage.error('Произошла непредвиденная ошибка');
               });
         }
      } catch (error) {
         console.error(error);
      } finally {
         loading.value = false;
      }
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
      :show-close="false"
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
               v-model="form.password_confirmation"
               type="password"
               placeholder="Пароль ещё раз"
               show-password
            />
         </el-form-item>
         <el-form-item>
            <el-text class="text">
               Нажимая "Зарегистрироваться" вы соглашаетесь с
               <el-text
                  type="primary"
                  @click="$router.push({name: 'Legal'}); visible = false"
                  class="inner-link"
               >Условиями использования</el-text>
            </el-text>
         </el-form-item>
      </el-form>
      <template #footer>
         <div class="flex gap">
            <el-button @click="callLog" text>Уже есть аккаунт?</el-button>
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
<style scoped>
.text {
   text-align: center;
   line-height: 1.5rem;
}
.inner-link {
   transition: var(--el-transition-border);
   border-bottom: 1px solid transparent;
}
.inner-link:hover {
   cursor: pointer;
   border-bottom-color: var(--el-color-primary);
}
</style>