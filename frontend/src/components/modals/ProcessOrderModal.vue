<script setup lang="ts">
import type { CartProduct } from '@/ts/entities/Items';
import { useUserStore } from '@/stores/userStore';
import type Services from '@/ts/types/Services';
import { computed, inject, onMounted, reactive, ref, watch } from 'vue';
import type { Address } from '@/ts/entities/Addresses';
import { ElMessage } from 'element-plus';
import type { OrderRequest } from '@/ts/entities/Order';
import type { UI } from '@/ts/types/Provides';
import { InfoFilled, ShoppingBag } from '@element-plus/icons-vue';

const visible = defineModel<boolean>();
const userStore = useUserStore();
const { items } = defineProps<{
    items: CartProduct[]
}>();
const {
	address: AddressService,
	order: OrderService
} = inject('services') as Services;
const loading = ref(false);
const addresses = reactive<(Address & {checked?: boolean})[]>([]);
const order = reactive<OrderRequest>({
    cart_items: [],
    is_hidden: false,
});
const currencyFormatter = (inject('ui') as UI).currencyFormatter;

const loadAddresses = async () => {
    try {
        const response = await AddressService.index();

        if (response.success && response.data) {
            addresses.splice(0);
            addresses.push(...response.data);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось загрузить сохранённые адреса');
    }
}
const cancel = () => {
    order.cart_items = [];
    setCurrentAddress(-1, 0);
    order.destination_address_id = undefined;
    order.destination_pickup_id = undefined;
    order.is_hidden = false;
}
const submit = async () => {
    try {
        loading.value = true;
        
        const response = await OrderService.store(order);

        if (response.success) {
            ElMessage.success('Заказ оформлен!');
            order.cart_items.forEach(item => [
                userStore.removeCartItem(item)
            ]);
            visible.value = false;
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось оформить заказ');
    } finally {
        loading.value = false;
    }
}
const setCurrentAddress = (addressIndex: number, addressId: number) => {
    addresses.forEach(address => {
        address.checked = false;
    })
    if (addresses[addressIndex]) {
        addresses[addressIndex].checked = true;
        order.destination_address_id = addressId;
    }
}
const totalBasePrice = () => currencyFormatter.format(items.reduce((acc, item) => acc + item.product.price.base_price, 0))
const totalPrice = () => currencyFormatter.format(items.reduce((acc, item) => acc + (item.product.price.total || item.product.price.final_price) * item.quantity, 0))

const isDoneForOrder = computed(() => {
    if (!order.cart_items.length) return false
    if (!order.destination_address_id && !order.destination_pickup_id) return false
    return true
})

onMounted(() => {
    loadAddresses()
})

watch(
    () => visible.value,
    () => {
        if (visible.value) {
            order.cart_items = items.map(item => item.id || 0);
        }
    }
)
</script>
<template>
<el-dialog
    title="Оформление заказа"
    v-model="visible"
    center
    align-center
    width="70%"
    :show-close="false"
    @closed="cancel"
>
<div class="grid">
    <section>
        <h4 class="section-header">Информация о заказе</h4>
        <div class="flex low gap" style="max-width: 525px; overflow-x: scroll">
            <el-popover
                v-for="item in items"
                :key="item.product?.id"
                :width="185"
            >
                <template #reference>
                <el-image
                    @click="$router.push({name: 'Product', params: { id: item.product?.id, slug: item.product?.name }})"
                    :src="item.product?.photo"
                    fit="cover"
                    lazy
                />
                </template>
                <div style="text-align: center; font-size: 1rem; line-height: 1.75rem">
                    <b>{{ item.product?.name }}</b>,
                    {{ currencyFormatter.format(item.product?.price.final_price || 0) }}
                </div>
            </el-popover>
        </div>
    </section>
    <section class="aside">
        <el-button
            :loading="loading"
            type="success"
            size="large"
            @click="submit"
            :disabled="!isDoneForOrder"
            style="font-size: 1rem !important; width: 100%"
        >
            <el-icon class="el-icon--left" :size="18"><shopping-bag/></el-icon>
            Оформить заказ
        </el-button>
        <div class="flex">
            <el-text size="large">{{ `Товары (без скидки) (${items.length}):` }}</el-text>
            <el-text size="large">{{ totalBasePrice() }}</el-text>
        </div>
        <div class="flex">
            <el-text size="large">Итого:</el-text>
            <el-text size="large">{{ totalPrice() }}</el-text>
        </div>
        <div>
            <el-text size="large">Выбранный адрес:</el-text>
            <p style="font-size: 1rem; margin-bottom: 0">{{ addresses.find(address => address.id === order.destination_address_id)?.address ?? 'Не выбран' }}</p>
        </div>
        <div class="flex">
            <el-checkbox
                v-model="order.is_hidden"
                label="Скрыть из истории заказов"
                size="large"
            />
            <el-popover>
                <template #reference>
                <el-icon :size="18">
                    <info-filled/>
                </el-icon>
                </template>
                <div style="text-align: center">
                    Ваш заказ не будет виден в истории заказов другим пользователям
                </div>
            </el-popover>
        </div>
    </section>
	<section>
		<h4 class="section-header">Способ получения</h4>
        <div class="addresses">
            <div class="contents" v-if="addresses.length">
                <article
                    v-for="(address, index) in addresses"
                    :key="address.id"
                    :style="{ order: address.is_default ? 1 : 2 }"
                >
                    <el-text size="large">
                        {{ address.address }}
                    </el-text>
                    <div class="flex">
                        <el-button @click="setCurrentAddress(index, address.id)">Выбрать</el-button>
                        <el-text type="primary" v-if="address.checked">
                            Выбран
                        </el-text>
                        <el-text v-if="address.is_default">
                            Ваш основной адрес
                        </el-text>
                    </div>
                </article>
            </div>
            <el-empty
                :description="loading ? 'Адреса загружаются...' : 'Вы не сохраняли адреса, вернитесь в личный профиль чтобы сохранить адрес'"
                v-else
            />
        </div>
	</section>
</div>
    <template #footer>
        <el-button @click="visible = false">Отменить</el-button>
    </template>
</el-dialog>
</template>
<style scoped>
.section-header {
	font-size: 1.25rem;
    margin-bottom: .75rem !important;
}
section {
	border: 1px solid var(--el-border-color);
	padding: .75rem;
	border-radius: .5rem;
}
.grid {
    display: grid;
    grid-template-rows: auto auto;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.addresses {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    max-height: 300px;
    overflow-y: scroll;
}
article {
    border: 1px solid var(--el-border-color);
	border-radius: .25rem;
	padding: .75rem;
    order: 2;
}
article > .el-text {
    display: block;
    text-align: justify;
    margin-bottom: 1rem;
}
.el-image {
    width: 80px;
    height: 100px;
    flex-shrink: 0;
    border-radius: .25rem;
    cursor: pointer;
}
.aside {
    grid-row: 1 / span 2;
    grid-column: 2;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
</style>