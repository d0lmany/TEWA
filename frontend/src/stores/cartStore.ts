import type { CartItem } from "@/ts/entities/Items";
import { defineStore } from "pinia";

export const useCartStore = defineStore('cart', {
    state: (): { cart: Map<number, CartItem> } => ({
        cart: new Map(),
    }),
    actions: {
        arraysEqualSorted(a: number[], b: number[]) {
            if (a.length !== b.length) return false;
            for (let i = 0; i < a.length; i++) {
                if (a[i] !== b[i]) return false;
            }
            return true;
        },
        getItemByProductId(productId: number, attrs?: number[]) {
            const normalizedAttrs = attrs ? [...attrs].sort() : [];

            for (const item of this.cart.values()) {
                if (item.product_id !== productId) continue;

                const itemAttrs = item.product_attributes ?? [];
                const sortedItemAttrs  = [...itemAttrs].sort();

                if (this.arraysEqualSorted(sortedItemAttrs, normalizedAttrs)) {
                    return item;
                }
            }

            return undefined;
        },
        getItem(id: number, attrs?: number[]) {
            const normalizedAttrs = attrs ? [...attrs].sort() : [];

            for (const item of this.cart.values()) {
                if (item.id !== id) continue;

                const itemAttrs = item.product_attributes ?? [];
                const sortedItemAttrs  = [...itemAttrs].sort();

                if (this.arraysEqualSorted(sortedItemAttrs, normalizedAttrs)) {
                    return item;
                }
            }

            return undefined;
        },
        addItem(item: CartItem) {
            const existingItem = this.getItemByProductId(item.product_id, item.product_attributes);

            if (existingItem) {
                this.cart.set(existingItem.id, {
                    ...existingItem,
                    quantity: item.quantity
                })
            } else {
                this.cart.set(item.id, item);
            }
        },
        removeItem(id: number) {
            this.cart.delete(id);
        },
        updateItem(id: number, newQuantity: number) {
            const item = this.cart.get(id);

            if (item) {
                this.cart.set(id, {
                    ...item,
                    quantity: newQuantity
                });
            }
        },
        set(cart: CartItem[]) {
            cart.forEach(item => this.addItem(item));
        },
        clear() {
            this.cart.clear();
        }
    },
    getters: {
        length: state => state.cart.size,
        asArray: state => state.cart.values(),
    }
});