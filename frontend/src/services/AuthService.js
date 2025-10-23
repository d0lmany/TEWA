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
                password_confirmation: data.passwordConfirmation
            });

            if (response.data?.data && response.data?.token) {
                return {
                    user: response.data.data,
                    token: response.data.token,
                    success: true
                };
            }

            throw new Error('Неверный формат ответа от сервера');

        } catch (error) {
            if (error.response?.status === 422) {
                const errors = error.response.data.errors;
                if (errors) {
                    const firstError = Object.values(errors)[0]?.[0];
                    throw new Error(firstError || 'Проверьте правильность введенных данных');
                }
            }
            
            if (error.response?.data?.message) {
                throw new Error(error.response.data.message);
            }
            
            if (error.code === 'NETWORK_ERROR' || error.code === 'ECONNABORTED') {
                throw new Error('Ошибка соединения с сервером');
            }
            
            throw new Error(error.message || 'Ошибка при регистрации');
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