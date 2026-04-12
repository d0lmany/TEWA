<template>
<el-dialog
    :title="`${shopStore.currentMode === 'create' ? 'Создание' : 'Редактирование'} товара`"
    v-model="visible"
    center
    align-center
    width="50%"
    :show-close="false"
>
    <el-empty
        v-if="loadings.get"
        description="Загружаю..."
    />
    <el-form
        v-else
        inline
        :rules="rules"
        v-model="product"
        label-position="top"
        ref="formRef"
        @submit.prevent="handleSubmit"
        id="createProductForm"
    >
        <div class="flex col" style="align-items: start; padding-right: 2rem; flex: 1;">
            <el-form-item label="Главное фото" prop="photo">
                <el-upload
                    :show-file-list="false" :auto-upload="false"
                    :accept="types.join(',')" :limit="1"
                    :on-change="handleFileChange" ref="uploaderRef"
                    @click="clearFile"
                >
                    <el-avatar
                        size="large"
                        shape="square"
                        :src="imageUrl"
                        style="width: 250px; height: 250px"
                    >Кликните для выбора фото. Разрешены только фото формата JPEG/JPG/PNG. Размер фото - не более 2Мб.</el-avatar>
                </el-upload>
            </el-form-item>
            <el-form-item label="Название" prop="name" style="width: 100%">
                <el-input v-model="product.name" clearable/>
            </el-form-item>
            <el-form-item label="Количество" prop="quantity">
                <el-input-number v-model.number="product.quantity" :min="0"/>
            </el-form-item>
            <el-form-item label="Теги" prop="tags" style="width: 100%">
                <el-select
                    v-model="product.tags"
                    multiple collapse-tags
                    collapse-tags-tooltip
                    clearable placeholder="Выберите теги"
                >
                    <el-option
                        v-for="tag in tags"
                        :key="tag.id"
                        :value="tag.name"
                    >
                    <div class="flex">
                        <span>{{ tag.name }}</span>
                        <el-popover width="200">
                            <div style="text-align: justify">{{ tag.description }}</div>
                            <template #reference>
                                <el-icon><info-filled/></el-icon>
                            </template>
                        </el-popover>
                    </div>
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="Базовая цена" prop="base_price">
                <el-input 
                    v-model.number="product.price.base_price"
                    clearable :min="0"
                />
            </el-form-item>
            <el-form-item label="Процент скидки" prop="discount">
                <el-input-number
                    v-model.number="product.price.discount"
                    clearable :max="100" :min="0"
                />
            </el-form-item>
            <el-text size="large">Итоговая цена: {{ formatter.format(finalPrice) }}</el-text>
        </div>
        <div class="flex col" style="align-items: start; flex: 1.75">
            <el-form-item label="Категория" prop="category_id" style="width: 100%">
                <el-select v-model="product.category.id">
                    <el-option
                        v-for="opt in categories"
                        :key="opt.id"
                        :label="opt.name"
                        :value="opt.id"
                    />
                </el-select>
            </el-form-item>
            <el-form-item label="Описание" prop="description" style="width: 100%">
                <el-input
                    type="textarea"
                    placeholder="Придумайте описание к товару"
                    v-model="product.details!.description"
                    resize="none"
                    class="textarea"
                />
            </el-form-item>
            <el-form-item label="Способ применения" prop="application" style="width: 100%">
                <el-input
                    type="textarea"
                    placeholder="Расскажите как этим пользоваться"
                    v-model="product.details!.application"
                    resize="none"
                    class="textarea"
                />
            </el-form-item>
        </div>
    </el-form>
    <template #footer>
        <div class="flex gap">
            <el-button @click="cancel">Отмена</el-button>
            <el-button
                type="primary"
                :loading="loadings.set"
                native-type="submit"
                form="createProductForm"
            >Отправить</el-button>
        </div>
    </template>
</el-dialog>
</template>
<script setup lang="ts">
import { useShopStore } from '@/stores/shop'
import type { Category, FullProduct, Tag } from '@/ts/entities'
import type { Services } from '@/ts/services'
import type { UI } from '@/ts/types'
import { InfoFilled } from '@element-plus/icons-vue'
import { ElMessage, type UploadFile } from 'element-plus'
import { computed, inject, onMounted, reactive, ref, watch } from 'vue'

