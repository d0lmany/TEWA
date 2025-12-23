import type { Category } from "@/ts/entities/Category"
import type { Review } from "@/ts/entities/Review"
import type { Shop } from "@/ts/entities/Shop"

export interface Product {
    id: number,
    name: string,
    quantity: number,
    photo: string,
    price: {
        discount: number,
        base_price: number,
        final_price: number,
        total?: number
    },
    tags: string,
    feedbacks: {
        rating: number,
        reviews?: Review[],
    },
    status: 'on' | 'off' | 'draft'
}

export interface FullProduct extends Product {
    category: Category,
    details?: {
        id: number,
        product_id: number,
        album?: string[],
        description: string,
        application: string,
    },
    attributes?: Record<string, ProductAttribute[]>,
    shop: Shop,
}

export interface ProductAttribute {
    id: number,
    attr_value: string,
    price: number,
    is_variant: boolean,
    is_default: boolean,
}