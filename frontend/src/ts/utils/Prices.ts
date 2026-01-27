import type { ProductAttribute } from "../entities/Product";

export const getPricesWithAttrs = (product: { price: { final_price: number, base_price: number } }, variantAttrs: Record<string, ProductAttribute[]> = {}, checkedAttrs: Record<string, string> = {}) => {
    let impact = 0;

    Object.keys(variantAttrs).forEach(type =>
        impact += variantAttrs[type]?.find(attr =>
            attr.attr_value === checkedAttrs[type])?.price || 0);

    return {
        total: product.price.base_price + impact,
        totalWithDisc: product.price.final_price + impact,
    };
}