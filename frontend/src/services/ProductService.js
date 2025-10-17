export default class ProductService
{
    constructor(API) {
        this.API = API;
    }

    async index(params = {}, page = 1) {
        try {
            const response = await this.API.get('/products', {
                params: {
                    ...params,
                    page: page,
                }
            });

            if (response.status === 200) {
                return {
                    success: true,
                    data: response.data.data,
                    pagination: {
                        current_page: response.data.current_page,
                        last_page: response.data.last_page,
                        total: response.data.total,
                        per_page: response.data.per_page
                    }
                };
            } else {
                throw new Error('Ошибка при получении товаров');
            }

        } catch (e) {
            return {
                success: false,
                message: e.response?.data?.message || e.message || e || 'Произошла ошибка при загрузке товаров'
            };
        }
    }

    async show(id) {
        try {
            const response = await this.API.get(`/products/${id}`);

            if (response.status === 200) {
                return {
                    success: true,
                    data: response.data
                };
            } else if (response.status === 404) {
                throw new Error('not found');
            } else {
                throw new Error('Не удалось загрузить товар');
            }
        } catch (e) {
            return {
                success: false,
                message: e.message || e
            };
        }
    }
}