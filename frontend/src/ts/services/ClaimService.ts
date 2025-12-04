import type ApiService from "@/ts/services/ApiService";
import Repository from "@/ts/services/Repository";
import type ResponseResult from "@/ts/types/ResponseResult";
import type Claim from "@/ts/entities/Claim";
/**
 * Claim management service
 */
export default class ClaimService
{
   private repo: Repository;
   
   constructor(api: ApiService) {
      this.repo = new Repository(api, 'claims');
   }

   public store = async (data: Claim): Promise<ResponseResult> => await this.repo.store({
      url: '/claims', data
   })
}