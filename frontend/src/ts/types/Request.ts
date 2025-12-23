export interface Request {
    url?: string,
    params?: object,
    data?: object,
    headers?: Record<string, string>
}

export type GetRequest = Omit<Request, 'data'>