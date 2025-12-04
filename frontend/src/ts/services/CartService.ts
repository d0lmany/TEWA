import Repository from "@/ts/services/Repository";
import ApiService from "@/ts/services/ApiService";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { CartItem } from "@/ts/entities/Items";

export default class CartService
{
   private repo: Repository;

   constructor(api: ApiService) {
      this.repo = new Repository(api, 'cart');
   }

   public store = async (data: CartItem): Promise<ResponseResult> => await this.repo.store({
      url: '/cart', data
   })

   public update = async (id: number, data: object): Promise<ResponseResult> => await this.repo.update({
      url: `/cart/${id}`, data
   })

   public destroy = async (id: number): Promise<ResponseResult> => await this.repo.destroy({
      url: `/cart/${id}`
   })
}