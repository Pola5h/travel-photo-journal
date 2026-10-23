function securityPage() {
    return {
        enabled: false,
        secret: '',
        qrSrc: '',
        otp: '',
        loading: false,
        error: '',
        success: '',
        init() {
            const user = AuthStore.user();
            this.enabled = !!(user && user.two_factor_enabled);
        },
        async setup() {
            this.loading = true;
            this.error = '';
            this.success = '';

            try {
                const response = await Api.post('/auth/2fa/setup');

                this.secret = response.data.secret;
                this.qrSrc = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(response.data.qr_svg);
            } catch (e) {
                if (e.status === 409) {
                    this.enabled = true;
                    this.error = 'Two-factor authentication is already enabled.';
                } else {
                    this.error = firstError(e);
                }
            } finally {
                this.loading = false;
            }
        },
        async verify() {
            this.loading = true;
            this.error = '';
            this.success = '';

            try {
                await Api.post('/auth/2fa/verify', { otp: this.otp });

                this.enabled = true;
                this.secret = '';
                this.qrSrc = '';
                this.otp = '';
                this.success = 'Two-factor authentication enabled.';

                const user = AuthStore.user();

                if (user) {
                    user.two_factor_enabled = true;
                    AuthStore.set(AuthStore.token(), user);
                }
            } catch (e) {
                this.error = firstError(e);
            } finally {
                this.loading = false;
            }
        },
    };
}
