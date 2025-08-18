import axios from 'axios';

export class AuthService {
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
                password: data.password
            });

            if (response.data && response.data.user) {
                try {
                    const loginResult = await this.login(data.email, data.password);
                    return loginResult;
                } catch (loginError) {
                    throw new Error('Регистрация успешна, но вход не удался: ' + (loginError.message || loginError));
                }
            }

            if (response.data?.message) {
                throw new Error(response.data.message);
            }

            throw new Error('Неизвестная ошибка регистрации');

        } catch (error) {
            throw error.response?.data?.message || error.message || 'Ошибка регистрации';
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