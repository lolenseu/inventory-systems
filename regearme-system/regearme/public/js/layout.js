window.addEventListener("load", () => {
    const loader = document.getElementById("main-loader");
    const content = document.getElementById("main-content");

    loader.style.opacity = "0";
    loader.style.transition = "opacity 0.4s ease";

    setTimeout(() => {
    loader.style.display = "none";
    content.style.display = "block";
    content.style.opacity = "0";
    content.style.transition = "opacity 0.4s ease";
    setTimeout(() => content.style.opacity = "1", 50);
    }, 200);
});

    // Smooth page transitions to prevent video reset
    document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('http')) {
        e.preventDefault();
        
        const content = document.getElementById('main-content');
        content.style.opacity = '0';
        
        setTimeout(() => {
            window.location.href = href;
        }, 200);
        }
    });
    });
});