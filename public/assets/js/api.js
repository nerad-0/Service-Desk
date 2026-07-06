(function () {
    class ApiClient {
        constructor(baseUrl) {
            this.baseUrl = baseUrl;
            this.csrfToken = '';
        }

        setCsrfToken(token) {
            this.csrfToken = token || '';
        }

        async get(path) {
            return this.request(path, { method: 'GET' });
        }

        async post(path, body) {
            return this.request(path, { method: 'POST', body });
        }

        async patch(path, body) {
            return this.request(path, { method: 'PATCH', body });
        }

        async delete(path) {
            return this.request(path, { method: 'DELETE' });
        }

        async request(path, options) {
            const method = options.method || 'GET';
            const headers = {
                Accept: 'application/json',
            };

            const requestOptions = {
                method,
                credentials: 'same-origin',
                headers,
            };

            if (options.body !== undefined) {
                headers['Content-Type'] = 'application/json';
                requestOptions.body = JSON.stringify(options.body);
            }

            if (!['GET', 'HEAD', 'OPTIONS'].includes(method) && this.csrfToken) {
                headers['X-CSRF-Token'] = this.csrfToken;
            }

            const response = await fetch(`${this.baseUrl}/${path.replace(/^\/+/, '')}`, requestOptions);
            const payload = await response.json().catch(() => ({
                success: false,
                message: 'Server nevrátil platný JSON.',
                errors: {},
            }));

            if (!response.ok || !payload.success) {
                const error = new Error(payload.message || 'Požadavek selhal.');
                error.status = response.status;
                error.errors = payload.errors || {};
                throw error;
            }

            if (payload.data && payload.data.csrf_token) {
                this.setCsrfToken(payload.data.csrf_token);
            }

            return payload.data || {};
        }
    }

    window.api = new ApiClient('api/index.php');
})();

