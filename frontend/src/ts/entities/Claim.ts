export default interface Claim {
    entity: 'product' | 'shop',
    entity_id: number,
    topic: string,
    text: string
}