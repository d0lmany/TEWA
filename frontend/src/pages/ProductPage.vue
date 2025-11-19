<script setup>
import { ArrowRight, StarFilled, Shop, InfoFilled, ShoppingCart, Star, Service, Top, Plus, Minus } from '@element-plus/icons-vue';
import { computed, inject, onMounted, onUnmounted, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { ElMessage } from 'element-plus';
import ReviewCard from '@/components/cards/ReviewCard.vue';
import ClaimModal from '@/components/modals/ClaimModal.vue';
import { storeToRefs } from 'pinia';
import { useUserStore } from '@/stores/userStore';

const storageURL = inject('storageURL');
const router = useRouter();
const route = useRoute();
const product = ref({});
const loading = ref(true);
const {
    product: ProductService,
    cart: CartService,
    i18n,
    favorite: FavoriteService,
} = inject('services');
const checkedAttributes = ref({});
const formatter = new Intl.NumberFormat(navigator.language, {
    style: 'currency',
    currency: inject('currency')
});
const userStore = useUserStore();
const { isAuth } = storeToRefs(userStore);
const count = ref(0);
const cartLoading = ref(false);
const favoriteLoading = ref(false);
const cartItem = ref(null);
const favoriteItem = ref(null);
const claimFormVisible = ref(false);

const album = computed(() => {
    if (!product.value?.details) return [mainPhoto.value];

    const alb = JSON.parse(product.value.details.album || '[]');

    return [
        mainPhoto.value,
        ...alb.map(url => makePhotoURL(url)),
    ];
});
const rating = computed(() => parseFloat(product.value?.feedbacks?.rating));
const shopRating = computed(() => parseFloat(product.value?.shop?.rating));

const translate = (text) => {
    const word = i18n.translate(text);
    return word[0].toUpperCase() + word.slice(1);
}
const getAttributes = (isVariant = false) => {
    if (!product.value?.attributes) return {};

    const attributes = product.value.attributes, result = {};
    Object.keys(attributes).forEach(type => {
        result[type] = attributes[type].filter(attr => attr.is_variant == isVariant);
        if (result[type].length === 0) delete result[type];
    });

    return result;
}
const addToCart = async () => {
    cartLoading.value = true;

    if (isAuth.value) {
        const item = {
            product_id: product.value.id,
            quantity: 1,
            product_attributes: collectCheckedAttrs.value,
        };

        const response = await CartService.store(item);
        if (response.success) {
            ElMessage.success('Добавлено в корзину!');
            item.id = response.data.data.id;
            userStore.addToCart(item);
        } else {
            ElMessage.error(`Не удалось добавить товар в корзину: ${response.message.data.message}`);
            console.log(response);
        }
    } else {
        ElMessage.warning('Корзина? А вы вошли в аккаунт?');
    }

    cartLoading.value = false;
}

const total = computed(() => {
    const attrs = getAttributes(true);
    let impact = 0;

    Object.keys(attrs).forEach(type => {
        const selectedAttr = attrs[type].find(attr => attr.attr_value === checkedAttributes.value[type]);
        if (selectedAttr) {
            impact += parseFloat(selectedAttr.price || 0);
        }
    });
    setProductCount();
    return parseFloat(product.value.price?.base_price || 0) + impact;
});
const totalWithDisc = computed(() => {
    if (!product.value.price?.discount) return total.value;
    return total.value * (1 - product.value.price.discount / 100);
});
const evaluations = computed(() => {
    const reviews = product.value.feedbacks.reviews;
    const result = {};
    const totalReviews = reviews.length;

    for (let i = 1; i < 6; i++) {
        const count = reviews.filter(review => review.evaluation === i).length;
        result[i] = totalReviews > 0 ? Math.round((count / totalReviews) * 100) : 0;
    }
    return result;
})
const filteredEvaluations = computed(() => {
    const filtered = {};
    for (const [key, value] of Object.entries(evaluations.value)) {
        if (value > 0) {
            filtered[key] = value;
        }
    }
    return filtered;
})

const collectCheckedAttrs = computed(() => {
    const data = getAttributes(true);
    
    return Object.keys(checkedAttributes.value)
        .map(type => {
            const target = checkedAttributes.value[type];
            const filteredItems = data[type]?.filter(item => item.attr_value == target) || [];
            return filteredItems[0]?.id;
        })
        .filter(id => id !== undefined);
})
const copyURL = async () => await copy(window.location.href);
const copy = async (target) => {
    try {
        await navigator.clipboard.writeText(target);
        ElMessage.success(`Успешно скопировано!`);
    } catch (e) {
        ElMessage.error(`Не удалось скопировать "${target}": ${e}`);
    }
}
const makePhotoURL = (filename) => `${storageURL}/${filename}`;
const mainPhoto = computed(() => makePhotoURL(product.value.photo));
const getProduct = async () => {
    try {
        const response = await ProductService.show(route.params.id);

        if (response.success) {
            product.value = await response.data.data;

            if (product.value?.attributes) {
                Object.keys(product.value.attributes).forEach(type => {
                    const defaultAttr = product.value.attributes[type].find(attr => attr.is_default);
                    if (defaultAttr) checkedAttributes.value[type] = defaultAttr.attr_value;
                });
            }
        } else {
            throw new Error(response.message);
        }
    } catch (error) {
        if (error.message.includes('404')) {
            router.push({name: 'NotFound'});
        } else {
            router.push({name: 'Home'});
            ElMessage.error(`Ошибка: ${error}`);
            console.error(error);
        }
    } finally {
        loading.value = false;
    }
}
const increase = async () => {
    if (count.value < product.value.quantity) {
        count.value++;

        const response = await CartService.update(cartItem.value.id, count.value);

        if (response.success) {
            userStore.updateCartItem(cartItem.value.id, count.value);
        } else {
            count.value--;
            ElMessage.error(response.message);
            console.error(response);
        }
    }
}
const decrease = async () => {
    if (count.value !== 0) {
        count.value--;

        if (count.value) {
            const response = await CartService.update(cartItem.value.id, count.value);

            if (response.success) {
                userStore.updateCartItem(cartItem.value.id, count.value);
            } else {
                count.value++;
                ElMessage.error(response.message);
                console.error(response);
            }
        } else {
            const response = await CartService.destroy(cartItem.value.id);

            if (response.success) {
                ElMessage.success('Удалено из корзины!');
                userStore.removeFromCart(cartItem.value.id);
                cartItem.value = null;
            } else {
                count.value++;
                ElMessage.error(response.message);
                console.error(response);
            }
        }
    }
}
const setProductCount = () => {
    if (isAuth.value) {
        cartItem.value = userStore.getProductFromCart(product.value.id, collectCheckedAttrs.value);
        if (cartItem.value) {
            count.value = cartItem.value.quantity;
        }
    }
}
const refineFavorite = () => {
    if (isAuth.value) {
        favoriteItem.value = userStore.getFavoriteItem(product.value.id);
    }
}
const toggleFavorite = async () => {
    favoriteLoading.value = true;
    if (isAuth.value) {
        const response = await FavoriteService.toggle(product.value.id);

        if (response.success) {
            if (response.added) {
                ElMessage.success('Добавлено в избранное!');
                userStore.addToFavorite(response.data.data);
                favoriteItem.value = response.data.data;
            } else {
                ElMessage.success('Удалено из избранного!');
                userStore.removeFromFavorite(product.value.id);
                favoriteItem.value = null;
            }
        } else {
            ElMessage.error(`Не удалось добавить товар в избранное: ${response.message}`);
            console.error(response);
        }
    } else {
        ElMessage.warning('Избранное? А вы вошли в аккаунт?');
    }
    favoriteLoading.value = false;
}

onMounted(() => {
    getProduct()
        .then(() => {
            setProductCount();
            refineFavorite();
        })
        .catch(e => {
            ElMessage.error(`Ошибка: ${e}`);
            console.error(e);
            router.push({name: 'Home'})
        })
})
onUnmounted(() => {
    loading.value = true;
})
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
                        category_id: product?.category?.parent.id,
                        category: product?.category?.parent.name
                    }
                }"
            >{{ product.category.parent.name }}</el-breadcrumb-item>
            <el-breadcrumb-item
                v-if="!loading"
                :to="{
                    name: 'Search',
                    query: {
                        category_id: product?.category?.id,
                        category: product?.category.name
                    }
                }"
            >
                <el-skeleton
                    animated
                    :loading="loading"
                    style="width: 50px;"
                >
                    {{ product.category.name }}
                    <template #template>
                        <el-skeleton-item variant="p"/>
                    </template>
                </el-skeleton>
            </el-breadcrumb-item>
            <el-breadcrumb-item>
                <el-skeleton
                    animated
                    :loading="loading"
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
                    :loading="loading" 
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
                            :src="mainPhoto"
                            class="image"
                            fit="cover"
                            show-progress
                            lazy
                        />
                        <div class="hero-content">
                            <div class="flex gap">
                                <h1 class="section-header">{{product.name}}</h1>
                                <div class="rating">
                                    <el-icon
                                        v-if="rating"
                                        :size="24"
                                        color="#F7BA2A"
                                    ><StarFilled/></el-icon>
                                    {{rating ? rating.toFixed(1) : 'Нет оценок'}}
                                </div>
                                <el-text
                                    size="large"
                                    v-if="product?.feedbacks?.reviews.length"
                                >Отзывы: {{product?.feedbacks?.reviews.length}} </el-text>
                            </div>
                            <div class="attributes">
                                <div
                                    v-for="(v, k) in getAttributes(true)"
                                    class="flex gap"
                                    :key="v[0].id"
                                >
                                <el-tag
                                    size="large"
                                    type="info"
                                >
                                <el-text size="large">{{ translate(k) + ':' }}</el-text>
                                </el-tag>
                                <el-radio-group v-model="checkedAttributes[k]">
                                    <el-radio-button
                                        v-for="obj in v"
                                        :value="obj.attr_value"
                                        :key="obj.id"
                                    >
                                        {{obj.attr_value}}
                                    </el-radio-button>
                                </el-radio-group>
                                </div>
                            </div>
                            <div class="attributes">
                                <div
                                    v-for="(v, k) in getAttributes()"
                                    class="flex gap"
                                    :key="v[0].id"
                                >
                                    <el-tag
                                        size="large"
                                        type="info"
                                    >
                                    <el-text size="large">{{ translate(k) + ':' }}</el-text>
                                    </el-tag>
                                    <el-text size="large">{{ v[0].attr_value }}</el-text>
                                </div>
                            </div>
                            <div class="tags">
                                <el-tag
                                    size="large"
                                    v-for="tag in JSON.parse(product.tags)"
                                    :key="tag"
                                >{{tag}}</el-tag>
                            </div>
                        </div>
                    </template>
                </el-skeleton>
            </div>
        </el-card>
        <el-card shadow="hover" body-class="flex low gap">
            <el-skeleton
                :loading="loading"
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
                        :src="makePhotoURL(product.shop.picture)"
                    >
                        <el-icon :size="24"><Shop /></el-icon>
                    </el-avatar>
                    <div class="shop">
                        <div class="flex gap">
                            <b>{{product.shop.name}}</b>
                            <el-popover
                                placement="top"
                                :width="175"
                            >
                                <template #reference>
                                    <el-icon :size="20"><InfoFilled/></el-icon>
                                </template>
                                <b>{{ `${translate(product.shop.seller.type)}: ${product.shop.seller.full_name}` }}</b><br>
                                {{ `ИНН/ОГРН: ${product.shop.seller.code}` }}
                            </el-popover>
                            <el-rate
                                v-if="shopRating"
                                disabled
                                allow-half
                                v-model="shopRating"
                                :colors="['#99A9BF', '#F7BA2A', '#FF9900']"
                            />
                            <el-text v-else size="large">Нет оценок</el-text>
                        </div>
                        <el-button round @click="$router.push({ name: 'Shop', params: {id: product?.shop.id}})"
                        >
                            <el-icon class="el-icon--left"><Shop/></el-icon>
                            Перейти
                        </el-button>
                    </div>
                </template>
            </el-skeleton>
        </el-card>
        <el-skeleton
            :loading="loading"
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
                        >{{product?.feedbacks?.reviews.length}}</el-tag>
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
                :loading="loading"
                animated
            >
            <template #template>
                <div class="flex gap">
                    <el-skeleton-item variant="button" style="flex: 1"/>
                    <el-skeleton-item variant="button" style="flex: 1"/>
                </div>
            </template>
            <template #default>
                <el-button @click="copy(product.id)">
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
                :loading="loading"
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
                            {{ formatter.format(totalWithDisc) }}
                            <span style="font-size:1rem" v-if="parseFloat(product.price?.discount)">{{ `- ${parseFloat(product.price.discount)}% ` }}</span>
                            <s v-if="parseFloat(product.price?.discount)">{{ formatter.format(total) }}</s>
                        </div>
                        <div
                            v-if="isAuth"
                            class="flex"
                        >
                        <el-popover
                            :disabled="product.quantity > 0"
                            content="К сожалению, этот товар закончился 😥"
                        >
                        <template #reference>
                            <el-button
                                :disabled="product.quantity < 1"
                                @click="addToCart"
                                type="primary"
                                v-if="!cartItem"
                                :loading="cartLoading"
                                round
                            >
                                <el-icon class="el-icon--left"><ShoppingCart/></el-icon>
                                В корзину
                            </el-button>
                        </template>
                        </el-popover>
                        <div class="count-container" v-if="cartItem">
                            <el-button
                                circle
                                :icon="Minus"
                                @click="decrease"
                            />
                            <el-button text role="text" class="count">
                                {{ count }}
                            </el-button>
                            <el-button
                                circle
                                :icon="Plus"
                                @click="increase"
                            />
                        </div>
                        <el-button
                            round
                            @click="toggleFavorite"
                            :loading="favoriteLoading"
                        >
                        <div class="contents" v-if="favoriteItem">
                            <el-icon class="el-icon--left"><StarFilled/></el-icon>
                            В избранном
                        </div>
                        <div class="contents" v-else>
                            <el-icon class="el-icon--left"><Star/></el-icon>
                            В избранное
                        </div>
                        </el-button>
                        </div>
                        <hr v-if="isAuth" style="margin-block: 1rem; color: var(--el-card-border-color);">
                        <div v-if="isAuth">
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
        <el-card shadow="hover" v-if="rating">
            <el-skeleton
                :loading="loading"
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
                            v-model="rating"
                            allow-half
                            disabled
                        />
                        <el-text size="large">{{ `${rating} / 5` }}</el-text>
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
        v-if="product"
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
    flex-shrink: 0;
}   
.images .image {
    width: 75px;
    height: 75px;
    flex-shrink: 0;
}
.attributes, .rating, .hero-content {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    font-size: 1.25rem;
}
.rating {
    flex-direction: row;
}
.tags {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    max-width: 75%;
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