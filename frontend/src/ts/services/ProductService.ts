import type ApiService from "@/ts/services/ApiService";
import Repository from "@/ts/services/Repository";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { GetRequest } from "@/ts/types/Request";
import type { FullProduct, Product } from "@/ts/entities/Product";
import type { PaginatedResult } from "@/ts/types/ResponseResult";
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
