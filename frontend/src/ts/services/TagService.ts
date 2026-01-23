import type ApiService from "@/ts/services/ApiService";
import Repository from "@/ts/services/Repository";
import type { Tag } from "@/ts/entities/Category";
import type ResponseResult from "@/ts/types/ResponseResult";
/**
 * Tag management service
 */
export default class TagService
{
    private repo: Repository;
    private cached: ResponseResult<Tag[]> | null = null;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'tags');
    }

    public index = async (): Promise<ResponseResult<Tag[]>> => {
        if (this.cached) {
            return this.cached;
        } else {
            const response = await this.repo.index();

            if (response.success && response.data) {
                this.cached = response;
            }
            
            return response;
        }
    }
}
