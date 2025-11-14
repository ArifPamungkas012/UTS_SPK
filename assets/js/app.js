document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const page = params.get('page') || 'dashboard';

    document.querySelectorAll('.menu-link').forEach(link => {
        if (link.href.includes('page=' + page)) {
            link.classList.add('active');
        }
    });
});
