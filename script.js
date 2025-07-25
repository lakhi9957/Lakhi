// School Website JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the website
    initGallery();
    initNotices();
    initSmoothScrolling();
    initAnimations();
});

// Gallery functionality
function initGallery() {
    const galleryContainer = document.getElementById('gallery-container');
    
    // Sample gallery data (in real app, this would come from admin panel)
    const galleryImages = [
        {
            id: 1,
            title: 'School Building',
            image: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400&h=300&fit=crop',
            category: 'Infrastructure'
        },
        {
            id: 2,
            title: 'Science Laboratory',
            image: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=400&h=300&fit=crop',
            category: 'Facilities'
        },
        {
            id: 3,
            title: 'Library',
            image: 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400&h=300&fit=crop',
            category: 'Facilities'
        },
        {
            id: 4,
            title: 'Sports Field',
            image: 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=300&fit=crop',
            category: 'Sports'
        },
        {
            id: 5,
            title: 'Computer Lab',
            image: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400&h=300&fit=crop',
            category: 'Technology'
        },
        {
            id: 6,
            title: 'Graduation Ceremony',
            image: 'https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=400&h=300&fit=crop',
            category: 'Events'
        }
    ];

    // Load gallery images
    loadGalleryImages(galleryImages);
}

function loadGalleryImages(images) {
    const galleryContainer = document.getElementById('gallery-container');
    
    if (!galleryContainer) return;
    
    // Show loading state
    galleryContainer.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
    
    // Simulate loading delay
    setTimeout(() => {
        galleryContainer.innerHTML = '';
        
        images.forEach((image, index) => {
            const galleryItem = createGalleryItem(image, index);
            galleryContainer.appendChild(galleryItem);
        });
        
        // Add fade-in animation
        setTimeout(() => {
            const items = galleryContainer.querySelectorAll('.gallery-item');
            items.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.add('fade-in-up');
                }, index * 100);
            });
        }, 100);
    }, 1000);
}

function createGalleryItem(image, index) {
    const colDiv = document.createElement('div');
    colDiv.className = 'col-lg-4 col-md-6';
    
    colDiv.innerHTML = `
        <div class="gallery-item">
            <img src="${image.image}" alt="${image.title}" loading="lazy">
            <div class="gallery-overlay">
                <h5>${image.title}</h5>
            </div>
        </div>
    `;
    
    return colDiv;
}

// Notices functionality
function initNotices() {
    loadNotices();
}

function loadNotices() {
    const noticesContainer = document.getElementById('notices-container');
    
    if (!noticesContainer) return;
    
    // Sample notices data (in real app, this would come from admin panel)
    const notices = [
        {
            id: 1,
            title: 'Annual Sports Day 2024',
            content: 'Join us for our annual sports day celebration on March 15th, 2024. All students are encouraged to participate in various sporting events.',
            date: '2024-01-15',
            priority: 'high'
        },
        {
            id: 2,
            title: 'Parent-Teacher Meeting',
            content: 'Monthly parent-teacher meeting scheduled for January 25th, 2024. Please confirm your attendance with the class teacher.',
            date: '2024-01-10',
            priority: 'medium'
        },
        {
            id: 3,
            title: 'Science Fair Registration Open',
            content: 'Registration for the annual science fair is now open. Students can submit their project proposals until February 1st, 2024.',
            date: '2024-01-08',
            priority: 'low'
        },
        {
            id: 4,
            title: 'Winter Break Schedule',
            content: 'School will be closed for winter break from December 20th, 2023 to January 3rd, 2024. Classes will resume on January 4th, 2024.',
            date: '2024-01-05',
            priority: 'medium'
        }
    ];
    
    // Show loading state
    noticesContainer.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
    
    // Simulate loading delay
    setTimeout(() => {
        displayNotices(notices);
    }, 800);
}

function displayNotices(notices) {
    const noticesContainer = document.getElementById('notices-container');
    noticesContainer.innerHTML = '';
    
    if (notices.length === 0) {
        noticesContainer.innerHTML = '<p class="text-center text-muted">No notices available at the moment.</p>';
        return;
    }
    
    notices.forEach((notice, index) => {
        const noticeElement = createNoticeElement(notice);
        noticesContainer.appendChild(noticeElement);
        
        // Add fade-in animation
        setTimeout(() => {
            noticeElement.classList.add('fade-in-up');
        }, index * 150);
    });
}

function createNoticeElement(notice) {
    const noticeDiv = document.createElement('div');
    noticeDiv.className = `notice-item notice-priority-${notice.priority}`;
    
    const formattedDate = formatDate(notice.date);
    
    noticeDiv.innerHTML = `
        <div class="notice-date">
            <i class="fas fa-calendar-alt"></i> ${formattedDate}
        </div>
        <div class="notice-title">${notice.title}</div>
        <div class="notice-content">${notice.content}</div>
    `;
    
    return noticeDiv;
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', options);
}

// Smooth scrolling for navigation links
function initSmoothScrolling() {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 76; // Account for fixed navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Animation on scroll
function initAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, observerOptions);
    
    // Observe sections for animation
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        observer.observe(section);
    });
}

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Gallery image modal (optional enhancement)
function openImageModal(imageSrc, title) {
    // Create modal HTML
    const modalHTML = `
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="${imageSrc}" alt="${title}" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('imageModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
    
    // Remove modal from DOM when hidden
    document.getElementById('imageModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

// Search functionality for notices
function searchNotices(query) {
    const notices = document.querySelectorAll('.notice-item');
    const searchTerm = query.toLowerCase();
    
    notices.forEach(notice => {
        const title = notice.querySelector('.notice-title').textContent.toLowerCase();
        const content = notice.querySelector('.notice-content').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || content.includes(searchTerm)) {
            notice.style.display = 'block';
        } else {
            notice.style.display = 'none';
        }
    });
}

// Utility functions
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
    }
}

function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const loading = element.querySelector('.loading');
        if (loading) {
            loading.remove();
        }
    }
}

// API functions for admin panel integration
async function fetchGalleryFromAPI() {
    try {
        const response = await fetch('api/gallery.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching gallery:', error);
        return [];
    }
}

async function fetchNoticesFromAPI() {
    try {
        const response = await fetch('api/notices.php');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching notices:', error);
        return [];
    }
}

// Export functions for use in other files
window.SchoolWebsite = {
    loadGalleryImages,
    displayNotices,
    searchNotices,
    openImageModal,
    showLoading,
    hideLoading
};