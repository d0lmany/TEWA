<script setup lang="ts">
import type { Address, ExactAddress, Pickup } from '@/ts/entities/Addresses';
import { type Rules, createMaxRule, createMinRule, createRequiredRule, createTypeRule } from '@/ts/utils/FormRules';
import type Services from '@/ts/types/Services';
import { ElMessage } from 'element-plus';
import { reactive, ref, inject, watch, onUnmounted, onMounted, nextTick } from 'vue';

const {
    address: AddressService,
    pickup: PickupService,
} = (inject('services') as Services);
const visible = defineModel({
    type: Boolean,
    required: true,
});
const { addressType } = defineProps<{
    addressType: 'address' | 'pickup'
}>();
const loading = ref<boolean>(false);
const paginate = reactive<{
    observer: IntersectionObserver | null,
    page: number,
    loading: boolean,
    hasMore: boolean
}>({
    observer: null,
    page: 1,
    loading: false,
    hasMore: true
});
const sentinel = ref(null);
const formRef = ref();
const pickups = reactive<Pickup[]>([]);
const emit = defineEmits<{ addAddress: [value: Address] }>();
const addressForm = reactive<ExactAddress>({
    country: 'Росссийская Федерация',
    region: '',
    city: '',
    street: '',
    house_number: '',
});
const rules: Rules = {
    country: [
        createTypeRule('Некорректная строка'),
        createRequiredRule('Страна'),
        createMinRule(3),
        createMaxRule(100)
    ],
    region: [
        createTypeRule('Некорректная строка'),
        createRequiredRule('Регион/Область'),
        createMinRule(3),
        createMaxRule(150)
    ],
    city: [
        createTypeRule('Некорректная строка'),
        createRequiredRule('Город'),
        createMinRule(3),
        createMaxRule(150)
    ],
    street: [
        createTypeRule('Некорректная строка'),
        createRequiredRule('Улица'),
        createMinRule(3),
        createMaxRule(150)
    ],
    house_number: [
        createTypeRule('Некорректный номер'),
        createRequiredRule('Дом/Строение'),
    ],
    apartment: [
        createMinRule(0, 'number'),
    ]
};
const handleAddressSubmit = async () => {
    if (!formRef.value || !await formRef.value.validate()) return;

    try {
        loading.value = true;

        const address = (addressForm.zip_code ? addressForm.zip_code + ', ' : '') +
            [addressForm.country, addressForm.region, addressForm.city, addressForm.street, addressForm.house_number].join(', ')
            + (addressForm.apartment ? ', ' + addressForm.apartment : '');

        const response = await AddressService.store({
            address: address,
            is_default: false,
        });

        if (response.success && response.data) {
            ElMessage.success('Адрес добавлен');
            const addr: Address = {
                id: response.data.id,
                address: address,
                is_default: false,
            };
            emit('addAddress', addr);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось добавить адрес');
    } finally {
        loading.value = false;
    }
}
const loadPickups = async () => {
    if (paginate.loading || !paginate.hasMore) return;

    paginate.loading = true;
    try {
        const response = await PickupService.index({
            params: { page: paginate.page }
        });

        if (response.success && response.data) {
            const { data, last_page }: { data: Pickup[], last_page: number } = response.data;

            pickups.push(...data);

            if (last_page && paginate.page >= last_page) {
                paginate.hasMore = false;
            } else if (paginate.page >= last_page) {
                paginate.hasMore = false;
            } else {
                paginate.page++;
                await loadPickups();
            }
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Произошла ошибка при загрузке ПВЗ');
    } finally {
        paginate.loading = false;
    }
}
const cancel = () => {
    (Object.keys(addressForm) as Array<keyof ExactAddress>).forEach(field => {
        if (addressForm[field]) {
            (addressForm[field] as any) = '';
        }
    })
    visible.value = false;

    paginate.observer = null;
    paginate.page = 1;
    paginate.loading = false;
    paginate.hasMore = false;
}
const addPickup = async (id: number) => {
    try {
        const response = await AddressService.store({
            is_default: false,
            pickup_id: id,
        });
        
        if (response.success && response.data) {
            ElMessage.success('ПВЗ добавлен');
            const pickup = (pickups.find(p => p.id === id) as Pickup);
            const addr: Address = {
                id: response.data.id,
                pickup_id: id,
                is_default: false,
                pickup: pickup
            };
            emit('addAddress', addr);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось добавить ПВЗ');
    }
}

watch(visible, async () => {
    if (visible.value && addressType === 'pickup') {
        if (paginate.hasMore) {
            await loadPickups()
        }
    }
})

onMounted(() => {
    loadPickups()

    paginate.observer = new IntersectionObserver((entries: IntersectionObserverEntry[]) => {
        if (!paginate.observer && !entries.length) return;

        if (!paginate.loading && !paginate.hasMore) return;

        loadPickups()
    }, { threshold: 0.1 });

    nextTick(() => {
        if (sentinel.value && paginate.observer) {
            paginate.observer.observe(sentinel.value);
        }
    });
});

onUnmounted(() => {
    if (paginate.observer) {
        paginate.observer.disconnect();
    }
});
</script>
<template>
<el-dialog
    :title="`Добавить ${addressType === 'address' ? 'адрес' : 'ПВЗ'}`"
    v-model="visible"
    center
    width="40%"
    style="border-radius: 1rem"
    :show-close="false"
>
    <el-form
        v-if="addressType === 'address'"
        label-position="top"
        :model="addressForm"
        :rules="rules"
        ref="formRef"
        @submit.prevent="handleAddressSubmit"
    >
        <el-form-item label="Страна" prop="country">
            <el-input
                clearable
                v-model="addressForm.country"
            />
        </el-form-item>
        <div class="flex gap">
            <el-form-item style="width: 100%" label="Регион/Область" prop="region">
                <el-input
                    clearable
                    v-model="addressForm.region"
                />
            </el-form-item>
            <el-form-item style="width: 100%" label="Город" prop="city">
                <el-input
                    clearable
                    v-model="addressForm.city"
                />
            </el-form-item>
        </div>
        <div class="flex gap">
            <el-form-item style="width: 100%" label="Улица" prop="street">
                <el-input
                    clearable
                    v-model="addressForm.street"
                />
            </el-form-item>
            <el-form-item style="width: 100%" label="Дом/Строение" prop="house_number">
                <el-input
                    clearable
                    v-model="addressForm.house_number"
                />
            </el-form-item>
        </div>
        <div class="flex gap">
            <el-form-item style="width: 100%" label="Квартира" prop="apartment">
                <el-input
                    clearable
                    v-model="addressForm.apartment"
                />
            </el-form-item>
            <el-form-item style="width: 100%" label="Почтовый индекс" prop="zip_code">
                <el-input
                    clearable
                    v-model="addressForm.zip_code"
                />
            </el-form-item>
        </div>
        <el-form-item style="margin-bottom: 0">
            <div class="flex gap" style="width: 100%">
                <el-button @click="cancel">Отмена</el-button>
                <el-button
                    type="primary"
                    :loading="loading"
                    native-type="submit"
                >Добавить адрес</el-button>
            </div>
        </el-form-item>
    </el-form>
    <div v-else class="pickups gap">
        <div class="pickups-list">
            <article
                class="pickup-card"
                v-for="pickup in pickups"
                :key="pickup.id"
            >
                <div class="flex col gap" style="align-items: start">
                    <h1>{{ pickup.name }}</h1>
                    <div>{{ `${pickup.city} - ${pickup.address}` }}</div>
                </div>
                <div class="flex col" style="align-items: start">
                    <el-button @click="addPickup(pickup.id)">Добавить</el-button>
                </div>
            </article>
        </div>
        <div ref="sentinel" style="height:1px"></div>
        <div class="flex col" style="width: 100%">
            <el-button @click="cancel" >Закрыть</el-button>
        </div>
    </div>
</el-dialog>
</template>
<style scoped>
.pickups {
    display: flex;
    flex-direction: column;
}
.pickups-list {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    max-height: 300px;
    overflow-y: scroll;
}
.pickup-card {
    display: flex;
    padding: 1rem;
    border: var(--el-border-color) 1px solid;
    border-radius: .5rem;
    gap: 1rem;
    justify-content: space-between;
}
.pickup-card h1 {
    margin: 0;
    font-size: 1.5rem;
}
</style>