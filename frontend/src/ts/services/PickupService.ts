import { Repository, type ApiService } from "@/ts/services"
import type { GetRequest, PaginatedResult } from "@/ts/types"
import type { Pickup } from "@/ts/entities"
/**
 * Pickup management service
 */
export default class PickupService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'pickups');
    }

    public index = async (data: GetRequest): Promise<PaginatedResult<Pickup[]>> => await this.repo.index(data) as unknown as PaginatedResult
}