import Repository from "@/ts/services/Repository";
import ApiService from "@/ts/services/ApiService";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { LoginData, UserResponseData, RegistrationData } from "@/ts/types/UserR-R";
import type { User } from "@/ts/entities/User";
import type PasswordChange from "@/ts/types/PasswordChange";
import type { CartProduct, FavoriteList } from "@/ts/entities/Items";
/**
 * Auth and User Entity management service
 */
export default class UserService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'auth');
    }

    public registration = (data: RegistrationData): Promise<ResponseResult<{ token: string }>> => this.repo.store({data})

    public show = async (): Promise<ResponseResult<User>> => await this.repo.show({})

    public async loadUserData(options = { loadUser: true }): Promise<ResponseResult<UserResponseData>> {
        try {
            const [cartResult, favoriteResult, userResult] = await Promise.all([
                this.repo.index({ url: '/cart/low' }) as Promise<ResponseResult<CartProduct[]>>,
                this.repo.index({ url: '/favorite/low' }) as Promise<ResponseResult<FavoriteList[]>>,
                options.loadUser ? this.show() : { success: false, data: '', message: '' }
            ]);
            
            const data = {
                cart: cartResult.success ? cartResult.data : null,
                favorite: favoriteResult.success ? favoriteResult.data : null,
                user: userResult.success ? userResult.data : null,
                errors: {
                    cart: !cartResult.success ? cartResult.message : null,
                    favorite: !favoriteResult.success ? favoriteResult.message : null,
                    user: !userResult.success ? userResult.message : null,
                }
            }

            return ApiService.renderResponse({
                success: true,
                status: 200,
                message: '',
                data
            });
        } catch (error) {
            return ApiService.renderResponse({
                success: false,
                status: 0,
                message: error instanceof Error ? error.message : '',
            });
        }
    }

    public login = async (data: LoginData): Promise<ResponseResult<{ token: string }>> => await this.repo.store({
        url: '/auth/login', data
    })

    public updatePersonalData = async (data: FormData) => await this.repo.store({
        url: '/auth/update', data, headers: { 'Content-Type': 'multipart/form-data' }
    });

    public updatePassword = async (data: PasswordChange): Promise<ResponseResult<{ token: string }>> => await this.repo.update({
        url: '/auth/update/password', data
    });

    public logout = async () => {
        const response = await this.repo.index({ url: '/auth/logout' });

        if (response.success) {
            localStorage.removeItem('auth_token');
        }

        return response;
    };

    public destroy = async () => await this.repo.destroy({});

    /**
     * @returns a token or null
     */
    public static get storedToken(): string | null {
        return localStorage.getItem('auth_token');
    }

    /**
     * @returns is there a token
     */
    public isAuthenticated = async (): Promise<boolean | User> => {
        const haveToken: boolean = !!localStorage.getItem('auth_token');

        if (!haveToken) {
            return false;
        }

        const response = await this.show();
        
        if (!response.success) {
            localStorage.removeItem('auth_token');
            return false;
        }

        return response?.data || false;
    }
}