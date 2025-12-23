import Repository from "@/ts/services/Repository";
import type ApiService from "@/ts/services/ApiService";
import type { Address } from "@/ts/entities/Addresses";
/**
 * Address management service
 */
export default class AddressService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'addresses');
    }

    public index = async () => await this.repo.index()

    public store = async (data: Omit<Address, 'id'>) => await this.repo.store({ data })

    public update = async (id: number, data: Partial<Address>) => await this.repo.update({
        url: `/addresses/${id}`, data
    })

    public destroy = async (id: number) => await this.repo.destroy({
        url: `/addresses/${id}`
    })
}