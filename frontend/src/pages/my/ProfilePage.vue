<script setup lang="ts">
import { Top, User, Location, Edit, Close } from '@element-plus/icons-vue';
import { View } from '@/ts/types/View';
import { useUserStore } from '@/stores/userStore';
import { computed, reactive, ref, type Component } from 'vue';
import ChangeProfileModal from '@/components/modals/ChangeProfileModal.vue';
import ChangePasswordModal from '@/components/modals/ChangePasswordModal.vue';
import LogoutModal from '@/components/modals/LogoutModal.vue';

import AddressesSection from '@/components/sections/AddressesSection.vue';

const currentView = ref<View>(View.Addresses);
const views = [
    { type: View.Addresses, name: 'Адреса и ПВЗ', icon: Location },
];
const sections: Record<View, Component> = {
    [View.Addresses]: AddressesSection,
};

const userStore = useUserStore();
const visibilities = reactive({
    changeProfile: false,
    changePassword: false,
    wannaLogout: false,
})

const age = computed(() => {
    const today = new Date();
    const birthday = new Date(userStore.user?.birthday);
    let ageNum = today.getFullYear() - birthday.getFullYear();
    const monthDiff = today.getMonth() - birthday.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
        ageNum--;
    }

    const lastDigit = ageNum % 10;
    
    let ageSuffix = '';
    if (ageNum >= 11 && ageNum <= 14) {
        ageSuffix = 'лет';
    } else {
        switch (lastDigit) {
            case 1: ageSuffix = 'год'; break;
            case 2: 
            case 3: 
            case 4: ageSuffix = 'года'; break;
            default: ageSuffix = 'лет';
        }
    }

    return `${ageNum} ${ageSuffix}`;
});
</script>
<template>
<div class="container">
    <aside>
        <h1 class="section-header">Профиль</h1>
        <figure>
            <el-avatar
                size="large"
                shape="square"
                :src="userStore.user.picture"
                style="width: 200px; height: 200px;"
            ><el-icon :size="48"><User/></el-icon></el-avatar>
            <figcaption>{{ userStore.user.name }}, {{ age }}</figcaption>
        </figure>
        <el-button-group class="buttons">
            <el-button text
                v-for="view in views"
                @click="currentView = view.type"
                :key="view.type"
                :type="currentView === view.type ? 'primary' : ''"
            >
                <el-icon class="el-icon--left" :size="20">
                    <component :is="view.icon"/>
                </el-icon>
                {{ view.name }}
            </el-button>
            <el-button
                text
                @click="visibilities.changeProfile = true"
            >
                <el-icon class="el-icon--left" :size="20"><Edit/></el-icon>
                Изменить данные
            </el-button>
            <el-button
                text
                type="danger"
                @click="visibilities.wannaLogout = true"
            >
                <el-icon class="el-icon--left" :size="20"><Close/></el-icon>
                Выйти из аккаунта
            </el-button>
        </el-button-group>
    </aside>
    <main>
        <article>
            <component :is="sections[currentView]"/>
        </article>
        <el-backtop>
            <el-icon :size="24"><Top/></el-icon>
        </el-backtop>
    </main>
    <change-profile-modal
        v-model="visibilities.changeProfile"
        @open-change-password="visibilities.changePassword = true"
    />
    <change-password-modal v-model="visibilities.changePassword"/>
    <logout-modal v-model="visibilities.wannaLogout"/>
</div>
</template>
<style scoped>
.container {
    display: grid;
    grid-template-columns: 280px 1fr;
    margin-bottom: 1rem;
    margin-inline: 1rem;
    gap: 1rem;
}
aside, article {
    background: var(--el-color-primary-light-9);
    border-radius: 1rem;
    padding: 1rem;
    height: min-content;
}
.buttons {
    display: flex;
    flex-direction: column;
    border-radius: .5rem;
}
.buttons .el-button {
    font-size: 1rem;
    padding-block: 1.1rem;
    width: 100%;
    justify-content: start;
}
.buttons .el-button:not(:first-child) {
    border-top: solid 2px var(--el-border-color);
}
figure {
    margin: 1rem auto;
    width: fit-content;
}
figcaption {
    font-size: 1.25rem;
    text-align: center;
    padding-top: 1rem;
}
</style>