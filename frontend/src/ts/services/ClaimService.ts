import type ApiService from "@/ts/services/ApiService";
import Repository from "@/ts/services/Repository";
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

    public store = async (data: Claim) => await this.repo.store({data})
}