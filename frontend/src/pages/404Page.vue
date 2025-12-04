<template>
<main>
   <h1 class="section-header">404</h1>
   <h2 class="section-header">Страница не найдена :(</h2>
   <router-link :to="{name: 'Home'}">
      <el-link style="font-size: 1rem;">Вернуться на главную?</el-link>
   </router-link>
</main>
</template>
<style scoped>
main {
   margin: 1rem;
   padding: 1rem;
   display: flex;
   border-radius: var(--el-border-radius-base);
   background: var(--el-bg-color-overlay);
   flex-direction: column;
   gap: 1rem;
   align-items: center;
   min-height: 70vh;
   justify-content: center;
}
h1 {
   font-size: 3rem;
}
</style>