import type { Category } from "../entities";

export interface RawCategory {
    name: string,
    parent_id?: number | null,
}
export interface CategoriesContext {
    createForm: RawCategory,
    updateForm: RawCategory & { selectedCategoryId?: number, selectedCategory?: Omit<Category, 'parent_id'> },
    deleteForm: { id?: number },
    loading: Record<string, boolean>,
}