<script setup>
import { ElMessage } from 'element-plus';
import { ArrowRight, Share, Shop, InfoFilled, StarFilled, ShoppingBag, Star } from '@element-plus/icons-vue'
import { computed, inject, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import ReviewCard from '@/components/cards/ReviewCard.vue';
import { ProductService } from '@/services/ProductService';

const route = useRoute();
const API = inject('API');
const product = ref(null);
const loading = ref(true);
const storageURL = inject('storageURL');
const i18n = inject('i18n');
const currency = inject('currency');
const {isAuthenticated, user} = inject('authState');
const price = ref({
    default: 0,
    current: 0,
    discount: 0,
});
const checkedAttributes = ref({});
const isAddedToFav = ref(false);
const isAddedToCart = ref(false);

const copyToClipBoard = async (target) => {
    try {
        await navigator.clipboard.writeText(target);
        ElMessage.success(`Успешно скопировано!`);
    } catch (e) {
        ElMessage.error(`Не удалось скопировать "${target}": ${e}`);
    }
}
const copyURL = () => {
    copyToClipBoard(window.location.href);
}
const getPhotoPath = (filename) => {
    return `${storageURL}/products/${filename}`;
}
const makePretty = (key) => {
    const word = i18n.translate(key);
    return word[0].toUpperCase() + word.slice(1);
}
const getAttributes = (isVariant = false) => {
    if (!product.value?.attributes) return {};

    const attributes = product.value.attributes, result = {};
    Object.keys(attributes).forEach(type => {
        result[type] = attributes[type].filter(attribute => {
            return attribute.is_variant == isVariant
        });
        if (result[type].length == 0) delete result[type];
    });

    return result;
}
const getAvatarPath = (filename) => {
    return `${storageURL}/avatars/${filename}`;
}

const updateCurrentPrice = computed(() => {
    const data = getAttributes(true);
    let impact = 0;
    Object.keys(data).forEach(type => {
        const price = data[type].filter(attr => attr.attr_value == checkedAttributes.value[type])[0].price;
        impact += parseFloat(price);
    })
    const preTotal = price.value.default + impact;
    price.value.current = price.value.discount === null ? preTotal:
    preTotal * (1 - price.value.discount / 100);
    return price.value.current;
})
const getAlbum = computed(() => {
    if (!product.value?.details) return [];

    const album = JSON.parse(product.value.details.album || '[]');
    return [...album, product.value.photo].map(url => getPhotoPath(url)).reverse();
})
const getRating = computed(() => {
    return parseFloat(product.value.feedbacks.rating);
})
const getShopRating = computed(() => {
    return parseFloat(product.value.shop.rating);
})
const i18nPrice = computed(() => {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency
    }).format(updateCurrentPrice.value);
})
const i18nDefault = computed(() => {
    return new Intl.NumberFormat(navigator.language, {
        style: 'currency',
        currency: currency
    }).format(price.value.default);
})

