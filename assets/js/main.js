/**
 * Barangay Zone 12-A Portal - Frontend Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggler
    const navToggle = document.getElementById('nav-toggle');
    const navMenu = document.getElementById('nav-menu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('open');
            const isOpen = navMenu.classList.contains('open');
            // Change hamburger icon character or look if wanted, e.g., toggle text content
            navToggle.innerHTML = isOpen ? '&times;' : '&#9776;';
        });
    }

    // Close menu when clicking a link (useful for hash links)
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu && navMenu.classList.contains('open')) {
                navMenu.classList.remove('open');
                if (navToggle) navToggle.innerHTML = '&#9776;';
            }
        });
    });

    // 2. Contact Form Client-side Validation
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            let isValid = true;
            
            // Name field validation
            const nameInput = document.getElementById('name');
            if (nameInput) {
                const nameVal = nameInput.value.trim();
                const parent = nameInput.parentElement;
                if (nameVal.length < 2) {
                    parent.classList.add('invalid');
                    isValid = false;
                } else {
                    parent.classList.remove('invalid');
                }
            }
            
            // Email field validation
            const emailInput = document.getElementById('email');
            if (emailInput) {
                const emailVal = emailInput.value.trim();
                const parent = emailInput.parentElement;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailVal)) {
                    parent.classList.add('invalid');
                    isValid = false;
                } else {
                    parent.classList.remove('invalid');
                }
            }

            // Message field validation
            const messageInput = document.getElementById('message');
            if (messageInput) {
                const messageVal = messageInput.value.trim();
                const parent = messageInput.parentElement;
                if (messageVal.length < 10) {
                    parent.classList.add('invalid');
                    isValid = false;
                } else {
                    parent.classList.remove('invalid');
                }
            }

            if (!isValid) {
                e.preventDefault(); // Stop submission if invalid
            }
        });

        // Real-time error removal on input
        const inputs = contactForm.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                const parent = input.parentElement;
                if (parent.classList.contains('invalid')) {
                    // Check if input has contents or matches pattern
                    if (input.id === 'name' && input.value.trim().length >= 2) {
                        parent.classList.remove('invalid');
                    }
                    if (input.id === 'email' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) {
                        parent.classList.remove('invalid');
                    }
                    if (input.id === 'message' && input.value.trim().length >= 10) {
                        parent.classList.remove('invalid');
                    }
                }
            });
        });
    }
});
