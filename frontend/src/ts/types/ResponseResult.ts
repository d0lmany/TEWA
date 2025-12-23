export default interface ResponseResult<T = any> {
    success: boolean;
    status: number;
    data?: T;
    message: string;
}