<script>
document.addEventListener('DOMContentLoaded', function() {
    const ssoLoginUrl = '{{ route("sso.login") }}';
    const ssoLogoutUrl = '{{ route("sso.logout") }}';

    // Intercept all links pointing to common auth paths
    const authKeywords = ['login', 'register', 'signup', 'forgot-password', 'password/reset'];
    
    document.querySelectorAll('a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && authKeywords.some(keyword => href.includes(keyword))) {
            link.setAttribute('href', ssoLoginUrl);
        }
        if (href && href.includes('logout')) {
            link.setAttribute('href', ssoLogoutUrl);
        }
    });

    // Intercept forms submitting to auth paths
    document.querySelectorAll('form').forEach(form => {
        const action = form.getAttribute('action');
        if (action && authKeywords.some(keyword => action.includes(keyword))) {
            form.setAttribute('action', ssoLoginUrl);
            form.method = 'GET'; // Force redirect to SSO login
        }
    });
});
</script>