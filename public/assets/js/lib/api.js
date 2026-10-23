(function () {
    const TOKEN_KEY = 'auth_token';
    const USER_KEY = 'auth_user';

    function token() {
        return localStorage.getItem(TOKEN_KEY);
    }

    function clearAuth() {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
    }

    async function parse(response) {
        try {
            return await response.json();
        } catch (e) {
            return { message: 'Unexpected response from the server.' };
        }
    }

    function makeError(data, status) {
        const error = new Error(data && data.message ? data.message : 'Request failed.');
        error.status = status;
        error.errors = (data && data.errors) || {};
        error.body = data;
        return error;
    }

    async function refresh() {
        const current = token();

        if (!current) {
            return false;
        }

        const response = await fetch('/api/auth/refresh', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                Authorization: 'Bearer ' + current,
            },
        });

        if (!response.ok) {
            return false;
        }

        const body = await parse(response);

        if (!body.success || !body.data || !body.data.access_token) {
            return false;
        }

        localStorage.setItem(TOKEN_KEY, body.data.access_token);

        if (body.data.user) {
            localStorage.setItem(USER_KEY, JSON.stringify(body.data.user));
        }

        return true;
    }

    async function request(method, path, options) {
        options = options || {};

        const send = async () => {
            const headers = { Accept: 'application/json' };
            let body = null;

            if (!options.skipAuth && token()) {
                headers.Authorization = 'Bearer ' + token();
            }

            if (options.form instanceof FormData) {
                body = options.form;
            } else if (options.body !== undefined && options.body !== null) {
                headers['Content-Type'] = 'application/json';
                body = JSON.stringify(options.body);
            }

            return fetch('/api' + path, { method: method, headers: headers, body: body });
        };

        let response = await send();

        if (response.status === 401 && !options.skipAuth && !options.skipRefresh && token()) {
            if (await refresh()) {
                response = await send();
            } else {
                clearAuth();
                window.location.replace('/login');
                throw makeError({ message: 'Your session expired. Please log in again.' }, 401);
            }
        }

        const data = await parse(response);

        if (!response.ok) {
            throw makeError(data, response.status);
        }

        return data;
    }

    window.Api = {
        get: function (path, options) {
            return request('GET', path, options);
        },
        post: function (path, body, options) {
            return request('POST', path, Object.assign({}, options || {}, { body: body }));
        },
        put: function (path, body, options) {
            return request('PUT', path, Object.assign({}, options || {}, { body: body }));
        },
        delete: function (path, options) {
            return request('DELETE', path, options);
        },
        postForm: function (path, form, options) {
            return request('POST', path, Object.assign({}, options || {}, { form: form }));
        },
        refresh: refresh,
        token: token,
        logout: async function () {
            try {
                await request('POST', '/auth/logout', { skipRefresh: true });
            } catch (e) {
                // The local session is cleared regardless of the API result.
            }

            clearAuth();
            window.location.replace('/login');
        },
    };

    window.firstError = function (error) {
        const errors = (error && error.errors) || {};
        const fields = Object.keys(errors);

        if (fields.length > 0) {
            const messages = errors[fields[0]];

            if (Array.isArray(messages) && messages.length > 0) {
                return messages[0];
            }

            if (typeof messages === 'string') {
                return messages;
            }
        }

        return (error && error.message) || 'Something went wrong.';
    };
})();
