document.addEventListener('DOMContentLoaded', function() {
    // Add animation delay to cards
    document.querySelectorAll('.subcategory-card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Add hover effect for badges
    document.querySelectorAll('.property-badge').forEach(badge => {
        badge.addEventListener('mouseover', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        badge.addEventListener('mouseout', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});