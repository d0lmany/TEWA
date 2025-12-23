<template>
<main>
    <h1>О проекте</h1>
    <article>
        <header><h2>Описание</h2></header>
        <p>
            <b>TEWA</b> — (Template E-commerce Web Application) - это шаблон SPA онлайн-магазина/маркетплейса, созданный для быстрого запуска интернет-бизнеса без долгих настроек, как в традиционных CMS. Использование и распространение происходит с учетом лицензии <b>AGPL v 3.0</b>. Проект может служить основой для реальных коммерческих решений!
        </p>
    </article>
    <article>
        <header><h2>Ссылки</h2></header>
        <div class="flex gap" style="flex-wrap: wrap">
            <a href="https://www.gnu.org/licenses/agpl-3.0.html" rel="noopener noreferrer nofollow" target="_blank">Лицензия GNU Affero GPL v3</a>
            <a href="https://github.com/d0lmany/TEWA" rel="noopener noreferrer nofollow" target="_blank">Исходный код на GitHub</a>
            <router-link :to="{name: 'Legal'}">Правовые документы</router-link>
        </div>
    </article>
</main>
</template>
<style scoped>
main {
    background: var(--el-color-primary-light-9);
    border-radius: 1rem;
    padding: 1rem;
    margin: 1rem;
}
/* text */
h1 {
    font-size: 1.75rem;
    margin-top: 0;
}
p, ul {
    text-align: justify;
    line-height: 1.5;
    font-size: 1.05rem;
}
article:last-child p {
    margin-bottom: 0;
}
.flex a {
    display: block;
    border: 1px solid var(--el-border-color);
    padding: 1rem;
    border-radius: .5rem;
    font-size: 1.25rem;
    flex: 1;
    min-width: 30%;
    text-align: center;
}
a {
    transition: .1s ease-out;
    text-decoration: none;
    color: var(--el-text-color-regular);
}
a:hover {
    color: var(--el-color-primary);
}
.flex a:hover {
    border-color: var(--el-color-primary);
}
</style>