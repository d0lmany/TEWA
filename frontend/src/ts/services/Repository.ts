import type ResponseResult from "@/ts/types/ResponseResult";
import type ApiService from "@/ts/services/ApiService";
import type { GetRequest, Request } from "@/ts/types/Request";
import type { ResponseType } from "axios";
/**
 * Server request service
 */
export default class Repository<T = any>
{
    private api: ApiService;
    private entity: string;

    constructor(api: ApiService, entity: string) {
        this.api = api;
        this.entity = entity;
    }

    public index = async (data: GetRequest = { url: `/${this.entity}` }): Promise<ResponseResult<T>> => await this.api.get(this.defaultData(data))

    public store = async (data: Omit<Request, 'params'>): Promise<ResponseResult<T>> => await this.api.post(this.defaultData(data))

    public show = async (data: GetRequest): Promise<ResponseResult<T>> => await this.api.get(this.defaultData(data))

    public update = async (data: Request): Promise<ResponseResult<T>> => await this.api.patch(this.defaultData(data))

    public destroy = async (data: GetRequest): Promise<ResponseResult<T>> => await this.api.delete(this.defaultData(data))

    public file = async (path: string, type: ResponseType): Promise<ResponseResult> => await this.api.getFile(path, type)

    private defaultData = (data: Request) => ({
        url: data?.url || `/${this.entity}`,
        data: data?.data,
        params: data?.params,
        headers: data?.headers
    })
}