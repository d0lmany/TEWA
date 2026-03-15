<script setup lang="ts">
import { ArrowRight, StarFilled, Shop, InfoFilled, ShoppingCart, Star, Service, Top, Plus, Minus } from '@element-plus/icons-vue';
import { computed, inject, onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import ReviewCard from '@/components/cards/ReviewCard.vue';
import ClaimModal from '@/components/modals/ClaimModal.vue';
import { useUserStore } from '@/stores/userStore';
import { useCartStore } from '@/stores/cartStore';
import { useFavoriteStore } from '@/stores/favoriteStore';
import type { UI } from '@/ts/types';
import type { FullProduct, ProductAttribute, CartItem, FavoriteListItem } from '@/ts/entities';
import type { Services } from '@/ts/services';
import { getPricesWithAttrs } from '@/ts/utils';

interface Attributes {
    checked: Record<string, string>,
    variant: Record<string, ProductAttribute[]>,
    nonVariant: Record<string, string>,
}

const product = reactive<Partial<FullProduct>>({});
const {
    cart: CartService,
    product: ProductService,
    i18n,
    favorite: FavoriteService,
} = inject('services') as Services;
const attributes = reactive<Attributes>({
    checked: {},
    variant: {},
    nonVariant: {},
})
const loadings = reactive({
    product: true,
    cart: false,
    favorite: false,
})
const claimFormVisible = ref(false);
const [route, router] = [useRoute(), useRouter()];
const formatter = (inject('ui') as UI).currencyFormatter;
const [userStore, cartStore, favoriteStore]
= [useUserStore(), useCartStore(), useFavoriteStore()];
const cartItem = reactive<Partial<CartItem>>({});
const favoriteItem = reactive<Partial<FavoriteListItem & { list?: string }>>({});

const evaluations = computed(() => {
    const reviews = product?.feedbacks?.reviews || [];
    const result: Record<number, number> = {};
    const totalReviews = reviews?.length || 0;

    for (let i = 1; i < 6; i++) {
        const count = reviews?.filter(review => review.evaluation === i).length || 0;
        result[i] = totalReviews > 0 ? Math.round((count / totalReviews) * 100) : 0;
    }
    return result;
})
const filteredEvaluations = computed(() => {
    const filtered: Record<number, number> = {};
    for (const [key, value] of Object.entries(evaluations.value)) {
        if (value > 0) {
            filtered[Number(key)] = value;
        }
    }
    return filtered;
})
const total = computed(() => {
    setCartItem();
    return getPricesWithAttrs(
        // @ts-ignore
        product,
        attributes.variant,
        attributes.checked
    )
})
const collectedCheckedIds = computed(() => {
    return Object.keys(attributes.checked)
        .map(type => {
            const target = attributes.checked[type];
            return attributes.variant[type]?.find(item => item.attr_value === target)?.id || 0;
        })
        .filter(id => id);
})
const album = computed<string[]>(() => [
    product.photo || '',
    ...product.details?.album || []
])

// @ts-ignore
const clearCartItem = () => Object.keys(cartItem).forEach(key => delete cartItem[key]);
// @ts-ignore
const clearFavoriteItem = () => Object.keys(favoriteItem).forEach(key => delete favoriteItem[key]);
const addToCart = async () => { 
    loadings.cart = true;

    if (userStore.isAuth) {
        const item: CartItem = {
            id: 0,
            product_id: product.id || 0,
            quantity: 1,
            product_attributes: collectedCheckedIds.value,
        };

        const response = await CartService.store(item);
        if (response.success) {
            ElMessage.success('Добавлено в корзину!');
            if (response.data) {
                item.id = response.data.id;
                cartStore.addItem(item);
            }
        } else {
            ElMessage.error(`Не удалось добавить товар в корзину: ${response.message}`);
            console.error(response);
        }
    } else {
        ElMessage.warning('Корзина? А вы вошли в аккаунт?');
    }

    loadings.cart = false;
}
const increase = async () => {
    try {
        if (cartItem.quantity && cartItem.quantity < (product.quantity || 0)) {
            cartItem.quantity++;
            if (cartItem && cartItem.id) {
                const response = await CartService.update(cartItem.id, {
                    quantity: cartItem.quantity
                });

                if (response.success) {
                    cartStore.updateItem(cartItem.id, cartItem.quantity);
                } else {
                    console.error(response);
                    throw new Error(`Не удалось обновить количество товара: ${response.message}`)
                }
            }
        }
    } catch (error) {
        (cartItem.quantity as number)--;
        ElMessage.error(error instanceof Error ? error.message : 'Неизвестная ошибка');
    }
}
const decrease = async () => {
    if (cartItem.quantity && cartItem.quantity <= 0) return;

    (cartItem.quantity as number)--;
    try {
        if (!cartItem.id) return;

        if (cartItem.quantity === 0) {
            const response = await CartService.destroy(cartItem.id);
            if (response.success) {
                ElMessage.success(`${product.name} - удалён из корзины`);
                cartStore.removeItem(cartItem.id);
                clearCartItem();
            } else {
                console.error(response);
                throw new Error('Не удалось удалить товар из корзины')
            }
        } else {
            const response = await CartService.update(cartItem.id, {
                quantity: cartItem.quantity
            });

            if (response.success) {
                cartStore.updateItem(cartItem.id, cartItem.quantity as number);
            } else {
                console.error(response);
                throw new Error(`Не удалось обновить количество товара: ${response.message}`)
            }
        }
    } catch (error) {
        (cartItem.quantity as number)++;
        ElMessage.error(error instanceof Error ? error.message : 'Неизвестная ошибка');
    }
}
const toggleFavorite = async () => {
    loadings.favorite = true;
    if (userStore.isAuth && product.id) {
        const response = await FavoriteService.toggle(product.id);

        if (response.success) {
            if (response.message === 'removed') {
                ElMessage.success('Удалено из избранного!');
                favoriteStore.removeItem(favoriteItem.id || 0, favoriteItem.list_id || 0);
                clearFavoriteItem();
            } else if (response.data) {
                ElMessage.success('Добавлено в избранное!');
                favoriteStore.addItem(response.data, response.data.list_id);
                Object.assign(favoriteItem, response.data);
            }
        } else {
            ElMessage.error(`Не удалось добавить товар в избранное: ${response.message}`);
            console.error(response);
        }
    } else {
        ElMessage.warning('Избранное? А вы вошли в аккаунт?');
    }
    loadings.favorite = false;
}
const setFavoriteItem = () => {
    if (userStore.isAuth && product.id) {
        const item = favoriteStore.getItemByProductId(product.id);
        if (item) {
            Object.assign(favoriteItem, {
                ...item,
                list: favoriteStore.getList(item.list_id)?.name
            })
        } else {
            clearFavoriteItem();
        }
    }
}
const setCartItem = () => {
    if (userStore.isAuth && product.id) {
        const item = cartStore.getItemByProductId(product.id, collectedCheckedIds.value);
        if (item) {
            Object.assign(cartItem, item);
        } else {
            clearCartItem();
        }
    }
}
const copyURL = async () => await copy(window.location.href);
const copy = async (target: string) => {
    try {
        await navigator.clipboard.writeText(target);
        ElMessage.success(`Успешно скопировано!`);
    } catch (e) {
        ElMessage.error(`Не удалось скопировать "${target}": ${e instanceof Error ? e.message : 'Неизвестная ошибка'}`);
    }
}
const translate = (phrase: string): string => {
    const word = i18n.translate(phrase);
    return word[0]?.toUpperCase() + word.slice(1);
}
const partitionAttributes = () => {
    if (!product?.attributes) {
        attributes.variant = {};
        attributes.nonVariant = {};
        return;
    }

    const variant: Record<string, ProductAttribute[]> = {};
    const nonVariant: Record<string, string> = {};

    for (const [type, attrs] of Object.entries(product.attributes)) {
        if (!Array.isArray(attrs)) continue;

        const vars = attrs.filter(a => a.is_variant);
        const nonVars = attrs.filter(a => !a.is_variant);

        if (vars.length) {
            variant[type] = [...vars];
        }

        if (nonVars.length) {
        nonVariant[type] = nonVars
            .map(a => a.attr_value || '')
            .filter(val => val !== '')
            .join('; ');
        }
    }

    attributes.variant = variant;
    attributes.nonVariant = nonVariant;
};
const loadProduct = async () => {
    try {
        if (!route.params.id && isNaN(Number(route.params.id))) throw new Error('Не удалось загрузить товар');
        const response = await ProductService.show(Number(route.params.id));

        if (response.success && response.data) {
            Object.assign(product, response.data);

            if (product?.attributes) {
                Object.keys(product.attributes).forEach(type => {
                    if (product.attributes && product.attributes[type]) {
                        const defaultAttr = product.attributes[type].find(attr => attr.is_default);
                        if (defaultAttr) attributes.checked[type] = defaultAttr.attr_value;
                    }
                })
            }
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        if (error instanceof Error) {
            if (error.message.includes('404')) router.push({name: 'NotFound'});
            ElMessage.error(error);
        } else {
            router.push({name: 'Home'});
            ElMessage.error('Неизвестная ошибка');
        }
    } finally {
        loadings.product = false;
    }
}

onMounted(() => {
    loadProduct()
        .then(() => {
            partitionAttributes();
            setCartItem();
            setFavoriteItem();
        })
        .catch(e => {
            ElMessage.error(`Ошибка: ${e}`);
            console.error(e);
            router.push({name: 'Home'})
        })
})
onUnmounted(() => {
    loadings.product = true;
})

watch(
    () => favoriteStore.length,
    () => setFavoriteItem()
)
watch(
    () => cartStore.length,
    () => setCartItem()
)
watch(
    () => route.params.id,
    (newId) => {
        if (newId) {
            loadings.product = true;
            loadProduct()
                .then(() => {
                    partitionAttributes();
                    setCartItem();
                    setFavoriteItem();
                })
                .catch(e => {
                    ElMessage.error(`Ошибка: ${e}`);
                    console.error(e);
                    router.push({name: 'Home'})
                })
        }
    }
)
</script>
<template>
<main>
    <article>
        <el-breadcrumb :separator-icon="ArrowRight" style="padding: 9px 0">
            <el-breadcrumb-item :to="{ name: 'Home' }">Главная</el-breadcrumb-item>
            <el-breadcrumb-item
                v-if="product.category?.parent"
                :to="{
                    name: 'Search',
                    query: {
                        category_id: product?.category?.parent?.id,
                        category: product?.category?.parent?.name
                    }
                }"
            >{{ product?.category?.parent?.name }}</el-breadcrumb-item>
            <el-breadcrumb-item
                v-if="!loadings.product"
                :to="{
                    name: 'Search',
                    query: {
                        category_id: product?.category?.id,
                        category: product?.category?.name
                    }
                }"
            >
                <el-skeleton
                    animated
                    :loading="loadings.product"
                    style="width: 50px;"
                >
                    {{ product?.category?.name }}
                    <template #template>
                        <el-skeleton-item variant="p"/>
                    </template>
                </el-skeleton>
            </el-breadcrumb-item>
            <el-breadcrumb-item>
                <el-skeleton
                    animated
                    :loading="loadings.product"
                    style="width: 50px;"
                >
                    {{ product.name }}
                    <template #template>
                        <el-skeleton-item variant="p"/>
                    </template>
                </el-skeleton>
            </el-breadcrumb-item>
        </el-breadcrumb>
        <el-card shadow="hover">
            <div class="hero">
                <el-skeleton
                    :loading="loadings.product" 
                    animated
                >
                    <template #template>
                        <div class="flex gap">
                            <el-skeleton-item variant="image" style="width: 50%; height: 250px;"/>
                            <div class="hero-content" style="flex:1; align-self: flex-start">
                                <div class="flex gap low">
                                    <el-skeleton-item variant="h1" style="width: 50%"/>
                                    <el-skeleton-item variant="h3" style="width: 10%"/>
                                </div>
                                <el-skeleton-item variant="p" style="width: 40%"/>
                                <el-skeleton-item variant="p" style="width: 40%"/>
                                <el-skeleton-item variant="p" style="width: 40%"/>
                            </div>
                        </div>
                    </template>
                    <template #default>
                        <div
                            class="images"
                            v-if="product.details"
                        >
                            <el-image
                                class="image"
                                fit="cover"
                                lazy
                                v-for="photo in album"
                                :src="photo"
                                :key="photo"
                            />
                        </div>
                        <el-image
                            :preview-src-list="album"
                            :src="product.photo"
                            class="image"
                            fit="cover"
                            show-progress
                            lazy
                        />
                        <div class="hero-content gap">
                            <div class="flex gap">
                                <h1 class="section-header">{{ product.name }}</h1>
                                <div class="rating flex">
                                    <el-icon
                                        v-if="product?.feedbacks?.rating"
                                        :size="24"
                                        color="#F7BA2A"
                                    ><star-filled/></el-icon>
                                    {{product?.feedbacks?.rating ? product?.feedbacks?.rating.toFixed(1) : 'Нет оценок'}}
                                </div>
                                <el-text
                                    size="large"
                                    v-if="product?.feedbacks?.reviews?.length"
                                >Отзывы: {{product?.feedbacks?.reviews?.length}} </el-text>
                            </div>
                            <div class="attributes">
                                <div
                                    v-for="(value, key) in attributes.variant"
                                    class="flex gap"
                                    :key="key"
                                >
                                    <el-tag
                                        size="large"
                                        type="info"
                                    >
                                        <el-text size="large">{{ translate(key) + ':' }}</el-text>
                                    </el-tag>
                                    <el-radio-group v-model="attributes.checked[key]">
                                        <el-radio-button
                                            v-for="attr in value"
                                            :value="attr.attr_value"
                                            :key="attr.id"
                                            class="hideBorders"
                                        >
                                            {{ translate(attr.attr_value) }}
                                        </el-radio-button>
                                    </el-radio-group>
                                </div>
                            </div>
                            <div class="attributes">
                                <div
                                    v-for="(value, key) in attributes.nonVariant"
                                    class="flex gap"
                                    :key="key"
                                >
                                    <el-tag
                                        size="large"
                                        type="info"
                                    >
                                        <el-text>{{ translate(key) + ':' }}</el-text>
                                    </el-tag>
                                    <el-text size="large">{{ translate(value) }}</el-text>
                                </div>
                            </div>
                            <div class="tags">
                                <el-tag
                                    size="large"
                                    v-for="tag in product.tags"
                                    :key="tag.id"
                                >{{tag.name}}</el-tag>
                            </div>
                        </div>
                    </template>
                </el-skeleton>
            </div>
        </el-card>
        <el-card shadow="hover" body-class="flex low gap" v-if="product.shop">
            <el-skeleton
                :loading="loadings.product"
                animated
            >
                <template #template>
                    <div class="flex gap">
                        <el-skeleton-item
                            variant="rect"
                            style="width: 75px; height: 75px;"
                        />
                        <div class="flex col gap" style="flex: 1; align-self: flex-start;align-items: flex-start;">
                            <el-skeleton-item variant="h1" style="width: 50%"/>
                            <div class="flex gap">
                                <el-skeleton-item variant="button"/>
                                <el-skeleton-item variant="button"/>
                            </div>
                        </div>
                    </div>
                </template>
                <template #default>
                    <el-avatar
                        size="large"
                        shape="square"
                        style="width: 75px; height: 75px;"
                        :src="product?.shop?.picture"
                        lazy
                    >
                        <el-icon :size="24"><Shop /></el-icon>
                    </el-avatar>
                    <div class="shop">
                        <div class="flex gap">
                            <b style="font-size: 1.5rem">{{product?.shop?.name}}</b>
                            <el-popover
                                placement="top"
                                :width="175"
                            >
                                <template #reference>
                                    <el-icon :size="20"><info-filled/></el-icon>
                                </template>
                                <b>{{ `${translate(product?.shop?.seller?.type || '')}: ${product?.shop?.seller.full_name}` }}</b><br>
                                {{ `ИНН/ОГРН: ${product?.shop?.seller.code}` }}
                            </el-popover>
                            <el-rate
                                v-if="product?.shop?.rating"
                                allow-half
                                disabled
                                :modelValue="product?.shop?.rating"
                                :colors="['#99A9BF', '#F7BA2A', '#FF9900']"
                            />
                            <el-text v-else size="large">Нет оценок</el-text>
                        </div>
                        <el-button round @click="$router.push({ name: 'Shop', params: { id: product?.shop?.id || 0}})">
                            <el-icon class="el-icon--left"><Shop/></el-icon>
                            Перейти
                        </el-button>
                    </div>
                </template>
            </el-skeleton>
        </el-card>
        <el-skeleton
            :loading="loadings.product"
            animated
            :count="3"
        >
            <template #default>
                <el-card shadow="hover" v-if="product?.details?.description">
                    <div class="section-header">Описание</div>
                    <p class="description">
                        {{ product.details.description }}
                    </p>
                </el-card>
                <el-card shadow="hover" v-if="product?.details?.application">
                    <div class="section-header">Способ применения</div>
                    <p class="description">
                        {{ product.details.application }}
                    </p>
                </el-card>
                <el-card shadow="hover" v-if="product?.feedbacks?.rating">
                    <div class="section-header flex low gap">
                        Отзывы
                        <el-tag effect="dark"
                        >{{product?.feedbacks?.reviews?.length}}</el-tag>
                    </div>
                    <div class="reviews">
                        <review-card
                            v-for="review in product?.feedbacks?.reviews"
                            :review="review"
                            :key="review.id"
                        />
                    </div>
                </el-card>
            </template>
            <template #template>
                <el-card shadow="hover" style="margin-bottom: 1rem;">
                    <el-skeleton-item variant="h1" style="width: 25%;"/><br><br>
                    <el-skeleton-item variant="p"/>
                    <el-skeleton-item variant="p"/>
                    <el-skeleton-item variant="p"/>
                </el-card>
            </template>
        </el-skeleton>
    </article>
    <el-backtop>
        <el-icon :size="24"><Top/></el-icon>
    </el-backtop>
    <aside>
        <div class="flex">
            <el-skeleton
                :loading="loadings.product"
                animated
            >
            <template #template>
                <div class="flex gap">
                    <el-skeleton-item variant="button" style="flex: 1"/>
                    <el-skeleton-item variant="button" style="flex: 1"/>
                </div>
            </template>
            <template #default>
                <el-button @click="copy(product.id?.toString() || '')">
                    Артикул: {{product.id}}
                </el-button>
                <el-button @click="copyURL()">
                    Поделиться
                </el-button>
            </template>
            </el-skeleton>
        </div>
        <el-card shadow="hover">
            <el-skeleton
                :loading="loadings.product"
                animated
            >
                <template #template>
                    <el-skeleton-item variant="h1"/>
                    <div class="flex gap" style="margin-top: .5rem">
                        <el-skeleton-item variant="button" style="flex: 1"/>
                        <el-skeleton-item variant="button" style="flex: 1"/>
                    </div>
                    <hr style="color: var(--el-card-border-color)">
                    <div class="flex gap" style="margin-top: .5rem;">
                        <el-skeleton-item variant="button" style="flex: 1"/>
                        <el-skeleton-item variant="button" style="flex: 1"/>
                    </div>
                </template>
                <template #default>
                    <div
                        v-if="product.status === 'on'"
                        class="contents"
                    >
                        <div
                            class="section-header"
                            style="text-align: center"
                        >
                            {{ formatter.format(total.totalWithDisc) }}
                            <div class="contents" v-if="product.price?.discount">
                                <span style="font-size:1rem">{{ `– ${product.price.discount}% ` }}</span>
                                <s>{{ formatter.format(total.total) }}</s>
                            </div>
                        </div>
                        <div
                            v-if="userStore.isAuth"
                            class="flex"
                        >
                        <el-popover :disabled="(product.quantity || 0) > 0">
                        <template #reference>
                            <el-button
                                :disabled="(product.quantity || 0) < 1"
                                @click="addToCart"
                                type="primary"
                                v-if="Object.keys(cartItem).length === 0"
                                :loading="loadings.cart"
                                round
                            >
                                <el-icon class="el-icon--left"><shopping-cart/></el-icon>
                                В корзину
                            </el-button>
                        </template>
                        <div style="text-align: center">К сожалению, этот товар закончился 😥</div>
                        </el-popover>
                        <div class="count-container" v-if="Object.keys(cartItem).length > 0">
                            <el-button
                                circle
                                :icon="Minus"
                                @click="decrease"
                                aria-label="Уменьшить количество на 1"
                            />
                            <el-button text role="text" class="count">
                                {{ cartItem.quantity }}
                            </el-button>
                            <el-button
                                circle
                                :icon="Plus"
                                @click="increase"
                                aria-label="Увеличить количество на 1"
                            />
                        </div>
                        <el-popover
                            :disabled="!Object.keys(favoriteItem).length || favoriteItem?.list === '__favorite__'"
                        >
                            <div style="text-align: center">Добавлен в лист '{{ favoriteItem?.list }}'</div>
                            <template #reference>
                            <el-button
                                round
                                @click="toggleFavorite"
                                :loading="loadings.favorite"
                            >
                            <div class="contents" v-if="Object.keys(favoriteItem).length > 0">
                                <el-icon class="el-icon--left"><star-filled/></el-icon>
                                В избранном
                            </div>
                            <div class="contents" v-else>
                                <el-icon class="el-icon--left"><Star/></el-icon>
                                В избранное
                            </div>
                            </el-button>
                            </template>
                        </el-popover>
                        </div>
                        <hr v-if="userStore.isAuth" style="margin-block: 1rem; color: var(--el-card-border-color);">
                        <div v-if="userStore.isAuth">
                            <el-button
                                type="danger" text
                                @click="claimFormVisible = true"
                            >
                                <el-icon class="el-icon--left"><Service/></el-icon>
                                Пожаловаться
                            </el-button>
                        </div>
                    </div>
                    <div
                        class="section-header"
                        style="text-align: center; margin: 0;"
                        v-else
                    >
                        Этот товар не продаётся
                    </div>
                </template>
            </el-skeleton>
        </el-card>
        <el-card shadow="hover" v-if="product?.feedbacks?.rating">
            <el-skeleton
                :loading="loadings.product"
                animated
            >
                <template #template>
                    <div class="flex gap" style="padding-bottom: .5rem">
                        <el-skeleton-item variant="button" style="flex:1"/>
                        <el-skeleton-item variant="button" style="flex:1"/>
                    </div>
                    <el-skeleton-item variant="p"/>
                    <el-skeleton-item variant="p"/>
                    <el-skeleton-item variant="p"/>
                </template>
                <template #default>
                    <div class="flex">
                        <el-rate
                            :modelValue="product?.feedbacks?.rating"
                            allow-half
                            disabled
                        />
                        <el-text size="large">{{ `${product?.feedbacks?.rating} / 5` }}</el-text>
                    </div>
                    <div
                        class="flex gap"
                        style="padding-top:.5rem"
                        v-for="(v, k) in filteredEvaluations"
                        :key="k"
                    >
                        <el-progress :percentage="v" :show-text="false" style="flex:1"/>
                        <el-text size="large">{{k}}</el-text>
                    </div>
                </template>
            </el-skeleton>
        </el-card>
    </aside>
    <claim-modal
        v-model="claimFormVisible"
        entity="product"
        :entity_id="product.id"
    />
