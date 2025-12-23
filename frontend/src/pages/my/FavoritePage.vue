<script setup lang="ts">
import type Services from '@/ts/types/Services';
import { computed, inject, reactive, ref, watch } from 'vue';
import { useUserStore } from '@/stores/userStore';
import type { FavoriteList, FavoriteListItem } from '@/ts/entities/Items';
import { ElMessage } from 'element-plus';
import { Delete, EditPen, Refresh, Top, Box } from '@element-plus/icons-vue';
import { createMaxRule, createMinRule, createRequiredRule, type Rules } from '@/ts/utils/FormRules';
import type Filters from '@/ts/types/Filters';
import SearchFilters from '@/components/SearchFilters.vue';
import ProductCard from '@/components/cards/ProductCard.vue';
import type { Product } from '@/ts/entities/Product';
import type { UI } from '@/ts/types/Provides';

const filters = reactive<Filters>({});
const FavoriteService = (inject('services') as Services).favorite;
const lists = reactive<FavoriteList[]>([]);
const userStore = useUserStore();
const activeList = reactive<FavoriteList>({
    id: 0,
    items: [],
    name: '',
    created_at: ''
});
const visibilities = reactive<Record<string, boolean>>({
    listEditorModal: false,
    changeName: false,
    changeList: false,
});
const form = reactive({ name: '' });
const rules: Rules = {
    name: [
        createRequiredRule('Имя списка'),
        createMinRule(2),
        createMaxRule(255),
        {
            validator: (_: any, value: string, callback: Function) => {
                if ('__favorite__' === value) 
                    callback(new Error(''));
                else
                    callback();
            },
            trigger: 'change'
        }
    ]
}
const chosenList = ref<number>(0);
const chosenItem = ref<FavoriteListItem>();
const formRef = ref();
const action = ref<'update' | 'create'>('create');
const formatter = (inject('ui') as UI).dateFormatter;

