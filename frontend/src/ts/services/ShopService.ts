import type ApiService from "@/ts/services/ApiService";
import Repository from "@/ts/services/Repository";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { Shop } from "@/ts/entities/Shop";
/**
 * Shop management service
 */
export default class ShopService
{
    private repo: Repository;
    
    constructor(api: ApiService) {
        this.repo = new Repository(api, 'shops');
    }

    public show = async (id: number): Promise<ResponseResult<Shop>> => await this.repo.show({ url: `/shops/${id}` })
}