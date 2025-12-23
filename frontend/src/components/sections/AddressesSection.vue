<script setup lang="ts">
import AddressCard from '@/components/cards/AddressCard.vue';
import type { Address } from '@/ts/entities/Addresses';
import type Services from '@/ts/types/Services';
import { ElMessage } from 'element-plus';
import { inject, onMounted, reactive } from 'vue';
import AddAddressOrPickupModal from '@/components/modals/AddAddressOrPickupModal.vue';
import { TakeawayBox, MapLocation } from '@element-plus/icons-vue';

const AddressService = (inject('services') as Services).address;
const addresses = reactive<Address[]>([]);
const addressModal = reactive<{
    visible: boolean,
    type: 'address' | 'pickup'
}>({
    visible: false,
    type: 'address'
});

const loadAddresses = async () => {
    try {
        const response = await AddressService.index();

        if (response.success && response.data?.data) {
            addresses.push(...response.data.data);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось загрузить сохранённые адреса');
    }
}
const storeAddress = (address: Address) => {
    addresses.push(address);
}
const updateAddress = async (id: number) => {
    try {
        const response = await AddressService.update(id, { is_default: true });

        if (response.success) {
            ElMessage.success('Новый основной адрес установлен');
            
            const oldDefaultIndex = addresses.findIndex(a => a.is_default);
            const newDefaultIndex = addresses.findIndex(a => a.id === id);
            
            if (oldDefaultIndex !== -1 && addresses[oldDefaultIndex]) {
                addresses[oldDefaultIndex].is_default = false;
            }
            
            if (newDefaultIndex !== -1 && addresses[newDefaultIndex]) {
                addresses[newDefaultIndex].is_default = true;
            }
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось обновить адрес');
    }
}
const deleteAddress = async (id: number) => {
    try {
        const response = await AddressService.destroy(id);

        if (response.success && response.data.deleted) {
            const index = addresses.findIndex(addr => addr.id === id);
            if (index !== -1) {
                addresses.splice(index, 1);
                ElMessage.success('Адрес удалён');
                return;
            }
            return;
        }
        throw new Error(response.message);
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось удалить адрес');
    }
}
const openAddr = () => {
    addressModal.type = 'address';
    addressModal.visible = true;
}
const openPickup = () => {
    addressModal.type = 'pickup';
    addressModal.visible = true;
}

onMounted(() => {
    loadAddresses();
})
</script>
<template>
<div class="section">
    <h2 class="section-header">Адреса и ПВЗ</h2>
    <section>
        <div class="addresses">
            <address-card
                v-for="address in addresses"
                :key="address.id"
                :address="address"
                :class="{ 'supreme': address.is_default }"
                @make-default="updateAddress(address.id)"
                @delete="deleteAddress(address.id)"
            />
        </div>
        <div class="flex gap" style="margin-top: 1rem">
            <el-button @click="openAddr" size="large">
                <el-icon :size="18" class="el-icon--left"><takeaway-box/></el-icon>
                Добавить адрес
            </el-button>
            <el-button @click="openPickup" size="large">
                <el-icon :size="18" class="el-icon--left"><map-location/></el-icon>
                Добавить ПВЗ
            </el-button>
        </div>
        <add-address-or-pickup-modal
            v-model="addressModal.visible"
            :address-type="addressModal.type"
            @add-address="storeAddress"
        />
    </section>
</div>
</template>
<style scoped>
.addresses {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    max-height: 500px;
    overflow-y: scroll;
}
.addresses > div {
    order: 2;
}
.supreme {
    order: 1 !important;
}
</style>