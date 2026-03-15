export enum AppMode {
    Market = 'marketplace',
    Shop = 'shop',
}

export interface PublicConfig {
    mode: AppMode,
}

export interface Config extends PublicConfig {
    //
}