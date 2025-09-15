export class ProductService
{
    constructor(isAuth, API, productId) {
        this.isAuth = isAuth;
        this.API = API;
        this.productId = productId;
    }

    async addToCart(attributes = []) {
        try {
            const request = {
                product_id: this.productId,
                product_attributes: JSON.stringify(attributes),
            };

            const response = await this.API.post('/cart', request);

            if (response.status === 201) return 'added';
            else throw response;

        } catch (e) {
            return e;
        }
    }

    async reduceProduct() {
        try {
            const response = await this.API.patch('/cart/reduce', { product_id: this.productId });

            if (response.status === 204) return 'deleted';

            if (response.status === 200) return 'reduced';

            return response.data;

        } catch (e) {
            return e;
        }
    }

    async removeFromCart() {
        try {
            const response = await this.API.delete(`/cart/${this.productId}`);

            if (response.status === 204) return 'deleted';
            
            return response.data;

        } catch (e) {
            return e;
        }
    }
}