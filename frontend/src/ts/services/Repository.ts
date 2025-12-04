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

   public index = async (data: GetRequest = { url: `/${this.entity}` }): Promise<ResponseResult<T>> => await this.api.get(data)

   public store = async (data: Omit<Request, 'params'>): Promise<ResponseResult<T>> => await this.api.post({
      url: data.url, data: data.data,
   },)

   public show = async (data: GetRequest): Promise<ResponseResult<T>> => await this.api.get(data)

   public update = async (data: Request): Promise<ResponseResult<T>> => await this.api.patch(data)

   public destroy = async (data: GetRequest): Promise<ResponseResult<T>> => await this.api.delete(data)

   public file = async (path: string, type: ResponseType): Promise<ResponseResult> => await this.api.getFile(path, type)
}