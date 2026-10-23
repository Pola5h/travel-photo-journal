function loginPage() {
    return {
        email: '',
        password: '',
        otp: '',
        twoFactorRequired: false,
        loading: false,
        error: '',
        success: '',
        async submit() {
            this.loading = true;
            this.error = '';

            try {
                const payload = {
                    email: this.email,
                    password: this.password,
                };

                if (this.twoFactorRequired && this.otp) {
                    payload.otp = this.otp;
                }

                const response = await Api.post('/auth/login', payload, { skipAuth: true, skipRefresh: true });

                AuthStore.set(response.data.access_token, response.data.user);

                window.location.href = '/photos';
            } catch (e) {
                if (e.status === 401 && e.body && e.body.two_factor_required) {
                    this.twoFactorRequired = true;
                    this.error = 'Enter the code from your authenticator app.';
                } else {
                    this.error = firstError(e);
                }
            } finally {
                this.loading = false;
            }
        },
    };
}
