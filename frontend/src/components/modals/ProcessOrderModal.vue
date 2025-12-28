<script setup lang="ts">
import type { CartProduct } from '@/ts/entities/Items';
import { useUserStore } from '@/stores/userStore';
import type Services from '@/ts/types/Services';
import { inject, ref } from 'vue';

const visible = defineModel<boolean>();
const userStore = useUserStore();
const { items } = defineProps<{
    items: CartProduct[]
}>();

</script>
<template>
<el-dialog
    title="Оформить заказ"
    v-model="visible"
    center
    align-center
    width="70%"
    :show-close="false"
>
    {{ items }}
</el-dialog>
</template>