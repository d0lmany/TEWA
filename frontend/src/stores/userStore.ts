import type { CartItem, FavoriteListItem, FavoriteList } from "@/ts/entities/Items";
import type { User, UserData } from "@/ts/entities/User";
import { defineStore } from "pinia";

export const useUserStore = defineStore('userData', {
    state: (): UserData => ({
        isAuth: false,
        cart: [],
        favorite: [],
        user: {
            name: '',
            picture: '',
            birthday: '',
        }
    }),
    actions: {
        login (user: User) {
            this.user = user;
            this.isAuth = true;
        },
        setCart (cart: CartItem[]) {
            this.cart = cart;
        },
        setFavorite (favorite: FavoriteList[]) {
            this.favorite = favorite;
        },
        getProductFromCart (id: number, attrs?: number[]): CartItem | undefined {
            const normalizedAttrs = attrs?.sort() || [];

            const existingItem = this.cart.find(item => 
                item.product_id === id &&
                JSON.stringify(item.product_attributes?.sort())
                    === JSON.stringify(normalizedAttrs)
            );

            return existingItem;
        },
        addToCart (item: CartItem) {
            const existingItem = this.getProductFromCart(item.product_id, item.product_attributes);

            if (existingItem) {
                existingItem.quantity = item.quantity;
            } else {
                this.cart.push(item);
            }
        },
        updateCartItem (id: number, newQuantity: number) {
            const item = this.cart.find(item => item.id === id);

            if (item) item.quantity = newQuantity;
        },
        removeCartItem (itemId: number) {
            this.cart = this.cart.filter(item => item.id !== itemId);
        },
        getFavoriteItem (productId: number): FavoriteListItem | undefined {
            let item = undefined;
            for (const list of this.favorite) {
                const definedItem = list.items.find(item => item.product_id === productId);
                if (definedItem) {
                    item = definedItem;
                    break;
                }
            }
            return item;
        },
        getListNameById (listId: number): string | undefined {
            return this.favorite.find(list => list.id === listId)?.name
        },
        addToFavorite (item: FavoriteListItem, listName: string = '__favorite__') {
            this.favorite.find(list => list.name === listName)?.items.push(item);
        },
        removeFromFavorite (favItemId: number, listName: string = '__favorite__') {
            const favoriteList = this.favorite.find(list => list.name === listName);

            if (!favoriteList?.items) return;

            const index = favoriteList.items.findIndex(item => item.id === favItemId);
            if (index !== -1) {
                favoriteList.items.splice(index, 1);
            }
        },
        removeFromFavoriteByProductId (productId: number, listName: string = '__favorite__') {
            const favoriteList = this.favorite.find(list => list.name === listName);

            if (!favoriteList?.items) return;

            const index = favoriteList.items.findIndex(item => item.product?.id === productId);
            if (index !== -1) {
                favoriteList.items.splice(index, 1);
            }
        },
        logout () {
            this.user = {
                name: '', picture: '', birthday: ''
            };
            this.favorite = [];
            this.cart = [];
            this.isAuth = false;
        },
        getFavoriteTotalLength (): number {
            return this.favorite.reduce((acc, list) => acc + (list?.items?.length || 0), 0)
        }
    }
});