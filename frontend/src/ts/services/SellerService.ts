import { Repository, type ApiService } from "@/ts/services"
import type { BecomeASellerRequest } from "@/ts/types";
/**
 * Seller management service
 */
export default class SellerService
{
    private repo: Repository;
    
    constructor(api: ApiService) {
        this.repo = new Repository(api, 'sellers');
    }

    public store = async (data: BecomeASellerRequest) => await this.repo.store({data, headers: { 'Content-Type': 'multipart/form-data' }})
}