const shopStore = useShopStore()
const visible = defineModel<boolean>()
// TODO: заполнить эту штуку + заблокировать кнопку отправки
const rules =  {}
const {
    product: ProductService,
    category: CategoryService,
    tag: TagService,
} = inject('services') as Services
const formatter = (inject('ui') as UI).currencyFormatter
const selectedFile = ref<File>()
const formRef = ref()
const types = ['image/jpeg', 'image/png', 'image/jpg'], categories: Category[] = [], tags: Tag[] = []
const uploaderRef = ref()
// @ts-ignore
const product = reactive<FullProduct>({
    ...shopStore.emptyProduct
})
const loadings = reactive({
    get: false, set: false,
})

const finalPrice = computed(() => 
    product.price.base_price * (1 - product.price.discount * 0.01)
)
const imageUrl = computed<string>(() => selectedFile.value ?
    URL.createObjectURL(selectedFile.value || new Blob()) : product.photo)

const loadProduct = async (id: number) => {
    loadings.get = true;
    try {
        const response = await ProductService.show(id)
        if (response.success && response.data) {
            Object.assign(product, response.data);
            loadings.get = false;
        } else {
            console.error(response);
            throw new Error(response.message)
        }
    } catch (e) {
        ElMessage.error(`Не удалось загрузить товар: ${e instanceof Error ? e.message : 'Неизвестная ошибка'}`);
        shopStore.createFormVisible = false;
        loadings.get = false;
    }
}
const handleFileChange = (file: UploadFile) => selectedFile.value = file.raw
const clearFile = () => {
    uploaderRef.value.clearFiles()
    if (selectedFile.value)
        URL.revokeObjectURL(imageUrl.value);
}
const handleSubmit = async () => {
    if (!formRef.value/* || !await formRef.value.validate()*/) {
        console.error(formRef.value)
        return
    };

    try {
        loadings.set = true;
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
            if (shopStore.currentMode !== 'update')
                throw new Error('Требуется фото товара');
        }
        let response;
        if (shopStore.currentMode === 'create') {
            response = await ProductService.store({
                name: product.name, quantity: product.quantity,
                base_price: product.price.base_price, discount: product.price.discount,
                photo: selectedFile.value, category_id: product.category.id,
                tags: product.tags.map(tag => tag.id), status: 'on',
                shop_id: shopStore.shop.id,
            });
        } else {
            response = await ProductService.update(product.id, {
                name: product.name, quantity: product.quantity,
                base_price: product.price.base_price, discount: product.price.discount,
                category_id: product.category.id,
                tags: product.tags.map(tag => tag.id), status: 'on',
            });
        }

        if (response.success) {
            ElMessage.success(`Товар успешно ${shopStore.currentMode === 'create' ? 'создан' : 'обновлён'}!`)
        } else {
            throw new Error(response.message);
        }
    } catch (e) {
        ElMessage.error(e instanceof Error ? e.message :
            `Не удалось ${shopStore.currentMode === 'create' ? 'создать' : 'обновить'} товар`)
    } finally {
        loadings.set = false
    }
}
const cancel = () => {
    shopStore.clearCurrentProduct()
    Object.assign(product, shopStore.emptyProduct)
    shopStore.createFormVisible = false
}

onMounted(async () => {
    // categories
    const responseCats = await CategoryService.index();
    if (responseCats.success && responseCats.data) categories.push(...responseCats.data.filter(cat => cat.parent));
    // tags
    const responseTags = await TagService.index();
    if (responseTags.success && responseTags.data) tags.push(...responseTags.data);
})

watch(
    () => shopStore.createFormVisible,
    (isVisible) => {
        if (isVisible && shopStore.currentMode === 'update') {
            loadProduct(shopStore.currentProduct?.id || 0);
        } else {
            shopStore.clearCurrentProduct()
            Object.assign(product, shopStore.emptyProduct)
        }
    }
)
</script>
<style scoped>
.textarea:deep(textarea) {
    min-height: 240px;
    scrollbar-width: thin;
}
</style>