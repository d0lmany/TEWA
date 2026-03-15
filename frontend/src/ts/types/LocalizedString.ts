export interface LocalizedString {
    [phrase: string]: {
        [languageCode: string]: string,
    }
}