import type { FavoriteList, FavoriteListItem } from "@/ts/entities/Items";
import { defineStore } from "pinia";

export const useFavoriteStore = defineStore('favorite', {
    state: (): { favorite: Map<number, FavoriteList> } => ({
        favorite: new Map()
    }),
    actions: {
        addItem(item: FavoriteListItem, listId: number) {
            this.favorite.get(listId)?.items.push(item);
        },
        removeItem(id: number, listId: number) {
            const list = this.favorite.get(listId);

            if (!list?.items) return;

            const index = list.items.findIndex(item => item.id === id);
            if (index !== -1) {
                list.items.splice(index, 1);
            }
        },
        removeItemByProductId(productId: number) {
            for (const list of this.favorite.values()) {
                const index = list.items?.findIndex(item => item.product_id === productId);
                if (index !== -1) {
                    list.items.splice(index, 1);
                    break;
                };
            }
        },
        getList(listId: number): FavoriteList | undefined {
            return this.favorite.get(listId);
        },
        getItem(id: number): FavoriteListItem | undefined {
            for (const list of this.favorite.values()) {
                const found = list.items?.find(item => item.id === id);
                if (found) return found;
            }
            return undefined;
        },
        getItemByProductId(productId: number): FavoriteListItem | undefined {
            for (const list of this.favorite.values()) {
                const found = list.items?.find(item => item.product_id === productId);
                if (found) return found;
            }
            return undefined;
        },
        set(favorite: FavoriteList[]) {
            favorite.forEach(list => this.favorite.set(list.id, list));
        },
        clear() {
            this.favorite.clear();
        }
    },
    getters: {
        length: state => [...state.favorite.values()].reduce((acc, list) => acc + (list?.items?.length || 0), 0),
        asArray: state => state.favorite.values(),
    }
});