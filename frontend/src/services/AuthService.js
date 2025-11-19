import axios from 'axios';

export default class AuthService {
    constructor(url) {
        this.api = axios.create({
            baseURL: url,
            timeout: 5000,
            headers: {
                'Content-Type' : 'application/json',
            },
            validateStatus: () => true,
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
                email: email,
                password: password,
            });

            if (response.status !== 200) {
                throw response;
            }
            
            if (response.data.token) {
                localStorage.setItem('auth_token', response.data.token);
                this.setAuthHeader(response.data.token);
            }
            
            return {
                success: true,
                data: response.data.data,
            }
        } catch (error) {
            return {
                success: false,
                message: error.data.message || error,
                status: error.status
            }
        }
    }

    async register(data) {
        try {
            const response = await this.api.post('/auth/register', {
                name: data.name,
                email: data.email,
                birthday: data.birthday,
                password: data.password,
                password_confirmation: data.passwordConfirmation
            });

            if (response.status === 201) {
                if (response.data.token) {
                    localStorage.setItem('auth_token', response.data.token);
                    this.setAuthHeader(response.data.token);

                    return {
                        success: true,
                        data: response.data.data,
                    };
                } else {
                    throw response;
                }
            } else {
                throw response;
            }
        } catch (error) {
            const msg = {
                422: 'Проверьте правильность введённых данных',
                429: 'Слишком много попыток',
                201: 'Регистрация выполнена, но авторизация не удалась'
            };
            
            return {
                success: false,
                message: msg[error.status] ?? (error.error || error)
            };
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
            const response = await this.api.get('/auth/logout');

            if (response.status === 204) {
                localStorage.removeItem('auth_token');
                delete this.api.defaults.headers.common['Authorization'];

                return { success: true }
            } else {
                throw response.data || response
            }
        } catch (error) {
            console.error('Logout error:', error.message || error);

            return {
                success: false,
                message: error.message || error,
            }
        }
    }

    async show() {
        try {
            const response = await this.api.get('/auth');

            if (response.status === 200) {
                return {
                    success: true,
                    data: response.data.data,
                };
            } else {
                throw response.data || response;
            }
        } catch (e) {
            return {
                success: false,
                data: e.message || e,
            }
        }
    }

    getApiInstance() {
        return this.api;
    }
}