const loadLists = async () => {
    try {
        const response = await FavoriteService.index();

        if (response.success && response.data) {
            lists.splice(0);
            lists.push(...response.data.data);
            const mainList = lists.find(list => list.name == '__favorite__');
            if (mainList) {
                mainList.name = 'Основной раздел'
                Object.assign(activeList, mainList);
            };
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось загрузить избранное');
    }
}
const updateList = async () => {
    form.name = form.name.trim();
    if (!formRef.value || !await formRef.value.validate()) return;

    if (checkDuplicateName(form.name)) {
        ElMessage.warning('Список с таким именем уже есть');
        return;
    }

    try {
        const response = await FavoriteService.update(chosenList.value, form);

        if (response.success) {
            const targetList = lists.find(list => list.id === chosenList.value);
            if (targetList) {
                ElMessage.success(`Имя списка изменено с '${targetList.name}' на '${form.name}'`);
                targetList.name = form.name;
                chosenList.value = 0;
                visibilities.formForName = false;
            }
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(
            error instanceof Error ?
                error.message === 'Unprocessable Entity' ? 'Список с таким именем уже есть' : error.message
            : 'Не удалось создать список'
        )
    }
}
const clearList = async () => {
    try {
        const response = await FavoriteService.clear(chosenList.value);

        if (response.success) {
            const targetList = lists.find(list => list.id === chosenList.value);
            if (targetList) {
                ElMessage.success(`Список '${targetList.name}' очищен`);
                targetList.items = [];
                chosenList.value = 0;
            }
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось очистить список');
    }
}
const destroyList = async () => {
    try {
        const response = await FavoriteService.destroy(chosenList.value);

        if (response.success) {
            const targetListIndex = lists.findIndex(list => list.id === chosenList.value);
            if (targetListIndex !== -1) {
                ElMessage.success(`Список '${lists[targetListIndex]?.name}' удалён`);
                lists.splice(targetListIndex, 1);
                chosenList.value = 0;
            }
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось удалить список');
    }
}
const storeList = async () => {
    form.name = form.name.trim();
    if (!formRef.value || !await formRef.value.validate()) return;

    if (checkDuplicateName(form.name)) {
        ElMessage.warning('Список с таким именем уже есть');
        return;
    }

    try {
        const response = await FavoriteService.store(form);

        if (response.success && response.data) {
            const newList: FavoriteList = {
                ...response.data.data,
                items: [],
            };
            lists.push(newList);
            visibilities.formForName = false;
            ElMessage.success(`Список '${form.name}' создан`);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(
            error instanceof Error ?
                error.message === 'Unprocessable Entity' ? 'Список с таким именем уже есть' : error.message
            : 'Не удалось создать список'
        )
    }
}
const checkDuplicateName = (listName: string) => lists.find(list => list.name === listName);
const deleteItem = async (item: FavoriteListItem) => {
    try {
        const response = await FavoriteService.toggle(item?.product?.id || 0, activeList.id);

        if (response.success) {
            ElMessage.success('Удалено из избранного');
            userStore.removeFromFavorite(item.id,
                activeList.name === 'Основной раздел' ?
                '__favorite__' : activeList.name);
            const itemIndex = activeList.items.findIndex(item => item.id === item.id);
            if (itemIndex !== -1) activeList.items.splice(itemIndex, 1);
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось удалить товар из избранного');
    }
}
const changeList = async (list_id: number, list_name: string) => {
    try {
        const response = await FavoriteService.changeList(chosenItem.value?.id || 0, list_id);

        if (response.success && chosenItem.value) {
            const listName = activeList.name === 'Основной раздел' ? '__favorite__' : activeList.name;
            ElMessage.success('Успешно перемещено');
            userStore.removeFromFavorite(chosenItem.value.id, listName);
            userStore.addToFavorite(chosenItem.value, list_name);
            const itemIndex = activeList.items.findIndex(item => item.id === chosenItem.value?.id);
            activeList.items.splice(itemIndex, 1);
            const targetList = lists.find(list => list.id === list_id);
            targetList?.items.push(chosenItem.value);

            chosenItem.value = undefined;
        } else {
            console.error(response);
            throw new Error(response.message);
        }
    } catch (error) {
        ElMessage.error(error instanceof Error ? error.message : 'Не удалось переместить товар');
    }
}
const formattedDate = (dateString: string) => {
    if (!dateString) return '—'
    const date = new Date(dateString)
    return isNaN(date.getTime()) ? '—' : formatter.format(date)
}

const filteredItems = computed(() => {
    return activeList.items.filter(item => {
        const product = item?.product;
        if (!product) return false;
        
        const rating = product.feedbacks?.rating || 0;
        const price = product.price?.final_price || 0;
        const tags = JSON.parse(product.tags || '[]');
        
        if (rating <= (filters.min_rating || 0)) return false;
        
        if (price <= (filters.min_price || 0)) return false;
        if (price >= (filters.max_price || Infinity)) return false;
        
        if (filters.category_id && product.category?.id !== filters.category_id) return false;
        
        if (filters.tags?.length) {
            const hasTag = filters.tags.some(tag => tags.includes(tag));
            if (!hasTag) return false;
        }
        
        return true;
    });
});

watch(
    () => userStore.getFavoriteTotalLength(),
    async () => await loadLists()
)

loadLists();
</script>
<template>
    <div class="container">
        <div>
            <aside>
                <h1 class="section-header">Избранное</h1>
                <el-button-group class="buttons">
                    <el-button
                        v-for="list in lists"
                        :key="list.id"
                        text
                        :type="activeList.id === list.id ? 'primary' : 'default'"
                        @click="Object.assign(activeList, list)"
                    >{{ `${list.name} (${list.items.length})` }}</el-button>
                    <el-button
                        text
                        @click="visibilities.listEditorModal = true"
                    >Редактировать списки</el-button>
                </el-button-group>
            </aside>
            <aside style="margin-top: 1rem">
                <h2 class="section-header">Фильтры</h2>
                <search-filters
                    v-model="filters"
                />
                <el-button @click="(Object.keys(filters) as Array<keyof typeof filters>).forEach(filter => delete filters[filter])">Сбросить фильтры</el-button>
            </aside>
        </div>
        <main>
            <h3 class="section-header">{{ activeList.name }}<span v-if="activeList.name !== 'Основной раздел'"> - {{ formattedDate(activeList.created_at) }}</span></h3>
            <section v-if="filteredItems.length">
                <el-popover v-for="item in filteredItems" width="max-content">
                    <div class="popover flex col gap">
                        <div class="flex gap">
                            <el-button @click="visibilities.changeList = true; chosenItem = item">Сменить список</el-button>
                            <el-button @click="deleteItem(item)">Удалить</el-button>
                        </div>
                        <div>{{ `Добавлено: ${formatter.format(new Date(item.added_at))}` }}</div>
                    </div>
                    <template #reference>
                    <product-card
                        :product="(item.product as unknown as Product)"
                        :key="item.product?.id"
                    />
                    </template>
                </el-popover>
            </section>
            <el-empty v-else description="Тут ничего нет"/>
        </main>
        <el-backtop>
            <el-icon :size="24"><Top/></el-icon>
        </el-backtop>
        <el-dialog
            title="Редактировать списки"
            v-model="visibilities.listEditorModal"
            center
            width="40%"
            style="border-radius: 1rem"
            :show-close="false"
        >
            <section>
                <div class="lists no-m" v-if="lists.filter(list => list.name !== 'Основной раздел').length">
                    <div
                        v-for="list in lists.filter(list => list.name !== 'Основной раздел')"
                        :key="list.id"
                    >
                        <el-text size="large">{{ list.name }}</el-text>
                        <el-button-group>
                            <el-popover>
                                <template #reference>
                                <el-button text @click="chosenList = list.id; action = 'update'; visibilities.formForName = true">
                                    <el-icon class="el-icon--left" :size="18"><edit-pen/></el-icon>
                                </el-button>
                                </template>
                                <div style="text-align: center">Переименовать список</div>
                            </el-popover>
                            <el-popover>
                                <template #reference>
                                <el-button text @click="chosenList = list.id; clearList()">
                                    <el-icon class="el-icon--left" :size="18"><refresh/></el-icon>
                                </el-button>
                                </template>
                                <div style="text-align: center">Очистить список</div>
                            </el-popover>
                            <el-popover>
                                <template #reference>
                                <el-button text @click="chosenList = list.id; destroyList()">
                                    <el-icon class="el-icon--left" :size="18"><delete/></el-icon>
                                </el-button>
                                </template>
                                <div style="text-align: center">Удалить список</div>
                            </el-popover>
                        </el-button-group>
                    </div>
                </div>
                <el-empty
                    v-else
                    description="Списков нет"
                />
                <div class="flex">
                    <el-button @click="visibilities.listEditorModal = false">Отмена</el-button>
                    <el-button type="primary" @click="action = 'create'; visibilities.formForName = true">
                        <el-icon class="el-icon--left" :size="18"><edit-pen/></el-icon>
                        Добавить список
                    </el-button>
                </div>
            </section>
        </el-dialog>
        <el-dialog
            :title="action === 'create' ? 'Создать новый список' : 'Изменить название списка'"
            v-model="visibilities.formForName"
            center
            width="20%"
            style="border-radius: 1rem"
            :show-close="false"
            @closed="form.name = ''"
        >
            <el-form
                label-position="top"
                :model="form"
                :rules="rules"
                ref="formRef"
                @submit.prevent="() => action === 'create' ? storeList() : updateList()"
            >
                <el-form-item prop="name" label="Имя списка">
                    <el-input
                        v-model="form.name"
                        clearable
                        placeholder="Например, 'Это на новый год!'"
                    />
                </el-form-item>
                <el-form-item style="margin-bottom: 0">
                    <div class="flex" style="width: 100%">
                        <el-button @click="visibilities.formForName = false">Отмена</el-button>
                        <el-button
                            type="primary"
                            native-type="submit"
                        >{{ action === 'create' ? 'Создать список' : 'Сменить имя списка' }}</el-button>
                    </div>
                </el-form-item>
            </el-form>
        </el-dialog>
        <el-dialog
            :title="`Переместить ${chosenItem?.product?.name || 'Товар'}`"
            v-model="visibilities.changeList"
            center
            width="30%"
            style="border-radius: 1rem"
            :show-close="false"
        >
            <section class="lists">
                <div
                    v-for="list in lists.filter(list => list.id !== activeList.id)"
                    :key="list.id"
                >
                    <el-text size="large">{{ list.name }}</el-text>
                    <el-button
                        text
                        @click="changeList(list.id, list.name)"
                    >
                        <el-icon class="el-icon--left" :size="18"><box/></el-icon>
                        Переместить
                    </el-button>
                </div>
            </section>
            <div class="flex">
                <el-button @click="visibilities.changeList = false; chosenItem = undefined">Отмена</el-button>
            </div>
        </el-dialog>
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
aside, main {
    background: var(--el-color-primary-light-9);
    border-radius: 1rem;
    padding: 1rem;
    height: min-content;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
main section {
    gap: 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(clamp(200px, 25%, 250px), 1fr));
    content-visibility: auto;
    contain-intrinsic-size: auto 750px;
}
h2, h3 {
    font-size: 1.25rem;
}
h3 span {
    font-size: 1rem;
    font-weight: 400;
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
.lists {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}
.lists > div {
    display: flex;
    justify-content: space-between;
    transition: all .1s;
    border: 1px solid transparent;
    border-radius: .25rem;
    padding-left: 1rem;
    background-color: transparent;
}
.lists > div:hover {
    border-color: var(--el-border-color);
    background-color: var(--el-border-color);
}
.no-m .el-button span i {
    margin: 0;
}
.popover .el-button + .el-button {
    margin: 0;
}
</style>