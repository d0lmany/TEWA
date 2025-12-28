import Repository from "@/ts/services/Repository";
import ApiService from "@/ts/services/ApiService";
import type { CartProduct, CartItem } from "@/ts/entities/Items";
import type ResponseResult from "@/ts/types/ResponseResult";
/**
 * Cart items management service
 */
export default class CartService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'cart');
    }

    public index = async (): Promise<ResponseResult<CartProduct[]>> => await this.repo.index()

    public store = async (data: CartItem) => await this.repo.store({data})

    public update = async (id: number, data: object) => await this.repo.update({
        url: `/cart/${id}`, data
    })

    public destroy = async (id: number) => await this.repo.destroy({ url: `/cart/${id}` })

    public destroyRange = async (range: number[]) => await this.repo.store({
        data: { ids: range },
        url: 'cart/destroy',
    })
}