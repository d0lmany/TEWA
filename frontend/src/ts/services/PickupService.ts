import Repository from "@/ts/services/Repository";
import type ApiService from "@/ts/services/ApiService";
import type { GetRequest } from "@/ts/types/Request";
/**
 * Pickup management service
 */
export default class PickupAddress
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'pickups');
    }

    public index = async (data: GetRequest) => await this.repo.index(data)
}