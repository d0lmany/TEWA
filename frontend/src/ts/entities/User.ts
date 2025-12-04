import type { CartItem, FavoriteLists } from "@/ts/entities/Items";

export interface UserData {
   isAuth: boolean;
   cart: CartItem[];
   favorite: FavoriteLists,
   user: User;
}

export interface User {
   name: string;
   picture: string | null;
   birthday: string;
}

export interface Seller {
   full_name: string,
   code: string,
   type: 'self_employed' | 'individual' | 'LLC'
}