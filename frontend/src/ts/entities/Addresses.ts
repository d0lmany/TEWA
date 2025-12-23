export interface Address {
    id: number
    pickup_id?: number,
    address?: string,
    is_default: boolean,
    pickup?: Pickup
}

export interface Pickup {
    id: number
    name: string,
    country: string,
    city: string,
    address: string,
}

export interface ExactAddress {
    country: string,
    region: string,
    city: string,
    street: string,
    house_number: string,
    apartment?: string,
    zip_code?: number
}