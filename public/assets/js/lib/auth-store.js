(function () {
    const TOKEN_KEY = 'auth_token';
    const USER_KEY = 'auth_user';

    window.AuthStore = {
        token: function () {
            return localStorage.getItem(TOKEN_KEY);
        },
        user: function () {
            try {
                return JSON.parse(localStorage.getItem(USER_KEY) || 'null');
            } catch (e) {
                return null;
            }
        },
        set: function (token, user) {
            if (token) {
                localStorage.setItem(TOKEN_KEY, token);
            }

            if (user !== undefined) {
                localStorage.setItem(USER_KEY, JSON.stringify(user));
            }
        },
        clear: function () {
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);
        },
    };

    document.addEventListener('alpine:init', function () {
        Alpine.store('auth', {
            user: window.AuthStore.user(),
            get isAuthenticated() {
                return !!window.AuthStore.token();
            },
        });
    });
})();
