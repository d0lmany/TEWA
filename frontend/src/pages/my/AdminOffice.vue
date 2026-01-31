<script setup lang="ts">
import HomeAdmin from '@/components/sections/HomeAdmin.vue';
import { AdminView } from '@/ts/types/View';
import { HomeFilled, House } from '@element-plus/icons-vue';
import { ref, type Component } from 'vue';

const currentView = ref<AdminView>(AdminView.Home);
const views = [
    { type: AdminView.Home, name: 'Главная', icon: House }
];
const sections: Record<AdminView, Component> = {
    [AdminView.Home]: HomeAdmin,
};
</script>
<template>
<div class="container">
    <aside>
        <h1 class="section-header" style="padding-bottom: 1rem">Кабинет администратора</h1>
        <el-button-group class="buttons">
            <el-button text
                v-for="view in views"
                :key="view.type"
                :type="currentView === view.type ? 'primary' : ''"
            >
                <el-icon class="el-icon--left" :size="20">
                    <component :is="view.icon"/>
                </el-icon>
                {{ view.name }}
            </el-button>
        </el-button-group>
    </aside>
    <main>
        <article>
            <component :is="sections[currentView]"/>
        </article>
    </main>
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
</style>