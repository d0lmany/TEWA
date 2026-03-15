import { Repository, type ApiService } from "@/ts/services"
import type { AppMode, Config, PublicConfig, ResponseResult } from "@/ts/types"
/**
 * Config management service
 */
export default class ConfigService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'config');
    }

    public index = async (): Promise<ResponseResult<PublicConfig>> => await this.repo.index({
        url: 'config/public'
    })

    public indexPrivate = async (): Promise<ResponseResult<Config>> => await this.repo.index({
        url: 'config'
    })

    public changeMode = async (data: { mode: AppMode }) => await this.repo.update({ data, url: '/config/mode' })
}