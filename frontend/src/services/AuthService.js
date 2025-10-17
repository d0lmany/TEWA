import axios from 'axios';

export default class AuthService {
    constructor(url) {
        this.api = axios.create({
            baseURL: url,
            timeout: 5000
        });
        
        this.checkStoredToken();
    }

    checkStoredToken() {
        const token = localStorage.getItem('auth_token');
        if (token) {
            this.setAuthHeader(token);
        }
    }

    setAuthHeader(token) {
        this.api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }

    async login(email, password) {
        try {
            const response = await this.api.post('/auth/login', {
                email,
                password
            });
            
            if (response.data.token) {
                localStorage.setItem('auth_token', response.data.token);
                this.setAuthHeader(response.data.token);
            }
            
            return response.data;
        } catch (error) {
            throw error.response?.data || error;
        }
    }

    async register(data) {
        try {
            const response = await this.api.post('/auth/register', {
                name: data.name,
                email: data.email,
                birthday: data.birthday,
                password: data.password,
            });

            if (response.data || response.data.id) {
                try {
                    const loginResult = await this.login({
                        email: data.email,
                        password: data.password,
                    });

                    if (loginResult.error) {
                        throw loginResult.error;
                    }

                    return loginResult;
                } catch (e) {
                    return {
                        user: response.data,
                        needLogin: true,
                    };
                }
            }

            throw new Error('Неожиданный ответ от сервера');
        } catch (error) {
            if (error.response?.status === 422) {
                const errorData = error.response.data;
                
                let errorMessage = errorData.message || 'Ошибка валидации';
                
                if (errorData.errors && Object.keys(errorData.errors).length > 0) {
                    const firstErrorKey = Object.keys(errorData.errors)[0];
                    const firstError = errorData.errors[firstErrorKey][0];
                    errorMessage = firstError;
                }
                
                throw new Error(errorMessage);
            }
            
            if (error.response?.data?.message) {
                throw new Error(error.response.data.message);
            }
            
            if (error.message) {
                throw new Error(error.message);
            }
            
            throw new Error('Неизвестная ошибка при регистрации');
        }
    }

    getToken() {
        return localStorage.getItem('auth_token');
    }

    isAuthenticated() {
        return !!this.getToken();
    }

    async logout() {
        try {
            await this.api.get('/auth/logout');
        } catch (error) {
            console.log('Logout error:', error);
        }
        
        localStorage.removeItem('auth_token');
        delete this.api.defaults.headers.common['Authorization'];
    }

    getApiInstance() {
        return this.api;
    }
}