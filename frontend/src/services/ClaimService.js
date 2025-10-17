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
                throw new Error(response);
            }
        } catch (e) {
            return {
                success: false,
                message: e.message || e,
            }
        }
    }
}