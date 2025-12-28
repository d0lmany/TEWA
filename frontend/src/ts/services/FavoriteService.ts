import Repository from "@/ts/services/Repository";
import ApiService from "@/ts/services/ApiService";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { FavoriteList } from "../entities/Items";
/**
 * Favorite lists and favorite list items management service
 */
export default class FavoriteService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'favorite');
    }

    public index = async (): Promise<ResponseResult<FavoriteList[]>> => await this.repo.index()
    
    public store = async (data: object) => await this.repo.store({data})
    
    public update = async (id: number, data: object) => await this.repo.update({
        url: `/favorite/${id}`, data
    })

    public toggle = async (product_id: number, list_id: number = 0) => {
        const response = await this.repo.store({
            data: { product_id, list_id },
            url: '/favorite/toggle'
        });

        return {
            success: response.status === 200 || response.status === 201,
            data: response.data,
            status: response.status,
            message: response.data.action || response.message
        };
    }

    public changeList = async (id: number, list_id: number) => await this.repo.update({
        url: `/favorite/item/${id}`, data: { list_id }
    })

    public clear = async (id: number) => await this.repo.destroy({
        url: `/favorite/clear/${id}`
    })

    public destroy = async (id: number) => await this.repo.destroy({
        url: `/favorite/${id}`
    })
}