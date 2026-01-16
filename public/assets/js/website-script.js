// Initialize Lucide icons
lucide.createIcons();

// Mobile menu functionality
const mobileMenuButton = document.querySelector('.mobile-menu-button');
const navLinks = document.querySelector('.nav-links');

if (mobileMenuButton && navLinks) {
  mobileMenuButton.addEventListener('click', () => {
    navLinks.classList.toggle('active');
  });
}

// Close mobile menu when clicking outside
document.addEventListener('click', (e) => {
  if (navLinks && navLinks.classList.contains('active') && 
      !navLinks.contains(e.target) && 
      !mobileMenuButton.contains(e.target)) {
    navLinks.classList.remove('active');
  }
});

// Sticky header functionality
window.addEventListener('scroll', () => {
  const navbar = document.querySelector('.navbar');
  if (window.scrollY > 0) {
    navbar.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1)';
  } else {
    navbar.style.boxShadow = 'none';
  }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

// Feature card hover effect
document.querySelectorAll('.feature-card').forEach(card => {
  card.addEventListener('mouseenter', () => {
    card.style.transform = 'translateY(-4px)';
    card.style.transition = 'transform 0.2s ease-in-out';
  });

  card.addEventListener('mouseleave', () => {
    card.style.transform = 'translateY(0)';
  });
});

// Stats animation
const stats = document.querySelectorAll('.stat-number');
const animateStats = () => {
  stats.forEach(stat => {
    const target = parseInt(stat.textContent);
    let current = 0;
    const increment = target / 50;
    const duration = 1500;
    const step = duration / 50;

    const updateStat = () => {
      current += increment;
      if (current < target) {
        if (stat.textContent.includes('%')) {
          stat.textContent = Math.floor(current) + '%';
        } else if (stat.textContent.includes('M+')) {
          stat.textContent = (Math.floor(current * 10) / 10).toFixed(1) + 'M+';
        } else {
          stat.textContent = Math.floor(current);
        }
        setTimeout(updateStat, step);
      } else {
        stat.textContent = stat.dataset.original;
      }
    };

    stat.dataset.original = stat.textContent;
    updateStat();
  });
};

// Intersection Observer for stats animation
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      animateStats();
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.5 });

const statsSection = document.querySelector('.stats-grid');
if (statsSection) {
  observer.observe(statsSection);
}