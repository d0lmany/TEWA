import type { CartItem, FavoriteList } from "@/ts/entities/Items";
import type { User } from "@/ts/entities/User";

export interface LoginData {
    email: string,
    password: string,
}

export interface RegistrationData {
    name: string,
    email: string,
    birthday: string,
    password: string,
    password_confirmation: string,
}

export interface UserResponseData {
    cart: CartItem[],
    favorite: FavoriteList[],
    user: User,
    errors: {
        cart: string,
        favorite: string,
        user: string,
    }
}