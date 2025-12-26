import type { Seller } from "@/ts/entities/User"
import type { Product } from "@/ts/entities/Product";
import type { Category } from '@/ts/entities/Category';

export interface Shop {
    id: number,
    name: string,
    picture?: string,
    description?: string,
    seller: Seller,
    rating: number,
    products?: (Product & { category: Category })[],
    reviewsCount?: number
}