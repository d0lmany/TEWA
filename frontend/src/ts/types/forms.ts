import type { UploadRawFile } from "element-plus";
import type { Category } from "../entities";

export interface RawCategory {
    name: string,
    parent_id?: number | null,
}
export interface CategoriesContext {
    createForm: RawCategory,
    updateForm: RawCategory & { selectedCategoryId?: number, selectedCategory?: Omit<Category, 'parent_id'> },
    deleteForm: { id?: number },
    loading: Record<string, boolean>,
}
export interface PasswordChange {
    old_password: string,
    password: string,
    password_confirmation: string,
}
export interface BecomeASellerForm {
    firstName: string
    lastName: string
    patronymic?: string
    code: string
    payment_account: string
    passport_scan: File
    type: 'self_employed' | 'individual' | 'LLC'
    pass_series: string
    pass_number: string
}