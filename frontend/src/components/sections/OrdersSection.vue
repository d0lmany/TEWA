<script setup lang="ts">
import { OrderStatus, type Order } from '@/ts/entities/Order';
import type Services from '@/ts/types/Services';
import { ElMessage } from 'element-plus';
import { computed, inject, onMounted, reactive, ref } from 'vue';
import OrderCard from '@/components/cards/OrderCard.vue';

const OrderService = (inject('services') as Services).order;
const orders = reactive<Order[]>([]);
const loading = ref(false);
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

const preparedOrders = computed(() => orders.filter(order => {
    if (order.status !== (preparedRules.status ?? order.status)) return false;

    if (order.is_hidden && !preparedRules.hidden) return false;

    if ((order.status === OrderStatus.Cancelled || order.status === OrderStatus.Completed)
        && preparedRules.actual) return false;

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
                    :key="order.id"
                    :order="order"
                />
            </div>
            <el-empty
                :description="loading ? 'Заказы загружаются...' : 'Заказов нет'"
                v-else
            />
        </section>
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
</style>