<script setup lang="ts">
import { OrderStatus, type Order } from '@/ts/entities/Order';
import type { UI } from '@/ts/types/Provides';
import { inject } from 'vue';
import { ElMessage } from 'element-plus';

const { order } = defineProps<{
    order: Order
}>();
const dateFormatter = (inject('ui') as UI).dateFormatter;
const currencyFormatter = (inject('ui') as UI).currencyFormatter;

const getItems = () => order.items.map(item => item.product);
const makeHeader = () => {
    const statuses = {
        [OrderStatus.Pending]: 'Ожидает оплаты',
        [OrderStatus.Paid]: 'Оплачен',
        [OrderStatus.Processing]: 'Собирается',
        [OrderStatus.Shipped]: 'Передан в доставку',
        [OrderStatus.Delivered]: 'Доставлен',
        [OrderStatus.Cancelled]: 'Отменён',
        [OrderStatus.Completed]: 'Получен',
    };
    return `${statuses[order.status] ?? 'Заказ'} от ${dateFormatter.format(new Date(order.last_status_change.created_at))}`;
}
const copy = async (target: string) => {
    try {
        await navigator.clipboard.writeText(target);
        ElMessage.success(`ID заказа скопирован!`);
    } catch (e) {
        ElMessage.error(`Не удалось скопировать "${target}": ${e instanceof Error ? e.message : 'Неизвестная ошибка'}`);
    }
}
const getCurrentLocation = () => {
    const places = {
        warehouse: 'на складе',
        pickup: `в ПВЗ (${order.current_location?.location_id})`,
        address: `по адресу ${order.current_location?.location_id}`,
        idk: 'в пути',
    };
    return `Сейчас находится ${places[order.current_location?.location_type ?? 'idk']}`;
}
</script>
<template>
    <el-card
        shadow="hover"
        role="article"
        class="card"
    >
        <div class="flex">
            <div class="text">
                <h3 class="section-header" :style="{opacity: order.status === OrderStatus.Cancelled ? 0.5 : 1}">{{ makeHeader() }}</h3>
                <div class="flex low gap">
                    <el-text>{{ getCurrentLocation() }}</el-text>
                    <el-button
                        round
                        size="small"
                        @click="copy(order.id.toString())"
                    >
                        ID: {{ order.id }}
                    </el-button>
                </div>
            </div>
            <div class="images">
                <el-popover
                    v-for="product in getItems()"
                    :key="product?.id"
                    :width="185"
                >
                    <template #reference>
                    <el-image
                        @click="$router.push({name: 'Product', params: { id: product?.id, slug: product?.name }})"
                        :src="product?.photo"
                        fit="cover"
                        lazy
                    />
                    </template>
                    <div style="text-align: center; font-size: 1rem; line-height: 1.75rem">
                        <b>{{ product?.name }}</b>,
                        {{ currencyFormatter.format(product?.price.final_price || 0) }}
                    </div>
                </el-popover>
            </div>
        </div>
    </el-card>
</template>
<style scoped>
.card {
    border-radius: .5rem;
    will-change: box-shadow;
    flex-shrink: 0;
}
.card:deep(.el-card__body) {
    padding: .75rem;
}
.card:hover h3 {
    color: var(--el-color-primary);
}
h3 {
    font-size: 1.25rem;
    transition: .2s ease-out;
}
.images {
    display: flex;
    gap: .5rem;
    overflow-x: hidden;
    max-width: 50%;
}
.images > * {
    width: 75px;
    height: 75px;
    flex-shrink: 0;
    border-radius: .25rem;
    cursor: pointer;
}
</style>