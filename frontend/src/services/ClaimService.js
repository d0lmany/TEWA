export default class ClaimService
{
    constructor(API) {
        this.API = API;
    }

    async store(claim) {
        try {
            const response = await this.API.post('/claims', claim);

            if (response.status === 201) {
                return {
                    success: true,
                    data: response.data,
                }
            } else {
                throw response;
            }
        } catch (e) {
            const msg = {
                422: 'Проверьте правильность введённых данных',
                429: 'Слишком много попыток',
            }

            return {
                success: false,
                message: msg[e.status] ?? (e.data.message || e.data),
                idk: e
            }
        }
    }
}