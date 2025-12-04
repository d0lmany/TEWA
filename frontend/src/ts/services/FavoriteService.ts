import Repository from "@/ts/services/Repository";
import ApiService from "@/ts/services/ApiService";
import type ResponseResult from "@/ts/types/ResponseResult";

export default class FavoriteService
{
   private repo: Repository;

   constructor(api: ApiService) {
      this.repo = new Repository(api, 'favorite');
   }

   public toggle = async (id: number): Promise<ResponseResult> => {
      const response = await this.repo.store({
         url: '/favorite', data: { product_id: id }
      });

      return {
         success: response.status === 200 || response.status === 201,
         data: response.data.data,
         status: response.status,
         message: response.data.action || response.message
      };
   }
}