export default interface Filters {
   min_rating?: number,
   min_price?: number,
   max_price?: number,
   tags?: string[],
   category_id?: number | object
}