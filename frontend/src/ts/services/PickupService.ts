import Repository from "@/ts/services/Repository";
import type ApiService from "@/ts/services/ApiService";
import type { GetRequest } from "@/ts/types/Request";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { Pickup } from "@/ts/entities/Addresses";
import type { PaginatedResult } from "@/ts/types/ResponseResult";
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