import type { Address } from "@/ts/entities/Addresses"
import type { Product } from "@/ts/entities/Product"

export interface Order {
    id: number,
    status: OrderStatus,
    total: number,
    is_hidden: boolean,
    created_at: string,
    updated_at: string,
    destination?: Address,
    items: OrderItem[],
    locations: OrderLocation[],
    current_location?: OrderLocation,
    status_history: OrderLogStatus[],
    last_status_change: OrderLogStatus
}
export interface OrderItem {
    id: number,
    order_id: number,
    product_id: number,
    quantity: number,
    unit_price: number,
    total: number,
    product?: Product,
    attributes?: string,
}
export interface OrderLocation {
    id: number,
    location_type: 'warehouse' | 'pickup' | 'address',
    location_id : number | 'default',
    notes?: string | null,
    arrived_at: string,
    left_at?: string,
    duration: string | null,
    is_current: boolean
}
export interface OrderLogStatus {
    id: number,
    old_status?: OrderStatus,
    new_status: OrderStatus,
    notes?: string | null,
    created_at: string,
    updated_at?: string,
}
export enum OrderStatus {
    Pending = 'pending',
    Paid = 'paid',
    Processing = 'processing',
    Shipped = 'shipped',
    Delivered = 'delivered',
    Cancelled = 'cancelled',
    Completed = 'completed',
}
export interface OrderRequest {
    destination_pickup_id?: number,
    destination_address_id?: number,
    is_hidden: boolean,
    cart_items: number[],
}