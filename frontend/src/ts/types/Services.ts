import type ApiService from "@/ts/services/ApiService";
import type UserService from "@/ts/services/UserService";
import type CategoryService from '@/ts/services/CategoryService';
import type ProductService from "@/ts/services/ProductService";
import type I18n from "@/ts//services/I18n";
import type CartService from "@/ts/services/CartService";
import type FavoriteService from "@/ts/services/FavoriteService";
import type ClaimService from "@/ts/services/ClaimService";

export default interface Services {
   api: ApiService,
   user: UserService,
   category: CategoryService,
   product: ProductService,
   i18n: I18n,
   cart: CartService,
   favorite: FavoriteService,
   claim: ClaimService,
}