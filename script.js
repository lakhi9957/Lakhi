// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initNavigation();
    initScrollEffects();
    initAnimations();
    initForms();
    initMobileMenu();
    initCounters();
    initCourseCards();
    initPWA();
});

// Initialize PWA features
function initPWA() {
    // Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                    
                    // Check for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                showNotification('App updated! Refresh to see changes.', 'info');
                            }
                        });
                    });
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
    
    // Install prompt
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        showInstallPrompt();
    });
    
    // Handle app installation
    window.addEventListener('appinstalled', (evt) => {
        console.log('App was installed');
        showNotification('App installed successfully! Welcome to Excellence Academy!', 'success');
    });
}

// Show install prompt
function showInstallPrompt() {
    const installBanner = document.createElement('div');
    installBanner.className = 'install-banner';
    installBanner.innerHTML = `
        <div class="install-content">
            <i class="fas fa-mobile-alt"></i>
            <div class="install-text">
                <h4>Install Excellence Academy App</h4>
                <p>Get the full app experience with offline access!</p>
            </div>
            <button class="btn-install" onclick="installApp()">Install</button>
            <button class="btn-close" onclick="closeInstallPrompt()">&times;</button>
        </div>
    `;
    
    // Add install banner styles
    if (!document.querySelector('#install-styles')) {
        const style = document.createElement('style');
        style.id = 'install-styles';
        style.textContent = `
            .install-banner {
                position: fixed;
                bottom: 20px;
                left: 20px;
                right: 20px;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: white;
                padding: 20px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                z-index: 2000;
                transform: translateY(100%);
                animation: slideUp 0.5s ease forwards;
            }
            .install-content {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            .install-content i {
                font-size: 2rem;
                color: #fbbf24;
            }
            .install-text {
                flex: 1;
            }
            .install-text h4 {
                margin: 0 0 5px 0;
                font-size: 1.1rem;
            }
            .install-text p {
                margin: 0;
                font-size: 0.9rem;
                opacity: 0.9;
            }
            .btn-install {
                background: #fbbf24;
                color: #1f2937;
                border: none;
                padding: 10px 20px;
                border-radius: 25px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            .btn-install:hover {
                background: #f59e0b;
                transform: translateY(-2px);
            }
            .btn-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
                padding: 5px;
                margin-left: 10px;
            }
            @keyframes slideUp {
                to { transform: translateY(0); }
            }
            @media (max-width: 768px) {
                .install-banner {
                    left: 10px;
                    right: 10px;
                    bottom: 10px;
                }
                .install-content {
                    flex-direction: column;
                    text-align: center;
                    gap: 10px;
                }
                .btn-close {
                    position: absolute;
                    top: 10px;
                    right: 15px;
                    margin: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(installBanner);
    
    // Auto hide after 10 seconds
    setTimeout(() => {
        if (document.querySelector('.install-banner')) {
            closeInstallPrompt();
        }
    }, 10000);
}

// Install app function
function installApp() {
    const installBanner = document.querySelector('.install-banner');
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the install prompt');
            } else {
                console.log('User dismissed the install prompt');
            }
            deferredPrompt = null;
            if (installBanner) installBanner.remove();
        });
    }
}

// Close install prompt
function closeInstallPrompt() {
    const installBanner = document.querySelector('.install-banner');
    if (installBanner) {
        installBanner.style.transform = 'translateY(100%)';
        setTimeout(() => installBanner.remove(), 300);
    }
}

// Navigation functionality
function initNavigation() {
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Smooth scrolling for navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 80; // Account for fixed navbar
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
            
            // Close mobile menu if open
            const navMenu = document.querySelector('.nav-menu');
            navMenu.classList.remove('active');
        });
    });
    
    // Active link highlighting
    window.addEventListener('scroll', function() {
        let current = '';
        const sections = document.querySelectorAll('section[id]');
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.clientHeight;
            
            if (scrollY >= sectionTop && scrollY <= sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
}

// Scroll effects
function initScrollEffects() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.15)';
        } else {
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
        }
    });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animateElements = document.querySelectorAll('.course-card, .teacher-card, .stat-item');
    animateElements.forEach(el => observer.observe(el));
}

// Initialize animations
function initAnimations() {
    // Add entrance animations to elements
    const animatedElements = document.querySelectorAll('.course-card, .teacher-card');
    
    animatedElements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1 + 0.3}s`;
    });
}

// Form handling
function initForms() {
    // Enrollment form
    const enrollmentForm = document.querySelector('.enrollment-form');
    if (enrollmentForm) {
        enrollmentForm.addEventListener('submit', handleEnrollmentSubmit);
    }
    
    // Contact form
    const contactForm = document.querySelector('.contact-form form');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactSubmit);
    }
    
    // Course card "Learn More" buttons
    const learnMoreBtns = document.querySelectorAll('.course-card .btn-outline');
    learnMoreBtns.forEach(btn => {
        btn.addEventListener('click', handleCourseLearnMore);
    });
}

