// Smooth scroll for navigation links
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href.startsWith('#')) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Learn More button scrolls to About section
document.getElementById('learnMoreBtn').addEventListener('click', function() {
    document.getElementById('about').scrollIntoView({ behavior: 'smooth' });
});

// Simple admission form submission
document.getElementById('admissionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const grade = document.getElementById('grade').value.trim();

    if (!name || !email || !grade) {
        document.getElementById('formMessage').textContent = 'Please fill all fields.';
        document.getElementById('formMessage').style.color = 'red';
        return;
    }

    document.getElementById('formMessage').textContent = 
        `Thank you, ${name}! Your enquiry for grade ${grade} has been received.`;
    document.getElementById('formMessage').style.color = 'green';

    // Optionally reset form
    document.getElementById('admissionForm').reset();
});
