import type { User } from "@/ts/entities/User";

export interface Review {
   id: number,
   user?: User,
   text: string,
   evaluation: number,
   created_at: string,
   updated_at?: string,
}