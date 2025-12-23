<script setup lang="ts">
import type { Address } from '@/ts/entities/Addresses';
import { TakeawayBox, MapLocation } from '@element-plus/icons-vue';

const { address } = defineProps<{
    address: Address
}>();
const emit = defineEmits(['makeDefault', 'delete']);
</script>
<template>
<el-card
    shadow="hover"
    class="card"
    role="article"
>
    <div class="flex">
        <div class="flex col gap" style="align-items: start">
            <h3>
                <span v-if="address.address">{{ address.address }}<span class="soft"> — <el-icon size="1.25cap"><map-location/></el-icon> Личный адрес</span></span>
                <span v-else>{{ address?.pickup?.name }}<span class="soft"> — <el-icon size="1.25cap"><takeaway-box/></el-icon> ПВЗ</span></span>
            </h3>
            <span v-if="!address.address">{{ address?.pickup?.address }}</span>
        </div>
        <div class="flex col gap" style="align-items: end">
            <el-text
                v-if="address.is_default"
                size="large"
            >Основной адрес</el-text>
            <el-button
                @click="emit('makeDefault')"
                v-else
            >Сделать основным</el-button>
            <el-button
                @click="emit('delete')"
                type="danger"
                plain
            >Удалить</el-button>
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
    padding: 1rem;
}
h3 {
    transition: .2s;
    will-change: color;
    margin: 0;
}
.card:hover h3 {
    color: var(--el-color-primary);
}
.soft {
    font-size: 1rem;
    font-weight: 400;
    color: var(--el-text-color-secondary);
    display: inline-flex;
    gap: .5rem;
    align-items: center;
}
</style>