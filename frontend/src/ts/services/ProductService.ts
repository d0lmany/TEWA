import type ApiService from "@/ts/services/ApiService";
import Repository from "@/ts/services/Repository";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { GetRequest } from "@/ts/types/Request";
import type { FullProduct } from "@/ts/entities/Product";
/**
 * Product management service
 */
export default class ProductService
{
    private repo: Repository;
    
    constructor(api: ApiService) {
        this.repo = new Repository(api, 'products');
    }

    public index = async (data: GetRequest) => await this.repo.index(data)

    public show = async (id: number): Promise<ResponseResult<FullProduct>> => {
        const response = await this.repo.show({ url: `/products/${id}` });

        return {
            success: response.success,
            data: response.data.data,
            status: response.status,
            message: response.message
        };
    }
}
