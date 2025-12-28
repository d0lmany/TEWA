import type LocalizedString from "@/ts/types/LocalizedString";
/**
 * Localization management service
 */
export default class I18n
{
    private currentLang: string;
    private dictionary: LocalizedString = {};
    private initialized: any;

    constructor(lang = I18n.deviceLang) {
        this.currentLang = lang;
        this.initialized = this.loadDictionary();
    }

    public static get deviceLang(): string {
        return navigator.language.split('-')[0] || 'en';
    }

    public async loadDictionary(): Promise<boolean> {
        try {
            const response = await fetch('/assets/json/dictionary.json');

            if (!response.ok) {
                throw new Error(`Can't load dictionary: ${response.status}`);
            }

            this.dictionary = await response.json();
            return true;
        } catch (error) {
            console.error(error instanceof Error ? error.message : error);
            return false;
        }
    }

    public translate(phrase: string, lang: string = this.currentLang): string {
        this.initialized;
        // TODO: игнорировать строки по типу XL, 500g и т. д.

        const target = phrase.trim().toLocaleLowerCase();

        if (!Object.keys(this.dictionary).length) {
            console.warn('Dictionary is empty');
            return target;
        }

        if (!this.dictionary[target]) {
            console.warn(`Phrase '${target}' not found`);
            return phrase;
        }

        if (!this.dictionary[target][lang]) {
            console.warn(`Language '${lang}' not set for phrase '${target}'`);
            return target;
        }

        return this.dictionary[target][lang];
    }
}