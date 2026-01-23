export interface Category {
    id: number,
    name: string,
    parent_id?: number | null,
    parent?: Category
}

export type GroupedCategories = Record<string, Category[]>

export interface Tag {
    id: number,
    name: string,
    description: string
}