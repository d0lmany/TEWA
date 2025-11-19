<script setup lang="ts">
import { /*Top, */User/*, Setting*/ } from '@element-plus/icons-vue';
//import { View } from '@/types/view';
import { useUserStore } from '@/stores/userStore';
import { storeToRefs } from 'pinia';
import { computed, inject/*, ref*/ } from 'vue';
/*
import DefaultSection from '@/components/sections/DefaultSection.vue';
import SettingsSection from '@/components/sections/SettingsSection.vue';

const currentView = ref(View.Default);
const views = [
    { type: View.Default, name: 'Главная', icon: User },
    { type: View.Settings, name: 'Настройки', icon: Setting },
];
*/
const userStore = useUserStore();
const { user } = storeToRefs(userStore);
const storageURL = inject('storageURL');

const picturePath = computed(() => `${storageURL}/${user?.value?.picture}`);
const age = computed(() => {
    const today = new Date();
    const birthday = new Date(user?.value?.birthday);
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
                    :src="picturePath"
                    style="width: 200px; height: 200px;"
                ><el-icon :size="48"><User/></el-icon></el-avatar>
                <figcaption>{{ user.name }}<br>{{ age }}</figcaption>
            </figure>
            <!--el-button-group class="buttons">
                <el-button
                    v-for="view in views"
                    @click="currentView = view.type"
                    :key="view.type"
                    :type="currentView === view.type ? 'primary' : ''"
                >
                    <el-icon class="el-icon--left">
                        <component :is="view.icon"/>
                    </el-icon>
                    {{ view.name }}
                </el-button>
            </!el-button-group-->
        </aside>
        <!--main>
            <article class="section-header">
                <h3 align="center">Здесь пока ничего нет.</h3>
                <--default-section v-if="currentView === View.Default" />
                <settings-section v-if="currentView === View.Settings" /->
            </article>
            <el-backtop>
                <el-icon :size="24"><Top/></el-icon>
            </el-backtop>
        </main-->
    </div>
</template>
<style scoped>
.container {
    display: grid;
    grid-template-columns:/* 300px */1fr;
    margin-bottom: 1rem;
    margin-inline: 1rem;
    gap: 1rem;
}
aside, article {
    background: var(--el-color-primary-light-9);
    border-radius: 1rem;
    padding: 1rem;
}
.buttons {
    display: flex;
    flex-direction: column;
    border-radius: .5rem;
}
.buttons .el-button:first-child {
    border-radius: .5rem .5rem 0 0 !important;
}
.buttons .el-button:last-child {
    border-radius: 0 0 .5rem .5rem !important;
}
.buttons .el-button {
    font-size: 1rem;
    padding-block: 1.1rem;
    width: 100%;
}
figure {
    margin: 1rem auto 0;
    width: fit-content;
}
figcaption {
    font-size: 1.25rem;
    text-align: center;
    padding-top: 1rem;
}
</style>