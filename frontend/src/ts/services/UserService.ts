import Repository from "@/ts/services/Repository";
import ApiService from "@/ts/services/ApiService";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { LoginData, UserResponseData, RegistrationData } from "@/ts/types/UserR-R";
import type { User } from "@/ts/entities/User";
import type PasswordChange from "@/ts/types/PasswordChange";
/**
 * Auth and User Entity management service
 */
export default class UserService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'auth');
    }

    public registration = (data: RegistrationData): Promise<ResponseResult> => this.repo.store({data})

    public show = async (id: number = 0): Promise<ResponseResult<User>> => {
        const response = await this.repo.show({url: `/auth${id ? `/${id}` : ''}`});
        return ApiService.renderResponse({
            success: response.success,
            message: response.message,
            status: response.status,
            data: response.data.data || response.data,
        });
    }

    public async loadUserData(): Promise<ResponseResult<UserResponseData>> {
        try {
            const [cartResult, favoriteResult, userResult] = await Promise.all([
                this.repo.index({ url: '/cart/low' }),
                this.repo.index({ url: '/favorite/low' }),
                this.show(),
            ]);

            return ApiService.renderResponse({
                success: true,
                status: 200,
                message: '',
                data: {
                    cart: cartResult.success ? cartResult.data.data : null,
                    favorite: favoriteResult.success ? favoriteResult.data.data : null,
                    user: userResult.success ? userResult.data : null,
                    errors: {
                        cart: !cartResult.success ? cartResult.message : null,
                        favorite: !favoriteResult.success ? favoriteResult.message : null,
                        user: !userResult.success ? userResult.message : null,
                    }
                }
            });
        } catch (error) {
            return ApiService.renderResponse({
                success: false,
                status: 0,
                message: error instanceof Error ? error.message : '',
            });
        }
    }

    public login = async (data: LoginData): Promise<ResponseResult> => await this.repo.store({
        url: '/auth/login', data
    })

    public updatePersonalData = async (data: FormData): Promise<ResponseResult> =>  await this.repo.store({
        url: '/auth/update', data, headers: { 'Content-Type': 'multipart/form-data' }
    });

    public updatePassword = async (data: PasswordChange): Promise<ResponseResult> => await this.repo.update({
        url: '/auth/update/password', data
    });

    public logout = async (): Promise<ResponseResult> => {
        const response = await this.repo.index({ url: '/auth/logout' });

        if (response.success) {
            localStorage.removeItem('auth_token');
        }

        return response;
    };

    /**
     * @returns a token or null
     */
    public static get storedToken(): string | null {
        return localStorage.getItem('auth_token');
    }

    /**
     * @returns is there a token
     */
    public isAuthenticated = async (): Promise<boolean> => {
        const haveToken: boolean = !!localStorage.getItem('auth_token');

        if (!haveToken) {
            return false;
        }

        const response = await this.show();
        
        if (!response.success) {
            localStorage.removeItem('auth_token');
            return false;
        }

        return true;
    }
}