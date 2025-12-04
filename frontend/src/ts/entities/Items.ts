export interface CartItem {
   id?: number,
   quantity: number,
   product_id: number,
   product_attributes?: number[],
}

export interface FavoriteLists {
   [listName: string]: Omit<FavoriteList, 'name'>
}

export interface FavoriteList {
   id: number,
   name: string,
   items: FavoriteListItem[],
   created_at: string
}

export interface FavoriteListItem {
   id: number,
   list_id: number,
   product_id: number,
   added_at: string
}