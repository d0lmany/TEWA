import { defineStore } from "pinia";

export const useUserStore = defineStore('user', {
    state: () => ({
        isAuth: false,
        cart: [],
        favorite: [],
    }),
    actions: {
        setIsAuth (condition) {
            this.logout();
            this.isAuth = condition;
        },
        setCart (cart) {
            this.cart = cart;
        },
        getProductFromCart (id, attrs) {
            const normalizedAttrs = attrs?.sort() || [];
            
            const existingItem = this.cart.find(item =>
                item.product_id === id &&
                JSON.stringify(item.product_attributes?.sort()) === JSON.stringify(normalizedAttrs)
            );
            
            return existingItem;
        },
        addToCart (item) {
            const existingItem = this.getProductFromCart(item.product_id, item.product_attributes);
            
            if (existingItem) {
                existingItem.quantity = item.quantity;
            } else {
                this.cart.push(item);
            }
        },
        updateCartItem (itemId, newQuantity) {
            const item = this.cart.find(item => item.id === itemId);
            if (item) {
                item.quantity = newQuantity;
            }
        },
        removeFromCart (itemId) {
            this.cart = this.cart.filter(item => item.id !== itemId);
        },
        setFavorite (favorite) {
            this.favorite = favorite;
        },
        getFavoriteItem (productId) {
            for (const list of this.favorite || []) {
                const item = list.items?.find(item => item.product_id === productId);
                if (item) {
                    return {
                        item: item,
                        list_name: list.name,
                        list_id: list.id
                    };
                }
            }
            return null;
        },
        addToFavorite(item) {
            if (this.getFavoriteItem(item.product_id)) {
                console.log('Товар уже в избранном');
                return false;
            }
            
            const targetList = this.favorite.find(list => list.name === '__favorite__');
            if (!targetList) {
                console.error('Список не найден');
                return false;
            }
            
            if (!targetList.items) {
                targetList.items = [];
            }
            
            targetList.items.push(item);
            
            return item;
        },
        removeFromFavorite (productId) {
            let removed = false;
            
            this.favorite.forEach(list => {
                if (list.items && Array.isArray(list.items)) {
                    const initialLength = list.items.length;
                    list.items = list.items.filter(item => item.product_id !== productId);
                    if (list.items.length < initialLength) {
                        removed = true;
                    }
                }
            });
            
            return removed;
        },
        logout () {
            this.cart = [];
            this.favorite = [];
            this.isAuth = false;
        }
    }
})