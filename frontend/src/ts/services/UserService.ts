import Repository from "@/ts/services/Repository";
import ApiService from "@/ts/services/ApiService";
import type ResponseResult from "@/ts/types/ResponseResult";
import type { LoginData, UserResponseData, RegistrationData } from "@/ts/types/UserR-R";
import type { User } from "@/ts/entities/User";
/**
 * Auth and User Entity management service
 */
export default class UserService
{
   private repo: Repository;

   constructor(api: ApiService) {
      this.repo = new Repository(api, 'users');
   }

   public registration = (data: RegistrationData): Promise<ResponseResult> => this.repo.store({
      url: '/auth', data,
   })

   public show = async (id: number = 0): Promise<ResponseResult<User>> => {
      const response = await this.repo.show({url: `/auth${id ? `/${id}` : ''}`});
      return ApiService.renderResponse({
         success: response.success,
         message: response.message,
         status: response.status,
         data: response.data.data,
      });
   }

   public async loadUserData(): Promise<ResponseResult<UserResponseData>> {
      if (UserService.isAuthenticated) {
         try {
            const [cartResult, favoriteResult, userResult] = await Promise.all([
               this.repo.index({ url: '/lowCart' }),
               this.repo.index({ url: '/lowFavorite' }),
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
      } else {
         return ApiService.renderResponse({
            success: false,
            message: '',
            status: 401,
         })
      }
   }

   public login = async (data: LoginData): Promise<ResponseResult> => await this.repo.store({
         url: '/auth/login', data
   })

   /**
    * @returns a token or null
    */
   public static get storedToken(): string | null {
      return localStorage.getItem('auth_token');
   }

   /**
    * @returns is there a token
    */
   public static get isAuthenticated(): boolean {
      return !!localStorage.getItem('auth_token');
   }
}