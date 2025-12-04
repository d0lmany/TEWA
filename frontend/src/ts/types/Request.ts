export interface Request {
   url?: string,
   params?: object,
   data?: object,
}

export type GetRequest = Omit<Request, 'data'>