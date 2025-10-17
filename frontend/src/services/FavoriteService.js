export default class FavoriteService
{
    constructor(API) {
        this.API = API;
    }

    async index(forBackground = true) {
        try {
            const endpoint = forBackground ? '/lowFavorite' : '/favorite';
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

    async toggle(id) {
        try {
            const response = await this.API.post('/favorite', { product_id: id });
            switch (response.status) {
                case 200:
                    return {
                        success: true,
                        data: response.data,
                        added: false
                    };
                case 201:
                    return {
                        success: true,
                        data: response.data,
                        added: true
                    };
                default:
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