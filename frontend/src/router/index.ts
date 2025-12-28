import { createRouter, createWebHistory } from 'vue-router'

import Index from '@/pages/IndexPage.vue';
import MyCart from '@/pages/my/CartPage.vue';
import MyFavorite from '@/pages/my/FavoritePage.vue';
import My from '@/pages/my/ProfilePage.vue';
import Search from '@/pages/SearchPage.vue';
import Product from '@/pages/ProductPage.vue';

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: '/', 
            name: 'Home',
            component: Index,
            meta: {
                title: 'TEWA'
            }
        },
        {
            path: '/my/favorite', 
            name: 'Favorite',
            component: MyFavorite,
            meta: {
                title: 'Избранное'
            }
        },
        {
            path: '/my/cart', 
            name: 'Cart',
            component: MyCart,
            meta: {
                title: 'Корзина'
            }
        },
        {
            path: '/my/:pathMatch(.*)*', 
            name: 'Profile',
            component: My,
            meta: {
                title: 'Профиль'
            },
        },
        {
            path: '/search',
            name: 'Search',
            component: Search,
            meta: {
                // @ts-ignore
                title: (route) => `Поиск: ${route.query.q || route.query.category }`
            }
        },
        {
            path: '/product/:id/:slug?',
            name: 'Product',
            component: Product,
            meta: {
                // @ts-ignore
                title: (route) => `${route.params.slug} на TEWA`
            }
        },
        {
            name: 'Shop',
            path: '/shop/:id',
            component: () => import('@/pages/ShopPage.vue'),
        },
        {
            name: 'About',
            path: '/legal/about',
            component: () => import('@/pages/legal/AboutPage.vue'),
        },
        {
            name: 'Legal',
            path: '/legal',
            component: () => import('@/pages/legal/LegalPage.vue'),
        },
        {
            name: 'NotFound',
            path: '/:pathMatch(.*)*',
            component: () => import('@/pages/404Page.vue'),
            meta: {
                title: 'Not found...'
            }
        }
    ],
});

router.beforeEach((to, from, next) => {
    const nearestWithTitle = to.matched.slice().reverse().find(r => r.meta && r.meta.title)
    let pageTitle = 'TEWA'
    if (nearestWithTitle) {
        pageTitle = typeof nearestWithTitle.meta.title === 'function'
            ? nearestWithTitle.meta.title(to)
            : nearestWithTitle.meta.title
        if (to.query.name) {
            // @ts-ignore
            pageTitle = `${pageTitle} | ${decodeURIComponent(to.query.name)}`
        }
    }
    document.title = pageTitle
    Array.from(document.querySelectorAll('[data-vue-router-controlled]'))
            // @ts-ignore
        .forEach(el => el.parentNode.removeChild(el))
    const metaTags = to.meta.metaTags || []
    const processedMetaTags = typeof metaTags === 'function'
        ? metaTags(to)
        : metaTags
            // @ts-ignore
    if (!processedMetaTags.some(tag => tag.property === 'og:title')) {
        processedMetaTags.push({
            property: 'og:title',
            content: pageTitle
        })
    }
            // @ts-ignore
    processedMetaTags.forEach(tagDef => {
        const tag = document.createElement('meta')
        Object.keys(tagDef).forEach(key => {
            tag.setAttribute(key, tagDef[key])
        })
        tag.setAttribute('data-vue-router-controlled', '')
        document.head.appendChild(tag)
    })
    next()
})

export default router