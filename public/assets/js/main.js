/**
 * Polara - Responsive Navbar, Search & Login Interaction
 * Diadaptasi dari referensi bedimcode dengan peningkatan integrasi CI4
 */

document.addEventListener('DOMContentLoaded', () => {
    /*=============== SHOW MENU (MOBILE) ===============*/
    const navMenu = document.getElementById('nav-menu');
    const navToggle = document.getElementById('nav-toggle');
    const navClose = document.getElementById('nav-close');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.add('show-menu');
        });
    }

    if (navClose && navMenu) {
        navClose.addEventListener('click', () => {
            navMenu.classList.remove('show-menu');
        });
    }

    // Tutup menu mobile ketika link navigasi diklik
    const navLinks = document.querySelectorAll('.nav__link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu) {
                navMenu.classList.remove('show-menu');
            }
        });
    });

    /*=============== SEARCH OVERLAY ===============*/
    const searchModal = document.getElementById('search');
    const searchBtn = document.getElementById('search-btn');
    const searchClose = document.getElementById('search-close');
    const searchInput = document.querySelector('.search__input');

    if (searchBtn && searchModal) {
        searchBtn.addEventListener('click', () => {
            searchModal.classList.add('show-search');
            if (loginModal && !loginModal.classList.contains('show-login')) {
                loginModal.classList.remove('show-login');
            }
            setTimeout(() => {
                if (searchInput) searchInput.focus();
            }, 150);
        });
    }

    if (searchClose && searchModal) {
        searchClose.addEventListener('click', () => {
            searchModal.classList.remove('show-search');
        });
    }

    /*=============== LOGIN OVERLAY ===============*/
    const loginModal = document.getElementById('login');
    const loginBtn = document.getElementById('login-btn');
    const loginClose = document.getElementById('login-close');
    const heroLoginBtn = document.getElementById('hero-login-btn');
    const usernameInput = document.getElementById('username');

    function openLogin() {
        if (loginModal) {
            loginModal.classList.add('show-login');
            if (searchModal) searchModal.classList.remove('show-search');
            if (navMenu) navMenu.classList.remove('show-menu');
            setTimeout(() => {
                if (usernameInput && !usernameInput.value) {
                    usernameInput.focus();
                }
            }, 200);
        }
    }

    function closeLogin() {
        // Jika loginClose adalah anchor tag dengan href, biarkan browser bernavigasi ke beranda
        if (loginClose && loginClose.tagName === 'A' && loginClose.getAttribute('href')) {
            window.location.href = loginClose.getAttribute('href');
            return;
        }

        if (loginModal) {
            loginModal.classList.remove('show-login');
        }
    }

    // Hanya bind click handler jika elemen bukan link anchor standar
    if (loginBtn && loginBtn.tagName !== 'A' && !loginBtn.closest('a')) {
        loginBtn.addEventListener('click', openLogin);
    }

    const navLoginWrapper = document.getElementById('nav-login-wrapper');
    if (navLoginWrapper && navLoginWrapper.tagName !== 'A') {
        navLoginWrapper.addEventListener('click', openLogin);
    }

    if (heroLoginBtn && heroLoginBtn.tagName !== 'A') {
        heroLoginBtn.addEventListener('click', openLogin);
    }

    if (loginClose) {
        loginClose.addEventListener('click', (e) => {
            if (loginClose.tagName === 'A' && loginClose.getAttribute('href')) {
                // Biarkan event navigasi tautan berjalan normal ke Beranda
                return;
            }
            e.preventDefault();
            closeLogin();
        });
    }

    /*=============== BACKDROP CLICK TO CLOSE ===============*/
    if (loginModal) {
        loginModal.addEventListener('click', (e) => {
            if (e.target === loginModal) {
                closeLogin();
            }
        });
    }

    if (searchModal) {
        searchModal.addEventListener('click', (e) => {
            if (e.target === searchModal) {
                searchModal.classList.remove('show-search');
            }
        });
    }

    /*=============== KEYBOARD ESCAPE TO CLOSE ===============*/
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (loginModal && loginModal.classList.contains('show-login')) {
                closeLogin();
            }
            if (searchModal && searchModal.classList.contains('show-search')) {
                searchModal.classList.remove('show-search');
            }
            if (navMenu && navMenu.classList.contains('show-menu')) {
                navMenu.classList.remove('show-menu');
            }
        }
    });

    /*=============== TOGGLE PASSWORD VISIBILITY ===============*/
    const btnTogglePassword = document.getElementById('btnTogglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (btnTogglePassword && passwordInput && eyeIcon) {
        btnTogglePassword.addEventListener('click', (e) => {
            e.preventDefault();
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            eyeIcon.className = isPassword ? 'ri-eye-off-line' : 'ri-eye-line';
        });
    }

    /*=============== AUTO-FOCUS ON LOGIN PAGE ONLY ===============*/
    // Jika halaman ini memang memiliki overlay form login aktif (.show-login), fokuskan ke input username
    if (loginModal && loginModal.classList.contains('show-login')) {
        setTimeout(() => {
            if (usernameInput && !usernameInput.value) {
                usernameInput.focus();
            }
        }, 200);
    }
});
