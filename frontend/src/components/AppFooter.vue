<script setup lang="ts">
import type { UI } from '@/ts/types/Provides';
import { Moon, Sunny } from '@element-plus/icons-vue';
import { inject } from 'vue';

const { visible } = defineProps<{
    visible?: Boolean
}>();

const ui = inject('ui') as UI;
</script>
<template>
<footer :style="{opacity: visible ? 1 : 0}">
    <div class="flex gap">
        <router-link class="logo" :to="{name: 'Home'}">
            TEWA
        </router-link>
        <el-button
            @click="ui.darkTheme = !ui.darkTheme"
            :icon="ui.darkTheme ? Moon : Sunny"
            circle
        />
    </div>
    <div class="links">
        <div @click="ui.loginVisible = true">Вход</div>
        <div @click="$router.push({name: 'AboutTEWA'})">О проекте</div>
        <div @click="ui.regVisible = true">Регистрация</div>
        <!--div>О маркетплейсе</div-->
        <!--div>Стать продавцом</div-->
        <div><a
            href="https://d0lmany.vercel.app/"
            rel="noopener noreferrer nofollow">
            dev: d0lmany
        </a></div>
    </div>
</footer>
</template>
<style scoped>
footer {
    padding: .5rem 2rem;
    background: var(--el-color-primary-light-8);
    
    gap: 1rem;
    display: flex;
    justify-content: space-between;

    position: fixed;
    width: 100%;
    box-sizing: border-box;
    bottom: 0;
    transition: ease-out .25s;
}
footer:hover {
    opacity: 1 !important;
}
.links {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    color: var(--el-text-color-regular);
}
.links > div {
    user-select: none;
    width: fit-content;
    cursor: pointer;
    font-weight: 600;
    transition: .1s ease-out;
}
.links > div:hover, a:hover {
    color: var(--el-color-primary) !important;
}
.links > div:nth-child(even) {
    justify-self: end;
}
.links a {
    transition: .1s ease-out;
    text-decoration: none;
    color: var(--el-text-color-regular);
}
.logo {
    font-weight: bold;
    color: var(--el-color-primary) !important;
    font-size: 2rem;
    text-decoration: none;
    display: flex;
    align-items: center;
}
</style>