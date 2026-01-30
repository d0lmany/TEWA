import Repository from "@/ts/services/Repository";
import type ApiService from "@/ts/services/ApiService";
import type ResponseResult from "@/ts/types/ResponseResult";
import { OrderStatus, type Order, type OrderRequest } from "@/ts/entities/Order";
/**
 * Order management service
 */
export default class OrderService
{
    private repo: Repository;

    public readonly statuses = {
        [OrderStatus.Pending]: 'Ожидает оплаты',
        [OrderStatus.Paid]: 'Оплачен',
        [OrderStatus.Processing]: 'Собирается',
        [OrderStatus.Shipped]: 'Передан в доставку',
        [OrderStatus.Delivered]: 'Доставлен',
        [OrderStatus.Cancelled]: 'Отменён',
        [OrderStatus.Completed]: 'Получен',
    };

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'orders');
    }

    public index = async (): Promise<ResponseResult<Order[]>> => await this.repo.index()

    public store = async (data: OrderRequest) => await this.repo.store({ data })

    public cancelOrder = async (id: number) => await this.repo.update({ url: `/orders/${id}/cancel` })
}