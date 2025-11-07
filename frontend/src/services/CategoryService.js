export default class CategoryService
{
    constructor(API) {
        this.API = API;
    }

    async getRawCategories() {
        try {
            const response = await this.API.get('/categories');
            if (response.status === 200) {
                return {
                    success: true,
                    data: response.data
                };
            } else {
                throw 'Ошибка при загрузке категорий';
            }
        } catch (e) {
            return {
                success: false,
                message: e
            };
        }
    }

    async prepare() {
        try {
            const raw = await this.getRawCategories();
            if (raw.success) {
                const grouped = await Object.groupBy(raw.data, category => category.parent_id ?? 'root');
                const result = {};

                if (grouped.root) {
                    grouped.root.forEach(category => {
                        result[category.name] = grouped[category.id] || [];
                    });
                }

                return {
                    success: true,
                    data: result,
                };
            } else {
                throw new Error('Ошибка при загрузке категорий');
            }
        } catch (e) {
            return {
                success: false,
                message: e.message,
            }
        }
    }

    async loadOptions() {
        try {
            const response = await fetch('/assets/json/tags.json');
            
            if (!response.ok) {
                throw response;
            }

            const data = await response.json();

            return {
                success: true,
                data: data.options,
            };
        } catch (e) {
            console.error(e);

            return {
                success: false,
                message: e.message || e.status || e
            }
        }
    }
}