onMounted(async () => {
    try {
        const id = route.params.id;
        
        if (!id) {
            ElMessage.error('ID товара не указан');
            return;
        }

        const response = await API.get(`/products/${id}`);

        if (!response.data) {
            throw new Error('Данные продукта не получены');
        }

        product.value = await response.data.data;
        price.value.default = parseFloat(product.value.price.base_price);
        price.value.current = parseFloat(product.value.price.base_price);
        price.value.discount = parseFloat(product.value.price.discount);

        if (product.value?.attributes) {
            Object.keys(product.value.attributes).forEach(type => {
                const defaultAttr = product.value.attributes[type].find(attr => attr.is_default);
                if (defaultAttr) {
                    checkedAttributes.value[type] = defaultAttr.attr_value;
                }
            });
        }

        console.log(product.value)
    } catch (error) {
        console.error('Ошибка загрузки продукта:', error);
        ElMessage.error('Произошла ошибка при загрузке товара');
    } finally {
        loading.value = false;
    }
});
</script>
<template>
<main>
    <article v-if="product">
        <el-breadcrumb :separator-icon="ArrowRight">
            <el-breadcrumb-item :to="{ path: '/' }">Главная</el-breadcrumb-item>
            <el-breadcrumb-item
            v-if="product.category?.parent"
            :to="{
                path: '/search',
                query: {
                    category_id: product.category.parent.id,
                    category: product.category.parent.name
                }
            }">{{ product.category.parent.name }}</el-breadcrumb-item>
            <el-breadcrumb-item
            :to="{
                path: '/search',
                query: {
                    category_id: product.category.id,
                    category: product.category.name
                }
            }">{{ product.category.name }}</el-breadcrumb-item>
            <el-breadcrumb-item>{{ product.name }}</el-breadcrumb-item>
        </el-breadcrumb>
        <el-card shadow="hover">
        <div class="hero">
            <div class="image-box">
                <div class="images">
                    <el-image
                    class="image"
                    v-if="product.details"
                    v-for="photo in getAlbum"
                    fit="cover"
                    lazy
                    :src="photo"/>
                </div>
                <div class="image">
                    <el-image
                    style="height: 400px; min-width: 300px;"
                    lazy
                    fit="cover"
                    show-progress
                    :src="getPhotoPath(product.photo)"
                    :preview-src-list="product.details ? getAlbum : []"
                    />
                </div>
            </div>
            <div class="hero-content">
                <h1>{{product.name}}</h1>
                <div class="flex gap">
                    <div class="rating">
                        <el-icon
                        :size="24"
                        color="#F7BA2A"
                        ><StarFilled/></el-icon>
                        {{ getRating }}
                    </div>
                    <el-text size="large">Отзывы: {{product.feedbacks.reviews.length}}</el-text>
                </div>
                <div class="attributes">
                    <div class="flex gap" v-for="(value, key) in getAttributes(true)">
                        <el-tag size="large" type="info"><el-text size="large">
                            {{ makePretty(key) + ':' }}
                        </el-text></el-tag>
                        <el-radio-group
                        v-model="checkedAttributes[key]">
                            <el-radio-button
                            v-for="obj in value"
                            :value="obj.attr_value"
                            >
                                {{obj.attr_value}}
                            </el-radio-button>
                        </el-radio-group>
                    </div>
                </div>
                <div class="flex low gap">
                    <div class="flex gap" v-for="(value, key) in getAttributes(false)">
                        <el-tag size="large" type="info">
                            <el-text size="large">
                                {{ makePretty(key) + ':' }}
                            </el-text>
                        </el-tag>
                        <el-text size="large">{{ value[0].attr_value }}</el-text>
                    </div>
                </div>
                <div class="tags">
                    <el-tag size="large" v-for="tag in JSON.parse(product.tags)">{{ tag }}</el-tag>
                </div>
            </div>
        </div>
        </el-card>
        <el-card shadow="hover" body-class="flex low gap">
            <el-avatar size="large" shape="square" :src="getAvatarPath(product.shop.avatar)">
                <el-icon :size="24"><Shop /></el-icon>
            </el-avatar>
            <div class="shop">
                <div class="flex low gap">
                    <b>{{product.shop.name}}</b>
                    <el-popover placement="top" :width="175">
                        <template #reference>
                            <el-icon :size="20"><InfoFilled /></el-icon>
                        </template>
                        <b>{{`${makePretty(product.shop.seller.type).toUpperCase()}: ${product.shop.seller.full_name}.`}}</b>
                        {{'ИНН/ОГРН: ' + product.shop.seller.code}}
                    </el-popover>
                </div>
                <div class="flex gap">
                    <el-rate v-model="getShopRating" disabled
                    allow-half :colors="['#99A9BF', '#F7BA2A', '#FF9900']"
                    v-if="getShopRating"/>
                    <el-text v-else size="large">Оценок нет</el-text>
                    <el-button
                    round size="small"
                    @click="$router.push(`/shop/${product.shop.id}`)"
                    >Перейти</el-button>
                </div>
            </div>
        </el-card>
        <div style="display:contents" v-if="product.details">
            <el-card shadow="hover" v-if="product.details.description">
                <div class="card-header">Описание</div>
                <p class="description">
                    {{product.details.description}}
                </p>
            </el-card>
            <el-card shadow="hover" v-if="product.details.application">
                <div class="card-header">Способ применения</div>
                <p class="description">
                    {{product.details.application}}
                </p>
            </el-card>
        </div>
        <el-card shadow="hover" v-if="product.feedbacks.rating">
            <div class="card-header">
                Отзывы
                <el-tag effect="dark"
                >{{product.feedbacks.reviews.length}}</el-tag>
            </div>
            <div class="reviews">
                <ReviewCard v-for="review in product.feedbacks.reviews" :review="review"/>
            </div>
        </el-card>
    </article>
    <aside v-if="product">
        <div class="flex gap">
            <el-button @click="copyToClipBoard(product.id)">
                Артикул: {{ product.id }}
            </el-button>
            <el-button @click="copyURL">
                Поделиться
                <el-icon class="el-icon--right">
                    <Share/>
                </el-icon>
            </el-button>
        </div>
        <el-card shadow="hover" v-if="product.status == 'on'">
            <div class="price-card">
                <div class="card-header">
                    {{ i18nPrice }}
                    <s>{{ i18nDefault }}</s>
                </div>
                <div class="flex" v-if="isAuthenticated">
                    <el-button type="primary" round @click="isAddedToCart = !isAddedToCart">
                        <el-icon :size="20" class="el-icon--left">
                            <ShoppingBag/>
                        </el-icon>
                        <span v-if="isAddedToCart">В корзину</span>
                        <span v-else>В корзине</span>
                    </el-button>
                    <el-button round @click="isAddedToFav = !isAddedToFav">
                        <div v-if="isAddedToFav" style="display:contents">
                            <el-icon :size="20" class="el-icon--left">
                                <StarFilled color="var(--el-color-primary)"/>
                            </el-icon>
                            В избранном
                        </div>
                        <div v-else style="display:contents">
                            <el-icon :size="20" class="el-icon--left">
                                <Star/>
                            </el-icon>
                            В избранное
                        </div>
                    </el-button>
                </div>
            </div>
        </el-card>
        <el-card shadow="hover" v-else>
            <div class="card-header">
                Этот товар не продаётся.
            </div>
        </el-card>
                    <!--el-rate v-model="getRating" disabled
                    allow-half :colors="['#99A9BF', '#F7BA2A', '#FF9900']"
                    /-->
    </aside>
    <el-empty v-else description="Загружается..." class="span"/>
</main>

</template>
<style scoped>
main {
    padding: 1rem 2rem;
    display: grid;
    grid-template-columns: 75% 1fr;
    gap: 2rem;
}
article, aside {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
s {
    color: var(--el-text-color-secondary);
    font-size: 1rem;
}
.image-box {
    display: flex;
    gap: .5rem;
}
.images {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    max-height: 400px;
    overflow-y: auto;
    max-width: 75px;
    flex-shrink: 0;
}
.image-box > .image {
    border-radius: 1rem;
    overflow: hidden;
    width: fit-content;
    height: 400px;
}
.images .image {
    width: 75px;
    height: 75px;
    border-radius: 1rem;
    flex-shrink: 0;
}
.hero {
    display: flex;
    gap: 1rem;
}
.hero h1 {
    margin: 0;
}
.hero-content {
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.attributes {
    gap: .5rem;
    display: flex;
    flex-direction: column;
}
.tags {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    max-width: 75%;
}
.shop {
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.card-header {
    font-size: 1.5rem;
    font-weight: 600;
}
.card-header + .description {
    margin-bottom: 0;
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
.rating {
    display: flex;
    align-items: center;
    gap: .25rem;
    font-size: 1.25rem;
}
.price-card {
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
</style>