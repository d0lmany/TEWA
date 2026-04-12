export interface BecomeASellerRequest {
    full_name: string
    code: string
    payment_account: string
    passport_numbers: string
    passport_scan: File
    type: 'self_employed' | 'individual' | 'LLC'
}
export interface FullProductRequest {
    name: string
    quantity: number
    base_price: number
    photo?: File
    category_id: number
    tags?: number[]
    discount?: number
    status?: 'on' | 'off' | 'draft'
    shop_id?: number
}