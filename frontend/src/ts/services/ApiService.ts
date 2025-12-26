import axios, { AxiosError, type AxiosInstance, type AxiosResponse, isAxiosError, type ResponseType } from "axios";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { Request, GetRequest } from "@/ts//types/Request";
/**
 * API object management and server request service
 */
export default class ApiService
{
    private static readonly msg: Record<string|number, string> = {
        // axios errors
        NETWORK_ERROR: 'The device is offline',
        TIMEOUT_ERROR: 'The response is too long',
        ECONNABORTED: 'Request timeout',
        ERR_CANCELED: 'Request was canceled',
        UNKNOWN_NETWORK: 'Unknown network error',
        // http codes
        200: 'Successful',
        201: 'Created',
        204: 'No Content',
        401: 'Unauthorized',
        403: 'Forbidden',
        404: 'Not Found',
        422: 'Unprocessable Entity',
        429: 'Too Many Requests',
    };

    private api: AxiosInstance;

    constructor(baseURL: string) {
        this.api = axios.create({
            baseURL: baseURL,
            timeout: 5000,
            headers: {
                'Content-Type' : 'application/json',
                'Accept': 'application/json',
            },
            validateStatus: () => true,
        });
    }

    /**
     * Sets the Bearer token for authorized requests
     */
    public set authToken(token: string | null) {
        if (token) {
            this.api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            localStorage.setItem('auth_token', token);
        } else {
            delete this.api.defaults.headers.common['Authorization'];
            localStorage.removeItem('auth_token');
        }
    }

    /**
     * Returns API instance from axios
     * @returns api instance
     */
    public getApiInstance(): AxiosInstance {
        return this.api;
    }

    /**
     * Creates a ready-made beautiful response
     * @param response result of api.method(url, data)
     * @example
     * const api = (new APIService()).getApiInstance();
     * const response = await api.post('/users');
     * if (response.success) {
     *     alert('It's OK!');
     *     console.table(response.data);
     * } else {
     *     alert('Something went wrong...');
     *     console.error(response.error);
     * }
     * @returns a rendered response 
     */
    public static renderResponse(response: AxiosResponse | AxiosError | ResponseResult): ResponseResult {
        if ('data' in response)
            return this.renderHttpResponse(response);
        else
            return this.renderNetworkError(response);
    };

    private static renderHttpResponse(response: AxiosResponse | ResponseResult): ResponseResult {
        return {
            success: response.status < 400,
            status: response.status,
            data: response?.data?.data ?? response?.data,
            message: response?.data?.message ?? this.msg[response.status] ?? 'Unexpected error',
        }
    }

    private static renderNetworkError(response: AxiosError | ResponseResult): ResponseResult {
        let errorKey: string | number;;
        
        if (isAxiosError(response)) {
            errorKey = response.code ?? 'UNKNOWN_NETWORK';
        } else {
            errorKey = response.status ?? 'UNKNOWN_NETWORK';
        }
        
        return {
            success: false,
            status: 0,
            message: this.msg[errorKey] ?? 'Unexpected error',
        };
    }

    /**
     * The raw method of accessing the server
     * @param request Request obj payload
     * @param method HTTP method
     * @returns The rendered response result
     */
    private async query(request: Request, method: 'get' | 'post' | 'put' | 'patch' | 'delete') {
        try {
            const response = await this.api({
                method,
                url: request?.url,
                data: request?.data,
                params: request?.params,
                headers: {
                    ...this.api.defaults.headers.common,
                    ...request.headers
                }
            });

            return ApiService.renderResponse(response);
        } catch (error) {
            return ApiService.renderResponse(error as AxiosError);
        }
    }

    /**
     * GET method, usually returns an array of records
     * @param request Request obj payload
     * @returns The rendered response result
     */
    public get = async (request: GetRequest) => await this.query(request, 'get');

    /**
     * POST method, usually returns a data
     * @param request Request obj payload
     * @returns The rendered response result
     */
    public post = async (request: Request) => await this.query(request, 'post');

    /**
     * PUT method, usually returns a message
     * @param request Request obj payload
     * @returns The rendered response result
     */
    public put = async (request: Request) => await this.query(request, 'put');

    /**
     * PATCH method, usually returns a message
     * @param request Request obj payload
     * @returns The rendered response result
     */
    public patch = async (request: Request) => await this.query(request, 'patch');

    /**
     * DELETE method, usually returns a message
     * @param request Request obj payload
     * @returns The rendered response result
     */
    public delete = async (request: GetRequest) => await this.query(request, 'delete');

    public getFile = async (path: string, type: ResponseType) => {
        try {
            const response = await this.api.get(path, {
                responseType: type
            });

            return ApiService.renderResponse(response);
        } catch (error) {
            return ApiService.renderResponse(error as AxiosError);
        }
    }
}