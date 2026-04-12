<template>
<el-dialog
    title="Стать продавцом"
    v-model="visible"
    center
    width="45%"
    :show-close="false"
    align-center
>
    <el-form
        label-position="top"
        ref="formRef"
        @submit.prevent="handleSubmit"
        id="becomeASellerForm"
        :rules="rules"
        :model="model"
    >
        <div class="flex gap">
            <div>
                <el-form-item label="Имя" prop="firstName">
                    <el-input
                        clearable
                        v-model="model.firstName"
                        placeholder="Введите имя"
                    />
                </el-form-item>
                <el-form-item label="Фамилия" prop="lastName">
                    <el-input
                        clearable
                        v-model="model.lastName"
                        placeholder="Введите фамилию"
                    />
                </el-form-item>
                <el-form-item label="Отчество" prop="patronymic">
                    <el-input
                        clearable
                        v-model="model.patronymic"
                        placeholder="Введите отчество (оставьте два тире если его нет)"
                    />
                </el-form-item>
                <el-form-item label="Тип субъекта" prop="type">
                    <el-select
                        v-model="model.type"
                        :options="options"
                    />
                </el-form-item>
                <el-form-item :label="model.type === 'LLC' ? 'ОГРН' : 'ИНН'" prop="code" style="margin-bottom: 0">
                    <el-input
                        clearable
                        v-model="model.code"
                        :placeholder="`Введите ${model.type === 'LLC' ? 'ОГРН' : 'ИНН'}`"
                    />
                </el-form-item>
            </div>
            <div>
                <el-form-item label="Реквизиты оплаты" prop="payment_account">
                    <el-input
                        clearable
                        v-model="model.payment_account"
                        placeholder="Введите реквизиты оплаты"
                    />
                </el-form-item>
                <div class="flex gap">
                    <el-form-item label="Серия паспорта" prop="pass_series" style="flex:1">
                        <el-input
                            clearable
                            v-model="model.pass_series"
                            placeholder="Серия"
                        />
                    </el-form-item>
                    <el-form-item label="Номер паспорта" prop="pass_number" style="flex:1">
                        <el-input
                            clearable
                            v-model="model.pass_number"
                            placeholder="Номер"
                        />
                    </el-form-item>
                </div>
                <el-form-item label="Скан паспорта" prop="passport_scan" style="margin-bottom: 0">
                    <el-upload
                        :show-file-list="false"
                        :auto-upload="false"
                        :accept="types.join(',')"
                        :limit="1"
                        style="width: 100%"
                        :on-change="handleFileChange"
                        ref="uploaderRef"
                        @click="clearFile"
                    >
                        <el-avatar
                            size="large"
                            shape="square"
                            :src="imageUrl"
                            style="width: 100%; height: 100%"
                        >
                        Кликните для выбора фото. Разрешены только фото формата JPEG/JPG/PNG. Размер фото - не более 2Мб.
                        </el-avatar>
                    </el-upload>
                </el-form-item>
            </div>
        </div>
    </el-form>
    <template #footer>
        <div class="flex gap">
            <el-button @click="cancel">Отмена</el-button>
            <el-button
                type="primary"
                :loading="loading"
                native-type="submit"
                form="becomeASellerForm"
                :disabled="isBecomeDisabled || !selectedFile"
            >Отправить</el-button>
        </div>
    </template>
</el-dialog>
</template>
<script setup lang="ts">
import { computed, inject, reactive, ref } from 'vue'
import type { BecomeASellerForm } from '@/ts/types'
import { createMaxRule, createMinRule, createRequiredRule, type Rules } from '@/ts/utils'
import { ElMessage, type UploadFile } from 'element-plus'
import type { Services } from '@/ts/services'

const visible = defineModel<boolean>()
const formRef = ref()
const selectedFile = ref<File>()
const loading = ref(false);
const uploaderRef = ref()
const model = reactive<Partial<BecomeASellerForm>>({
    firstName: '', lastName: ''
})
const SellerService = (inject('services') as Services).seller;
const rules: Rules = {
    firstName: [ createRequiredRule('Имя'), createMinRule(2), createMaxRule(100) ],
    lastName: [ createRequiredRule('Фамилия'), createMinRule(2), createMaxRule(100) ],
    patronymic: [ createRequiredRule('Отчество'), createMinRule(2), createMaxRule(100) ],
    code: [ createRequiredRule('ИНН/ОГРН'), createMinRule(12), createMaxRule(15) ],
    payment_account: [ createRequiredRule('Реквизиты оплаты'), createMinRule(10) ],
    pass_series: [ createRequiredRule('Серия'), createMinRule(4), createMaxRule(4) ],
    pass_number: [ createRequiredRule('Номер'), createMinRule(6), createMaxRule(6) ],
    type: [ createRequiredRule('Тип субъекта') ],
    passport_numbers: [
        {
            validator: (_: any, value: any, callback: Function) => {
                if (model.pass_number?.length === 6 && model.pass_series?.length === 4)
                    callback();
                else
                    callback(new Error('Серия и номер - обязательные поля'));
            },
            trigger: 'blur'
        }
    ]
}
const options = [
    { label: 'Юридическое лицо (ООО/ОАО/ПАО и т.д.)', value: 'LLC' },
    { label: 'Самозанятость', value: 'self-employed' },
    { label: 'Индивидуальный предприниматель', value: 'individual' }
], types = ['image/jpeg', 'image/png', 'image/jpg']

const imageUrl = computed<string>(() => (selectedFile.value ?
    URL.createObjectURL(selectedFile.value) : URL.createObjectURL(model.passport_scan ?? new Blob())) ?? '')
const isBecomeDisabled = computed(() => (Object.keys(model) as Array<keyof typeof model>)
        .some(key => !model[key]))

const handleFileChange = (file: UploadFile) => selectedFile.value = file.raw
const clearFile = () => uploaderRef.value.clearFiles()
const cancel = () => {
    (Object.keys(model) as Array<keyof typeof model>).forEach(key => model[key] = undefined)
    selectedFile.value = undefined

    visible.value = false
}
const handleSubmit = async () => {
    if (!formRef.value || !await formRef.value.validate()) return;

    try {
        if (selectedFile.value) {
            if (!types.includes(selectedFile.value.type)) {
                ElMessage.warning('Разрешены только фото формата JPEG, JPG и PNG');
                return;
            }
            if (selectedFile.value.size / 1024 / 1024 > 2) {
                ElMessage.warning('Размер фото - не более 2Мб')
                return;
            }
        } else {
            throw new Error('Требуется скан паспорта');
        }
        const response = await SellerService.store({
            full_name: `${model.lastName} ${model.firstName} ${model.patronymic ?? ''}`,
            code: model.code as string,
            payment_account: model.payment_account as string,
            passport_numbers: `${model.pass_series} ${model.pass_number}`,
            passport_scan: selectedFile.value as File,
            type: model.type as 'self_employed' | 'individual' | 'LLC',
        });

        if (response.success) {
            ElMessage.success('Заявка отправлена! Дождитесь одобрения');
            (Object.keys(model) as Array<keyof typeof model>).forEach(key => {
                delete model[key];
            })
        } else {
            console.error(response)
            throw new Error(response.message)
        }
    } catch (e) {
        ElMessage.error(e instanceof Error ? e.message : 'Не удалось отправить заявку')
    }
}
</script>
<style scoped>
.el-form > .flex.gap > div {
    flex: 1;
}
.el-form > .flex.gap {
    align-items: start;
}
</style>