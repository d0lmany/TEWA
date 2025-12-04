import type { Seller } from "@/ts/entities/User"

export interface Shop {
   id: number,
   name: string,
   picture?: string,
   description?: string,
   seller: Seller,
   rating: DoubleRange,
}