/**
 * MakersLab - Main JavaScript
 * Warsztaty robotyki i elektroniki dla dzieci
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modules
    initMobileMenu();
    initNavbarScroll();
    initScrollAnimations();
    initSmoothScroll();
    initFormValidation();
    initAllegroTracking();
});

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');

    if (!menuToggle || !navLinks) return;

    menuToggle.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        
        // Animate hamburger to X
        this.classList.toggle('active');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!menuToggle.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    });

    // Close menu when clicking a link
    navLinks.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        });
    });
}

/**
 * Navbar Scroll Effect
 */
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;

    let lastScroll = 0;

    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;

        // Add scrolled class after 50px
        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        // Optional: Hide navbar on scroll down, show on scroll up
        // if (currentScroll > lastScroll && currentScroll > 300) {
        //     navbar.style.transform = 'translateY(-100%)';
        // } else {
        //     navbar.style.transform = 'translateY(0)';
        // }

        lastScroll = currentScroll;
    });
}

/**
 * Scroll Animations (Intersection Observer)
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                // Optional: unobserve after animation
                // observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all elements with animate-on-scroll class
    document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
        observer.observe(el);
    });

    // Also animate module cards with stagger effect
    const moduleCards = document.querySelectorAll('.module-card');
    moduleCards.forEach(function(card, index) {
        card.style.transitionDelay = (index * 0.1) + 's';
        observer.observe(card);
    });
}

/**
 * Smooth Scroll for Anchor Links
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if (!target) return;

            const navbarHeight = document.querySelector('.navbar').offsetHeight;
            const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight - 20;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        });
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const form = document.querySelector('.contact-form form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];

        // Email validation
        const emailInput = form.querySelector('input[name="email"]');
        if (emailInput && emailInput.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value)) {
                isValid = false;
                errors.push('Proszę podać prawidłowy adres email');
                emailInput.style.borderColor = '#ff6b35';
            } else {
                emailInput.style.borderColor = '';
            }
        }

        // Phone validation (optional, but if provided should be valid)
        const phoneInput = form.querySelector('input[name="phone"]');
        if (phoneInput && phoneInput.value) {
            const phoneRegex = /^[\d\s\+\-\(\)]{9,}$/;
            if (!phoneRegex.test(phoneInput.value)) {
                isValid = false;
                errors.push('Proszę podać prawidłowy numer telefonu');
                phoneInput.style.borderColor = '#ff6b35';
            } else {
                phoneInput.style.borderColor = '';
            }
        }

        // Age validation
        const ageInput = form.querySelector('input[name="child_age"]');
        if (ageInput && ageInput.value) {
            const age = parseInt(ageInput.value);
            if (age < 6 || age > 18) {
                isValid = false;
                errors.push('Wiek dziecka powinien być między 6 a 18 lat');
                ageInput.style.borderColor = '#ff6b35';
            } else {
                ageInput.style.borderColor = '';
            }
        }

        if (!isValid) {
            e.preventDefault();
            showFormErrors(errors);
        }
    });

    // Clear error styling on input
    form.querySelectorAll('input, select, textarea').forEach(function(input) {
        input.addEventListener('input', function() {
            this.style.borderColor = '';
        });
    });
}

/**
 * Show Form Errors
 */
function showFormErrors(errors) {
    // Remove existing error messages
    const existingErrors = document.querySelector('.form-errors');
    if (existingErrors) {
        existingErrors.remove();
    }

    // Create error container
    const errorContainer = document.createElement('div');
    errorContainer.className = 'form-errors form-message error';
    errorContainer.innerHTML = errors.join('<br>');

    // Insert before form
    const form = document.querySelector('.contact-form form');
    form.insertBefore(errorContainer, form.firstChild);

    // Scroll to errors
    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Auto-remove after 5 seconds
    setTimeout(function() {
        errorContainer.remove();
    }, 5000);
}

/**
 * Allegro Link Tracking
 * Tracks clicks on Allegro links for analytics
 */
function initAllegroTracking() {
    document.querySelectorAll('.allegro-link, .kit-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            const moduleName = this.closest('.module-card, .kit-card')?.querySelector('.module-title, .kit-name')?.textContent || 'Unknown';
            
            // Track with Google Analytics (if available)
            if (typeof gtag === 'function') {
                gtag('event', 'click', {
                    'event_category': 'Allegro Link',
                    'event_label': moduleName
                });
            }

            // Track with custom analytics (console log for now)
            console.log('Allegro click:', moduleName);

            // Optional: Track with your own analytics endpoint
            // fetch('/api/track', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ event: 'allegro_click', module: moduleName })
            // });
        });
    });
}

/**
 * Lazy Load Images
 */
function initLazyLoad() {
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(function(img) {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for older browsers
        lazyImages.forEach(function(img) {
            img.src = img.dataset.src;
        });
    }
}

/**
 * Counter Animation
 * Animate numbers counting up when visible
 */
function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(function(counter) {
        observer.observe(counter);
    });
}

function animateCounter(element) {
    const target = parseInt(element.dataset.counter);
    const duration = 2000; // 2 seconds
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const timer = setInterval(function() {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

/**
 * Typing Effect for Hero Title
 */
function initTypingEffect() {
    const typingElement = document.querySelector('[data-typing]');
    if (!typingElement) return;

    const text = typingElement.dataset.typing;
    typingElement.textContent = '';
    let index = 0;

    function type() {
        if (index < text.length) {
            typingElement.textContent += text.charAt(index);
            index++;
            setTimeout(type, 100);
        }
    }

    type();
}

/**
 * Parallax Effect for Hero
 */
function initParallax() {
    const hero = document.querySelector('.hero');
    if (!hero) return;

    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const heroCard = hero.querySelector('.hero-card');
        
        if (heroCard && scrolled < window.innerHeight) {
            heroCard.style.transform = `translateY(${scrolled * 0.1}px)`;
        }
    });
}

/**
 * Dark/Light Mode Toggle (future feature)
 */
function initThemeToggle() {
    const toggle = document.querySelector('.theme-toggle');
    if (!toggle) return;

    const currentTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);

    toggle.addEventListener('click', function() {
        const newTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
}

/**
 * Utility Functions
 */

// Debounce function for scroll/resize events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = function() {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Format price
function formatPrice(price) {
    return new Intl.NumberFormat('pl-PL', {
        style: 'currency',
        currency: 'PLN'
    }).format(price);
}

/**
 * Cookie Consent (GDPR)
 */
function initCookieConsent() {
    const consent = localStorage.getItem('cookieConsent');
    if (consent) return;

    const banner = document.createElement('div');
    banner.className = 'cookie-banner';
    banner.innerHTML = `
        <div class="cookie-content">
            <p>Ta strona używa plików cookies w celu świadczenia usług na najwyższym poziomie.</p>
            <button class="btn btn-primary btn-sm cookie-accept">Akceptuję</button>
        </div>
    `;
    document.body.appendChild(banner);

    banner.querySelector('.cookie-accept').addEventListener('click', function() {
        localStorage.setItem('cookieConsent', 'true');
        banner.remove();
    });
}

// Uncomment to enable cookie consent
// initCookieConsent();