</main>
</template>
<style scoped>
main {
    margin: 1rem;
    display: grid;
    grid-template-columns: 75% 1fr;
    gap: 1rem;
}
article, aside {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
aside {
    height: max-content;
    position: sticky;
    top: 4.5rem;
}
.hero {
    display: flex;
    gap: 1rem;
}
.image {
    border-radius: .5rem;
    overflow: hidden;
    height: 400px;
    min-width: 30%;
}
.images {
    border-radius: .5rem;
    display: flex;
    flex-direction: column;
    max-width: 75px;
    gap: 1rem;
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
    flex-shrink: 0;
    scrollbar-width: 0;
}
.images::-webkit-scrollbar {
    width: 0;
}
.images .image {
    width: 75px;
    height: 75px;
    flex-shrink: 0;
}
.hero-content, .attributes {
    width: 100%;
    display: flex;
    flex-direction: column;
}
.hero-content .section-header {
    margin: 0
}
.rating {
    gap: .5rem;
    font-size: 1.25rem;
}
.tags {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}
.el-tag {
    border-radius: 1rem;
}
.el-radio-group {
    border-radius: 1rem;
    overflow: hidden;
    border: var(--el-border);
}
.hideBorders:deep(.el-radio-button__inner) {
    border: none;
    border-left: var(--el-border);
}
.attributes {
    gap: .5rem;
}
.shop {
    display: flex;
    align-items: flex-start;
    flex-direction: column;
    gap: 1rem;
}
.section-header {
    font-weight: 600;
    margin-bottom: .75rem;
}
.section-header + .description {
    margin: 0;
    text-align: justify;
    line-height: 1.4rem;
    white-space: pre-line;
}
.reviews {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
s {
    color: var(--el-text-color-secondary);
    font-size: 1rem;
}
.el-button + .el-button {
    margin: 0;
}
.count-container {
    min-width: 35%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: calc(var(--el-border-radius-round) - 2px);
    border: 1px solid var(--el-border-color);
    transition: all var(--el-transition-duration);
}
.count-container:hover {
    border-color: var(--el-color-primary);
}
.count-container .count {
    pointer-events: none;
}
</style>