// Handle enrollment form submission
function handleEnrollmentSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const enrollmentData = {
        name: e.target.querySelector('input[type="text"]').value,
        email: e.target.querySelector('input[type="email"]').value,
        phone: e.target.querySelector('input[type="tel"]').value,
        grade: e.target.querySelectorAll('select')[0].value,
        course: e.target.querySelectorAll('select')[1].value,
        time: e.target.querySelectorAll('select')[2].value,
        message: e.target.querySelector('textarea').value
    };
    
    // Validate form
    if (!validateEnrollmentForm(enrollmentData)) {
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Success! Your enrollment application has been submitted. We will contact you soon.', 'success');
        e.target.reset();
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }, 2000);
}

// Handle contact form submission
function handleContactSubmit(e) {
    e.preventDefault();
    
    const formData = {
        name: e.target.querySelector('input[placeholder="Your Name"]').value,
        email: e.target.querySelector('input[placeholder="Your Email"]').value,
        subject: e.target.querySelector('input[placeholder="Subject"]').value,
        message: e.target.querySelector('textarea').value
    };
    
    // Validate form
    if (!validateContactForm(formData)) {
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        showNotification('Thank you! Your message has been sent successfully.', 'success');
        e.target.reset();
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }, 1500);
}

// Handle course "Learn More" button clicks
function handleCourseLearnMore(e) {
    e.preventDefault();
    const courseCard = e.target.closest('.course-card');
    const courseName = courseCard.querySelector('h3').textContent;
    
    showCourseModal(courseName);
}

// Show course details modal
function showCourseModal(courseName) {
    const courseDetails = {
        'Mathematics': {
            description: 'Comprehensive mathematics program covering algebra, geometry, calculus, and advanced topics.',
            features: ['Individual attention', 'Problem-solving techniques', 'Regular assessments', 'Exam preparation'],
            duration: '12 months',
            schedule: 'Mon, Wed, Fri - 2 hours each',
            prerequisites: 'Basic arithmetic knowledge'
        },
        'Science': {
            description: 'In-depth science education covering Physics, Chemistry, and Biology with practical experiments.',
            features: ['Laboratory sessions', 'Real-world applications', 'Interactive learning', 'Project-based learning'],
            duration: '12 months',
            schedule: 'Tue, Thu, Sat - 2 hours each',
            prerequisites: 'Grade 6+ science background'
        },
        'English': {
            description: 'Complete English language program focusing on reading, writing, speaking, and literature.',
            features: ['Grammar fundamentals', 'Creative writing', 'Literature analysis', 'Communication skills'],
            duration: '10 months',
            schedule: 'Mon, Wed, Fri - 1.5 hours each',
            prerequisites: 'Basic reading ability'
        },
        'Computer Science': {
            description: 'Modern computer science curriculum including programming, web development, and technology fundamentals.',
            features: ['Programming languages', 'Web development', 'Database management', 'Project portfolio'],
            duration: '15 months',
            schedule: 'Tue, Thu, Sat - 2.5 hours each',
            prerequisites: 'Basic computer literacy'
        }
    };
    
    const details = courseDetails[courseName];
    if (details) {
        const modal = createModal(courseName, details);
        document.body.appendChild(modal);
        setTimeout(() => modal.classList.add('show'), 10);
    }
}

// Create modal for course details
function createModal(courseName, details) {
    const modal = document.createElement('div');
    modal.className = 'course-modal';
    modal.innerHTML = `
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>${courseName} Course</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="course-description">${details.description}</p>
                <div class="course-info">
                    <div class="info-item">
                        <h4>Duration</h4>
                        <p>${details.duration}</p>
                    </div>
                    <div class="info-item">
                        <h4>Schedule</h4>
                        <p>${details.schedule}</p>
                    </div>
                    <div class="info-item">
                        <h4>Prerequisites</h4>
                        <p>${details.prerequisites}</p>
                    </div>
                </div>
                <div class="course-features">
                    <h4>Course Features</h4>
                    <ul>
                        ${details.features.map(feature => `<li>${feature}</li>`).join('')}
                    </ul>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-primary" onclick="scrollToEnrollment()">Enroll Now</button>
                    <button class="btn btn-outline" onclick="closeModal()">Close</button>
                </div>
            </div>
        </div>
    `;
    
    // Add modal styles
    const style = document.createElement('style');
    style.textContent = `
        .course-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .course-modal.show {
            opacity: 1;
            visibility: visible;
        }
        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        .modal-content {
            position: relative;
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: translateY(50px);
            transition: transform 0.3s ease;
        }
        .course-modal.show .modal-content {
            transform: translateY(0);
        }
        .modal-header {
            padding: 30px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #6b7280;
        }
        .modal-body {
            padding: 30px;
        }
        .course-description {
            font-size: 1.1rem;
            margin-bottom: 30px;
            color: #6b7280;
        }
        .course-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            text-align: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 10px;
        }
        .info-item h4 {
            color: #2563eb;
            margin-bottom: 10px;
        }
        .course-features h4 {
            margin-bottom: 15px;
            color: #1f2937;
        }
        .course-features ul {
            list-style: none;
            padding: 0;
        }
        .course-features li {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .course-features li::before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            margin-right: 10px;
        }
        .modal-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
    `;
    
    if (!document.querySelector('#modal-styles')) {
        style.id = 'modal-styles';
        document.head.appendChild(style);
    }
    
    // Add event listeners
    modal.querySelector('.modal-close').addEventListener('click', () => closeModal());
    modal.querySelector('.modal-overlay').addEventListener('click', () => closeModal());
    
    return modal;
}

