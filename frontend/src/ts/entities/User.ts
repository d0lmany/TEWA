export interface UserData {
    isAuth: boolean,
    user: User,
}
export interface User {
    name: string,
    picture: string | null,
    birthday: string,
    role: UserRole,
}
export enum UserRole {
    User = 'user',
    Admin = 'admin',
    Unsigned = '',
}
export interface Seller {
    full_name: string,
    code: string,
    type: 'self_employed' | 'individual' | 'LLC',
}