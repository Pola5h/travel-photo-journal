function registerPage() {
    return {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        loading: false,
        error: '',
        success: '',
        async submit() {
            this.loading = true;
            this.error = '';

            try {
                await Api.post('/auth/register', {
                    name: this.name,
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.password_confirmation,
                }, { skipAuth: true, skipRefresh: true });

                window.location.href = '/check-your-email?email=' + encodeURIComponent(this.email);
            } catch (e) {
                this.error = firstError(e);
            } finally {
                this.loading = false;
            }
        },
    };
}
