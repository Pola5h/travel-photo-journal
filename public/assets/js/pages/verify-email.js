function verifyEmailPage() {
    return {
        status: 'verifying',
        error: '',
        async init() {
            const params = new URLSearchParams(window.location.search);
            const token = params.get('token');
            const email = params.get('email');

            if (!token || !email) {
                this.status = 'error';
                this.error = 'This verification link is incomplete.';
                return;
            }

            try {
                await Api.post('/auth/verify-email', { token: token, email: email }, { skipAuth: true, skipRefresh: true });
                this.status = 'done';
            } catch (e) {
                this.status = 'error';
                this.error = firstError(e);
            }
        },
    };
}
