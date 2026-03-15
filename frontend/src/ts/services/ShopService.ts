import { Repository, type ApiService } from "@/ts/services"
import type { ResponseResult } from "@/ts/types"
import type { Shop } from "@/ts/entities"
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