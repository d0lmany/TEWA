export type Rules = Record<string, object[]>

export const createRule = (message: string, trigger: 'blur' | 'change') => ({message, trigger})

export const createMinRule = (min: number, type: 'string' | 'number' = 'string', trigger: 'blur' | 'change' = 'change') =>
    ({ min, ...createRule(
        type === 'string'
        ? `Минимум ${min} символов`
        : `Не менее ${min}`
        , trigger) })

export const createMaxRule = (max: number, type: 'string' | 'number' = 'string', trigger: 'blur' | 'change' = 'change') =>
    ({ max, ...createRule(
        type === 'string'
        ? `Максимум ${max} символов`
        : `Не более ${max}`
        , trigger) })

export const createRequiredRule = (field: string, trigger: 'blur' | 'change' = 'blur') => ({
    required: true, message: `"${field}" - обязательное поле`, trigger
})

export const createTypeRule = (message: string, type: 'string' | 'number' | 'boolean' | 'regexp' | 'integer' | 'float' | 'array'
    | 'object' | 'enum' | 'date' | 'url' | 'hex' | 'email' | 'any' = 'string'
    , trigger: 'blur' | 'change' = 'change') => ({ type, ...createRule(message, trigger) })

export const createEnumRule = (enumRange: Array<any>, message: string, trigger: 'blur' | 'change' = 'change') => ({
    enum: enumRange, ...createRule(message, trigger)
})