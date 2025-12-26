export default interface ResponseResult<T = any> {
    success: boolean;
    status: number;
    data?: T;
    message: string;
}
export type PaginatedResult<T = any> = ResponseResult<T> & { last_page: number }