import Repository from "@/ts/services/Repository";
import type ApiService from "@/ts/services/ApiService";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { Order } from "@/ts/entities/Order";
/**
 * Order management service
 */
export default class OrderService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'orders');
    }

    public index = async (): Promise<ResponseResult<Order[]>> => await this.repo.index()

    //public store = async (data: Omit<Address, 'id'>) => await this.repo.store({ data })

    /*public update = async (id: number, data: Partial<Address>) => await this.repo.update({
        url: `/addresses/${id}`, data
    })*/

    /*public destroy = async (id: number) => await this.repo.destroy({
        url: `/addresses/${id}`
    })*/
}