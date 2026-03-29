import { UserRole, type User, type UserData } from "@/ts/entities";
import { defineStore } from "pinia";
import { useCartStore } from "./cartStore";
import { useFavoriteStore } from "./favoriteStore";

export const useUserStore = defineStore('user', {
    state: (): UserData => ({
        isAuth: false,
        user: {
            name: '',
            picture: '',
            birthday: '',
            role: UserRole.Unsigned,
        },
    }),
    actions: {
        login(user: User) {
            this.user = user;
            this.isAuth = true;
        },
        logout() {
            this.user = {
                name: '', picture: '',
                birthday: '', role: UserRole.Unsigned,
            };
            useFavoriteStore().clear();
            useCartStore().clear();
            this.isAuth = false;
        },
    }
});