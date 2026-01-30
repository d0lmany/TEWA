<script setup lang="ts">
import { OrderStatus, type Order, type OrderLocation } from '@/ts/entities/Order';
import type Services from '@/ts/types/Services';
import { ElMessage } from 'element-plus';
import { computed, inject, onMounted, reactive, ref } from 'vue';
import OrderCard from '@/components/cards/OrderCard.vue';
import type { UI } from '@/ts/types/Provides';

const OrderService = (inject('services') as Services).order;
const orders = reactive<Order[]>([]);
const loading = ref(false);
const currencyFormatter = (inject('ui') as UI).currencyFormatter;
const dateFormatter = (inject('ui') as UI).dateFormatter;
const currentOrder = reactive({
    visible: false,
    order: {} as Order,
})
const preparedRules = reactive({
    status: null,
    hidden: false,
    sort: 'updated_at',
    actual: true,
});

const loadOrders = async () => {
    try {
        loading.value = true;
        const response = await OrderService.index();

        if (response.success && response.data) {
            orders.push(...response.data);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось загрузить заказы');
    } finally {
        loading.value = false;
    }
}
const getTotalByStatus = (status: OrderStatus): number => {
    return orders.reduce((sum, order) => 
        order.status === status ? sum + order.total : sum, 0);
}
const getTotalExcludingStatus = (excludeStatus: OrderStatus): number => {
    return orders.reduce((sum, order) => 
        order.status === excludeStatus ? sum : sum + order.total, 0);
}
const getRedemptionPercentage = (): string => {
    const completedTotal = getTotalByStatus(OrderStatus.Completed);
    const nonCancelledTotal = getTotalExcludingStatus(OrderStatus.Cancelled);
    
    if (nonCancelledTotal === 0) return '0';
    
    const percentage = (completedTotal / nonCancelledTotal) * 100;
    return percentage.toFixed(0);
}
const showOrder = (order: Order) => {
    currentOrder.order = order;
    currentOrder.visible = true;
}

const preparedOrders = computed(() => orders.filter(order => {
    if (order.status !== (preparedRules.status ?? order.status)) return false;

    if (order.is_hidden && !preparedRules.hidden) return false;

    const isArchived = (order.status === OrderStatus.Cancelled || order.status === OrderStatus.Completed);

    if (preparedRules.actual === false) {
        if (!isArchived) return false;
    } else {
        if (isArchived) return false;
    }

    return true;
}).toSorted((a, b) => {
    switch (preparedRules.sort) {
        case 'updated_at':
            return new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime();
        case 'created_at':
            return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
        case 'count':
            return b.items.length - a.items.length;
        case 'total':
        default:
            return b.total - a.total;
    }
}))
const localizeLocationType = (location_type: string) => {
    switch(location_type) {
        case 'warehouse':
            return 'Склад';
        case 'pickup':
            return 'ПВЗ';
        case 'address':
            return 'Точка доставки';
        default:
            return 'Неизвестная локация';
    }
}

onMounted(() => {
    loadOrders();
})
</script>
<template>
<div class="section">
    <h2 class="section-header">
        <div class="flex">
            <span>Заказы</span>
            <div class="soft" v-if="orders.length">
                <el-tag>
                    Потраченная сумма: {{ getTotalByStatus(OrderStatus.Completed) }}
                </el-tag>
                <el-tag>
                    Процент выкупа: {{ getRedemptionPercentage() }}%
                </el-tag>
                <el-tag>
                    Количество заказов: {{ orders.length }}
                </el-tag>
            </div>
        </div>
    </h2>
    <div class="options">
        <el-text size="large">Опции:</el-text>
        <el-select placeholder="Фильтрация по статусу" v-model="preparedRules.status" clearable style="width: 15rem">
            <el-option :value="OrderStatus.Pending" label="Ожидает оплаты"/>
            <el-option :value="OrderStatus.Paid" label="Оплачен"/>
            <el-option :value="OrderStatus.Processing" label="Ждёт сборки"/>
            <el-option :value="OrderStatus.Shipped" label="Передан в доставку"/>
            <el-option :value="OrderStatus.Delivered" label="Доставлен"/>
            <el-option :value="OrderStatus.Cancelled" label="Отменён"/>
            <el-option :value="OrderStatus.Completed" label="Получен"/>
        </el-select>
        <div class="divider"></div>
        <el-text>Показать скрытые</el-text>
        <el-radio-group v-model="preparedRules.hidden">
            <el-radio :value="true" label="Да"/>
            <el-radio :value="false" label="Нет"/>
        </el-radio-group>
        <div class="divider"></div>
        <el-text>Фильтрация</el-text>
        <el-select v-model="preparedRules.sort" style="width: 15rem">
            <el-option value="updated_at" label="По дате изменения"/>
            <el-option value="created_at" label="По дате оформления"/>
            <el-option value="total" label="По итоговой сумме"/>
            <el-option value="count" label="По количеству позиций"/>
        </el-select>
        <el-radio-group v-model="preparedRules.actual" style="margin-left: auto">
            <el-radio-button :value="true" label="Актуальные"/>
            <el-radio-button :value="false" label="Завершённые"/>
        </el-radio-group>
    </div>
    <section>
        <div class="orders" v-if="preparedOrders.length">
            <order-card
                v-for="order in preparedOrders"
                @click="showOrder(order)"
                :key="order.id"
                :order="order"
            />
        </div>
        <el-empty
            :description="loading ? 'Заказы загружаются...' : 'Заказов нет'"
            v-else
        />
    </section>
    <el-dialog
        :title="`Заказ №${currentOrder.order.id}`"
        v-model="currentOrder.visible"
        :show-close="false"
        center
    >
        <div class="data">
            <el-text size="large" class="span header">Информация:</el-text>
            <el-text size="large">Статус: <b>{{ OrderService.statuses[currentOrder.order.status] ?? 'N/A' }}</b></el-text>
            <el-text size="large">Цена: <b>{{ currencyFormatter.format(currentOrder.order.total) }}</b></el-text>
            <el-text size="large">Скрытый: <b>{{ currentOrder.order.is_hidden ? 'Да' : 'Нет' }}</b></el-text>
            <el-text size="large">
                Оформлен: <b>{{ dateFormatter.format(new Date(currentOrder.order.created_at)) }}</b>
            </el-text>
            <el-text size="large" v-if="currentOrder.order.status === OrderStatus.Completed || currentOrder.order.status === OrderStatus.Cancelled">
                Завершён: <b>{{ dateFormatter.format(new Date(currentOrder.order.updated_at)) }}</b>
            </el-text>
            <el-text size="large" v-else-if="currentOrder.order.updated_at !== currentOrder.order.created_at">
                Изменён: <b>{{ dateFormatter.format(new Date(currentOrder.order.updated_at)) }}</b>
            </el-text>
            <el-text size="large" class="span header">Содержимое:</el-text>
            <div class="span items">
                <div
                    class="item-card"
                    v-for="item in currentOrder.order.items"
                    @click="$router.push({ name: 'Product', params: { id: item.product_id, slug: item.product?.name } })"
                >
                    <el-image
                        :src="item.product?.photo"
                        fit="cover"
                        lazy
                    />
                    <el-text size="large"><b>{{ currencyFormatter.format(item.total) }}</b></el-text>
                    <el-text size="large">{{ item.product?.name + (item.quantity > 1 ? ` (${item.product?.quantity})` : '') }}</el-text>
                </div>
            </div>
            <el-text size="large" class="span header">Местоположение:</el-text>
            <div class="span items">
                <div class="location-card" v-for="location in currentOrder.order.locations">
                    <div class="flex">
                        <el-text size="large"><b>
                            {{ localizeLocationType(location.location_type) }}
                        </b></el-text>
                        <el-tag v-if="location.is_current">Сейчас здесь</el-tag>
                    </div>
                    <p v-if="location.notes">{{ location.notes }}</p>
                    <el-text v-if="location.arrived_at">Прибыл: {{ dateFormatter.format(new Date(location.arrived_at)) }}</el-text>
                    <el-text v-if="location.left_at">Отбыл: {{ dateFormatter.format(new Date(location.left_at)) }}</el-text>
                </div>
            </div>
        </div>
    </el-dialog>
</div>
</template>
<style scoped>
.orders {
    gap: .5rem;
    display: flex;
    flex-direction: column;
    overflow-y: scroll;
    max-height: 65vh;
}
.soft .el-tag {
    font-weight: 400;
    font-size: .8rem;
    border-radius: .5rem;
}
.options {
    margin-bottom: 1rem;
    align-items: center;
    display: flex;
    gap: .5rem;
}
.divider {
    border-left: 1px solid var(--el-border-color);
    align-self: stretch;
}
.options .el-radio {
    margin-right: .5rem;
}
.data {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}
.span {
    grid-column: 1 / 4;
}
.items {
    display: flex;
    gap: 1rem;
    max-width: 100%;
    overflow-x: scroll;
}
.item-card {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    width: 150px;
    cursor: pointer;
    flex-shrink: 0;
}
.item-card:hover > .el-text {
    color: var(--el-color-primary);
}
.item-card > .el-text {
    margin-right: auto;
    will-change: color;
    transition: var(--el-transition-duration);
}
.item-card > .el-image {
    border-radius: .5rem;
}
.location-card {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    flex-shrink: 0;
    align-items: left;
    border: var(--el-border-color) 1px solid;
    border-radius: .25rem;
    padding: .5rem;
    user-select: none;
}
.location-card .el-text {
    margin-right: auto;
}
.location-card p {
    margin: 0;
}
.span.header {
    font-size: 1.25rem;
    margin-top: 1rem;
}
.span.header:first-child {
    margin-top: 0;
}
</style>