import ApiService from "./ApiService"
import UserService from "./UserService"
import CategoryService from "./CategoryService"
import ProductService from "./ProductService"
import I18n from "./I18n"
import CartService from "./CartService"
import FavoriteService from "./FavoriteService"
import ClaimService from "./ClaimService"
import AddressService from "./AddressService"
import PickupService from "./PickupService"
import ShopService from "./ShopService"
import OrderService from "./OrderService"
import TagService from "./TagService"
import Repository from "./Repository"
import ConfigService from "./ConfigService"

export {
    ApiService,
    UserService,
    CategoryService,
    ProductService,
    I18n,
    CartService,
    FavoriteService,
    ClaimService,
    AddressService,
    PickupService,
    ShopService,
    OrderService,
    TagService,
    Repository,
    ConfigService,
}

export interface Services {
    api: ApiService,
    user: UserService,
    category: CategoryService,
    product: ProductService,
    i18n: I18n,
    cart: CartService,
    favorite: FavoriteService,
    claim: ClaimService,
    address: AddressService,
    pickup: PickupService,
    shop: ShopService,
    order: OrderService,
    tag: TagService,
    config: ConfigService,
}