import type { FavoriteListItem, FavoriteList } from "@/ts/entities/Items";
import type { User, UserData } from "@/ts/entities/User";
import { defineStore } from "pinia";
import { useCartStore } from "./cartStore";
import { useFavoriteStore } from "./favoriteStore";

export const useUserStore = defineStore('userData', {
    state: (): UserData => ({
        isAuth: false,
        user: {
            name: '',
            picture: '',
            birthday: '',
        }
    }),
    actions: {
        login(user: User) {
            this.user = user;
            this.isAuth = true;
        },
        logout() {
            this.user = {
                name: '', picture: '', birthday: ''
            };
            useFavoriteStore().clear();
            useCartStore().clear();
            this.isAuth = false;
        },
    }
});