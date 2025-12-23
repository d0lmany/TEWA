export default interface LocalizedString {
    [phrase: string]: {
        [languageCode: string]: string,
    }
}