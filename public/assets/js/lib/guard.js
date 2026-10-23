(function () {
    if (!localStorage.getItem('auth_token')) {
        window.location.replace('/login');
    }
})();
