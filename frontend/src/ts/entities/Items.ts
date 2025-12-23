import type { FullProduct, Product } from "@/ts/entities/Product"
import type { Category } from "./Category"

export interface CartItem {
    id?: number,
    quantity: number,
    product_id: number,
    product_attributes?: number[],
}

export interface CartProduct extends CartItem {
    product: FullProduct,
    checked: boolean,
    isFavorite: boolean,
}

export interface FavoriteList {
    id: number,
    name: string,
    items: FavoriteListItem[],
    created_at: string
}

export interface FavoriteListItem {
    id: number,
    list_id: number,
    product_id: number,
    product?: Product & {
        category: Category;
    };
    added_at: string
}