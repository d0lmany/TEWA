import { Repository, type ApiService } from "@/ts/services"
import type { Claim } from "@/ts/entities"
/**
 * Claim management service
 */
export default class ClaimService
{
    private repo: Repository;
    
    constructor(api: ApiService) {
        this.repo = new Repository(api, 'claims');
    }

    public store = async (data: Claim) => await this.repo.store({data})
}