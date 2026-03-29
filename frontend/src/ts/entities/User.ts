import type { Shop } from "./Shop";

export interface UserData {
    isAuth: boolean
    user: User
}
export interface User {
    name: string
    picture?: string
    birthday: string
    role: UserRole
    seller?: Seller
}
export enum UserRole {
    User = 'user',
    Admin = 'admin',
    Unsigned = '',
}
export interface Seller {
    full_name: string
    code: string
    type: 'self_employed' | 'individual' | 'LLC'
    shops?: Shop[]
    verified_at?: string
}