export interface BecomeASellerRequest {
    full_name: string
    code: string
    payment_account: string
    passport_numbers: string
    passport_scan: File
    type: 'self_employed' | 'individual' | 'LLC'
}