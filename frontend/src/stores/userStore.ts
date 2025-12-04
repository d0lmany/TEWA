import type { CartItem, FavoriteListItem, FavoriteLists } from "@/ts/entities/Items";
import type { User, UserData } from "@/ts/entities/User";
import { defineStore } from "pinia";

export const useUserStore = defineStore('userData', {
   state: (): UserData => ({
      isAuth: false,
      cart: [],
      favorite: {},
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
      setFavorite (favorite: FavoriteLists) {
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
      removeCartItem (id: number) {
         this.cart = this.cart.filter(item => item.id !== id);
      },
      getFavoriteItem (id: number): FavoriteListItem | undefined {
         for (const [listName, list] of Object.entries(this.favorite)) {
            const item = list.items?.find(item => item.product_id === id);
            if (item) {
               return item;
            }
         }
      },
      addToFavorite (item: FavoriteListItem) {
         this.favorite['__favorite__']?.items.push(item);
      },
      removeFromFavorite (id: number) {
         if (this.favorite['__favorite__'] && this.favorite['__favorite__'].items) {
            this.favorite['__favorite__'].items =
               this.favorite['__favorite__'].items.filter(item => item.id === id)
         }
      },
      logout () {
         this.user = {
            name: '', picture: '', birthday: ''
         };
         this.favorite = {};
         this.cart = [];
         this.isAuth = false;
      }
   }
});