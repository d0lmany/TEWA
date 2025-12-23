<script setup lang="ts">
import { Plus, Minus, Delete, Star, StarFilled } from '@element-plus/icons-vue';
import { type CartProduct } from '@/ts/entities/Items';
import type { UI } from '@/ts/types/Provides';
import type Services from '@/ts/types/Services';
import { inject } from 'vue';

const { item, allowCheck } = defineProps<{
    item: CartProduct,
    allowCheck?: boolean
}>();
const formatter = (inject('ui') as UI).currencyFormatter;
const emit = defineEmits<{
    increase: [void],
    decrease: [void],
    delete: [void],
    toggleFavorite: [void]
}>();
const i18n = (inject('services') as Services).i18n;
const attrs: Record<string, string> = {};

const collectedAttrs = () => {
    if (!item.product_attributes || !item.product.attributes) return;
    let impactPrice = 0;
    item.product_attributes.forEach(attrId => {
        for (const [type, attributes] of Object.entries(item.product.attributes!)) {
            const attribute = attributes.find(attr => attr.id === attrId);
            if (attribute) {
                attrs[type] = attribute.attr_value;
                impactPrice += Number(attribute.price);
                break;
            }
        }
    });
    item.product.price.total = item.product.price.final_price + impactPrice;
}
const translate = (phrase: string) => i18n.translate(phrase);

collectedAttrs();
</script>
<template>
<el-card
    shadow="hover"
    role="article"
    class="card"
    body-class="cart-product-card-body"
>
    <el-checkbox
        v-if="allowCheck"
        v-model="item.checked"
        :value="item.id"
        class="cb"
    />
    <el-image :src="item.product.photo" fit="contain" lazy class="img"/>
    <div class="text">
        <h3
            @click="$router.push({name: 'Product', params: { id: item.product.id, slug: item.product.name }})"
            style="cursor: pointer"
        >{{ item.product.name }}</h3>
        <div class="flex gap">
            <el-text size="large">{{ formatter.format(item.product.price.final_price) }}</el-text>
            <el-text size="large">{{ `-${item.product.price.discount}%` }}</el-text>
        </div>
        <div class="flex gap">
            <el-tag
                style="width: min-content"
                v-for="(value, key) in attrs"
            >{{ `${translate(key)}: ${translate(value)}` }}</el-tag>
        </div>
    </div>
    <div class="text">
        <h3 style="text-align: center">{{ formatter.format(item.quantity * (item.product.price.total || item.product.price.final_price)) }}</h3>
        <div class="count-container" v-if="allowCheck">
            <el-button
                circle
                :icon="Minus"
                @click="emit('decrease')"
                aria-label="Уменьшить количество на 1"
            />
            <el-button text role="text" class="count">
                {{ item.quantity }}
            </el-button>
            <el-button
                circle
                :icon="Plus"
                @click="emit('increase')"
                aria-label="Увеличить количество на 1"
            />
        </div>
        <div class="flex">
            <el-popover placement="top">
                <div style="text-align: center; user-select: none">{{ item.isFavorite ? 'Удалить из избранного' : 'Добавить в избранное' }}</div>
                <template #reference>
                    <el-button
                        circle
                        plain
                        :icon="item.isFavorite ? StarFilled : Star"
                        @click="emit('toggleFavorite')"
                        :aria-label="item.isFavorite ? 'Удалить из избранного' : 'Добавить в избранное'"
                        type="primary"
                    />
                </template>
            </el-popover>
            <el-popover placement="top">
                <div style="text-align: center; user-select: none">Удалить из корзины</div>
                <template #reference>
                    <el-button
                        circle
                        plain
                        :icon="Delete"
                        @click="emit('delete')"
                        aria-label="Удалить из корзины"
                        type="danger"
                    />
                </template>
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
.cb {
    height: min-content;
    position: absolute;
}
.cb:deep(.el-checkbox__label) {
    display: none;
}
.cb:deep(.el-checkbox__inner) {
    background: var(--el-bg-color-overlay);
    border-radius: .35rem;
    width: 1.75rem;
    height: 1.75rem;
}
.count-container {
    min-width: 35%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: calc(var(--el-border-radius-round) - 2px);
    border: 1px solid var(--el-border-color);
    transition: all var(--el-transition-duration);
}
.count-container:hover {
    border-color: var(--el-color-primary);
}
.count-container .count {
    pointer-events: none;
}
</style>