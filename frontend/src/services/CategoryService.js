export default class CategoryService
{
    constructor(API) {
        this.API = API;
    }

    async getRawCategories() {
        try {
            const response = await this.API.get('/categories');
            if (response.status === 200) {
                return response.data;
            } else {
                throw 'Ошибка при загрузке категорий';
            }
        } catch (e) {
            return e;
        }
    }

    async prepare() {
        try {
            const raw = await this.getRawCategories();
            const grouped = Object.groupBy(raw, cat => cat.parent_id ?? 'root');
            const result = {};

            grouped.root.forEach(category => {
                result[category.name] = grouped[category.id] || [];
            });

            return result;
        } catch (e) {
            return { error: e.message }
        }
    }

    async loadTags(all) {
        try {
            const response = await fetch('/assets/json/tags.json');

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const tags = await response.json();
            return all
                ? tags
                : tags.options;
        } catch (error) {
            console.error(error);
            return {error};
        }
    }
}