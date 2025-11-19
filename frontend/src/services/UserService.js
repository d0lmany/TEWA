import { useUserStore } from "@/stores/userStore";

export default class UserService
{
    constructor(CartService, FavoriteService, AuthService) {
        this.CartService = CartService;
        this.FavoriteService = FavoriteService;
        this.userStore = useUserStore();
        this.AuthService = AuthService;
    }

    async loadData() {
        const result = {};
        const responseCart = await this.CartService.index();
        const responseFavorite = await this.FavoriteService.index();
        const responseUser = await this.AuthService.show();
        
        if (responseCart.success) {
            result.cart = responseCart.data.data;
        } else {
            result.failCart = true;
        }

        if (responseFavorite.success) {
            result.favorite = responseFavorite.data.data;
        } else {
            result.failFavorite = true;
        }

        if (responseUser.success) {
            result.user = responseUser.data;
        } else {
            result.failUser = true;
        }

        return result;
    }

    async loadAndSave() {
        const data = await this.loadData();
        
        if (!data.failCart) {
            this.userStore.setCart(data.cart);
        }
        if (!data.failFavorite) {
            this.userStore.setFavorite(data.favorite);
        }

        if (!data.failUser) {
            this.userStore.setUser(data.user);
        }

        return data.failCart || data.failFavorite || data.failUser;
    }
}