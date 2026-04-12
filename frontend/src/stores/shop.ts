import type { FullProduct, Shop } from "@/ts/entities"
import { defineStore } from "pinia"
import { computed, reactive, ref } from "vue"

export const useShopStore = defineStore('shop', () => {
    // getters
    const emptyProduct = computed(() => ({
        name: '', quantity: 0,
        photo: '', price: { discount: 0, base_price: 0 },
        status: 'draft', category: { id: 0, name: '' }, details: {
            description: '', application: '',
        }, tags: [], id: 0
    }) as unknown as Partial<FullProduct>)
    // state
    const shop = reactive<Shop>({
        id: 0, name: '', rating: 0,
        seller: { full_name: '', code: '', type: 'self_employed' },
    })
    const currentMode = ref<'create' | 'update'>('create')
    const currentProduct = reactive({...emptyProduct.value})
    const createFormVisible = ref(false)
    // actions
    const clearCurrentProduct = () => Object.assign(currentProduct, emptyProduct.value)
    return {
    // state
        shop, currentMode, currentProduct, createFormVisible,
    // getters
        emptyProduct,
    // actions
        clearCurrentProduct,
    }
})