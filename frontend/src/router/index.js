import { createRouter, createWebHistory } from 'vue-router'

import Index from '@/pages/Index.vue';
import MyCart from '@/pages/my/Cart.vue';
import MyFavorite from '@/pages/my/Favorite.vue';
import MyOrders from '@/pages/my/Orders.vue';
import My from '@/pages/my/Index.vue';
import Search from '@/pages/search.vue';
import Product from '@/pages/product.vue';
import Shop from '@/pages/shop.vue';
import About from '@/pages/legal/about.vue';
import Legal from '@/pages/legal/legal.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/', 
      name: 'Home',
      component: Index,
      meta: {
        title: 'TEWA — Свободный маркетплейс'
      }
    },
    {
      path: '/my', 
      name: 'Profile',
      component: My,
      meta: {
        title: 'Профиль'
      }
    },
    {
      path: '/my/orders', 
      name: 'Orders',
      component: MyOrders
    },
    {
      path: '/my/favorite', 
      name: 'Favorite',
      component: MyFavorite
    },
    {
      path: '/my/cart', 
      name: 'Cart',
      component: MyCart
    },
    {
      path: '/search',
      name: 'Search',
      component: Search,
      meta: {
        title: (route) => `Поиск: ${route.query.q || route.query.category }`
      }
    },
    {
      path: '/product/:id/:slug?',
      name: 'Product',
      component: Product,
      meta: {
        title: (route) => `${route.params.slug} на TEWA`
      }
    },
    {
      name: 'Shop',
      path: '/shop/:id',
      component: Shop,
    },
    {
      name: 'AboutTEWA',
      path: '/legal/about',
      component: About,
    },
    {
      name: 'Legal',
      path: '/legal',
      component: Legal
    },
    {
      name: 'NotFound',
      path: '/:pathMatch(.*)*',
      component: () => import('@/pages/404.vue')
    }
  ],
})

router.beforeEach((to, from, next) => {
  const nearestWithTitle = to.matched.slice().reverse().find(r => r.meta && r.meta.title)
  let pageTitle = 'TEWA'
  if (nearestWithTitle) {
    pageTitle = typeof nearestWithTitle.meta.title === 'function'
      ? nearestWithTitle.meta.title(to)
      : nearestWithTitle.meta.title
    if (to.query.name) {
      pageTitle = `${pageTitle} | ${decodeURIComponent(to.query.name)}`
    }
  }
  document.title = pageTitle
  Array.from(document.querySelectorAll('[data-vue-router-controlled]'))
    .forEach(el => el.parentNode.removeChild(el))
  const metaTags = to.meta.metaTags || []
  const processedMetaTags = typeof metaTags === 'function'
    ? metaTags(to)
    : metaTags
  if (!processedMetaTags.some(tag => tag.property === 'og:title')) {
    processedMetaTags.push({
      property: 'og:title',
      content: pageTitle
    })
  }
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