import type { ShopState } from "@/ts/types";
import { defineStore } from "pinia";

export const useShopStore = defineStore('shop', {
    state: (): ShopState => ({
        shop: {
            id: 0,
            name: "",
            seller: {
                full_name: "",
                code: "",
                type: "self_employed"
            },
            rating: 0
        }
    })
})