// Close modal
function closeModal() {
    const modal = document.querySelector('.course-modal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    }
}

// Scroll to enrollment section
function scrollToEnrollment() {
    closeModal();
    const enrollmentSection = document.querySelector('#enrollment');
    const offsetTop = enrollmentSection.offsetTop - 80;
    window.scrollTo({
        top: offsetTop,
        behavior: 'smooth'
    });
}

// Mobile menu functionality
function initMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    hamburger.addEventListener('click', function() {
        navMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        }
    });
}

// Animated counters
function initCounters() {
    const counters = document.querySelectorAll('.stat-item h3');
    const observerOptions = {
        threshold: 0.5
    };
    
    const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                animateCounter(entry.target);
                entry.target.classList.add('counted');
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => counterObserver.observe(counter));
}

// Animate counter numbers
function animateCounter(element) {
    const target = parseInt(element.textContent.replace(/\D/g, ''));
    const suffix = element.textContent.replace(/\d/g, '');
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target + suffix;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current) + suffix;
        }
    }, 16);
}

// Course cards interaction
function initCourseCards() {
    const courseCards = document.querySelectorAll('.course-card');
    
    courseCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-15px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Form validation
function validateEnrollmentForm(data) {
    const errors = [];
    
    if (!data.name || data.name.trim().length < 2) {
        errors.push('Please enter a valid name');
    }
    
    if (!data.email || !isValidEmail(data.email)) {
        errors.push('Please enter a valid email address');
    }
    
    if (!data.phone || data.phone.trim().length < 10) {
        errors.push('Please enter a valid phone number');
    }
    
    if (!data.grade) {
        errors.push('Please select a grade');
    }
    
    if (!data.course) {
        errors.push('Please select a course');
    }
    
    if (!data.time) {
        errors.push('Please select a preferred time');
    }
    
    if (errors.length > 0) {
        showNotification(errors.join('\n'), 'error');
        return false;
    }
    
    return true;
}

function validateContactForm(data) {
    const errors = [];
    
    if (!data.name || data.name.trim().length < 2) {
        errors.push('Please enter a valid name');
    }
    
    if (!data.email || !isValidEmail(data.email)) {
        errors.push('Please enter a valid email address');
    }
    
    if (!data.subject || data.subject.trim().length < 3) {
        errors.push('Please enter a subject');
    }
    
    if (!data.message || data.message.trim().length < 10) {
        errors.push('Please enter a message (at least 10 characters)');
    }
    
    if (errors.length > 0) {
        showNotification(errors.join('\n'), 'error');
        return false;
    }
    
    return true;
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    // Add notification styles
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 100px;
                right: 20px;
                z-index: 3000;
                max-width: 400px;
                padding: 20px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }
            .notification.show {
                transform: translateX(0);
            }
            .notification-success {
                background: #10b981;
                color: white;
            }
            .notification-error {
                background: #ef4444;
                color: white;
            }
            .notification-info {
                background: #3b82f6;
                color: white;
            }
            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .notification-close {
                background: none;
                border: none;
                color: white;
                font-size: 1.2rem;
                cursor: pointer;
                margin-left: auto;
            }
            .notification span {
                flex: 1;
                white-space: pre-line;
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => notification.classList.add('show'), 10);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
    
    // Close button functionality
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });
}

// Get notification icon based on type
function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'info': return 'info-circle';
        default: return 'info-circle';
    }
}

// Lazy loading for images (if you add images later)
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Keyboard navigation support
document.addEventListener('keydown', function(e) {
    // Close modal with Escape key
    if (e.key === 'Escape') {
        closeModal();
    }
    
    // Close notification with Escape key
    if (e.key === 'Escape') {
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }
    }
});

// Performance optimization: Debounce scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debouncing to scroll events
window.addEventListener('scroll', debounce(() => {
    // Scroll-related functions can be called here
}, 10));

console.log('Excellence Academy website loaded successfully! 🎓');