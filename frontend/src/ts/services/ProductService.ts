import { Repository, type ApiService } from "@/ts/services"
import type { ResponseResult, GetRequest, PaginatedResult } from "@/ts/types"
import type { FullProduct, Product } from "@/ts/entities"
/**
 * Product management service
 */
export default class ProductService
{
    private repo: Repository;
    
    constructor(api: ApiService) {
        this.repo = new Repository(api, 'products');
    }

    public index = async (data: GetRequest): Promise<PaginatedResult<Product[]>> => await this.repo.index(data) as unknown as PaginatedResult

    public show = async (id: number): Promise<ResponseResult<FullProduct>> => await this.repo.show({ url: `/products/${id}` })
}
