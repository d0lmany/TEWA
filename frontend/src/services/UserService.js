import { useUserStore } from "@/stores/userStore";

export default class UserService
{
    constructor(CartService, FavoriteService) {
        this.CartService = CartService;
        this.FavoriteService = FavoriteService;
        this.userStore = useUserStore();
    }

    async loadData() {
        const result = {};
        const responseCart = await this.CartService.index();
        const responseFavorite = await this.FavoriteService.index();
        
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

        return data.failCart || data.failFavorite;
    }
}