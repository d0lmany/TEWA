export interface UserData {
    isAuth: boolean;
    user: User;
}

export interface User {
    name: string;
    picture: string | null;
    birthday: string;
}

export interface Seller {
    full_name: string,
    code: string,
    type: 'self_employed' | 'individual' | 'LLC'
}