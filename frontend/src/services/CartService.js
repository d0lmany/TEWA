export default class CartService
{
    constructor(API) {
        this.API = API;
    }

    async index(forBackground = true) {
        try {
            const endpoint = forBackground ? '/lowCart' : '/cart';
            const response = await this.API.get(endpoint);

            if (response.status !== 200) {
                throw new Error(response);
            }

            return {
                success: true,
                data: response.data
            };
        } catch (e) {
            return {
                success: false,
                message: e.message || e
            };
        }
    }

    async store(product) {
        const validations = {
            quantity: product.quantity ?? 1,
            isArray: Array.isArray(product.product_attributes),
        };
        const attrs = product.product_attributes.filter(attr => typeof attr === 'number');

        if (validations.quantity && validations.isArray) {
            try {
                const response = await this.API.post('/cart', {
                    ...product,
                    product_attributes: attrs,
                });

                if (response.status === 201) {
                    return {
                        success: true,
                        data: response.data,
                    };
                } else {
                    throw new Error(response);
                }
            } catch (e) {
                return {
                    success: false,
                    message: e.message || e,
                }
            }
        } else {
            return {
                success: false,
                message: 'bad attempt'
            }
        }
    }

    async update(id, count) {
        try {
            const newCount = parseInt(count);
            const response = await this.API.patch(`cart/${id}`, {
                quantity: newCount
            });

            console.log(response)

            if (response.status === 200) {
                return {
                    success: true,
                    data: response.data
                };
            } else {
                throw new Error(response);
            }
        } catch (e) {
            return {
                success: false,
                message: e.message || e
            };
        }
    }

    async destroy(id) {
        try {
            const response = await this.API.delete(`/cart/${id}`);

            if (response.status === 204) {
                return { success: true };
            } else {
                throw new Error(response);
            }
        } catch (e) {
            return {
                success: false,
                message: e.message || e
            };
        }
    }
}