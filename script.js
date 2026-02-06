// ===== SMOOTH SCROLL FOR NAV LINKS =====
const navLinks = document.querySelectorAll('.nav-links li a');

navLinks.forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    const targetId = link.getAttribute('href').substring(1);
    const targetSection = document.getElementById(targetId);
    if (targetSection) {
      window.scrollTo({
        top: targetSection.offsetTop - 70, // adjust for navbar height
        behavior: 'smooth'
      });
    }
  });
});

// ===== SCROLL ANIMATIONS =====
const sections = document.querySelectorAll('.about-container, .services, .contact');

function handleScrollAnimations() {
  const scrollY = window.pageYOffset;
  const windowHeight = window.innerHeight;

  sections.forEach(section => {
    const sectionTop = section.getBoundingClientRect().top + scrollY;
    if (scrollY + windowHeight * 0.8 > sectionTop) {
      section.style.opacity = 1;
      section.style.transform = 'translateY(0)';
    }
  });
}

window.addEventListener('scroll', handleScrollAnimations);

// ===== NAVBAR SHADOW ON SCROLL =====
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
  if (window.scrollY > 50) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
});

// ===== OPTIONAL PARALLAX EFFECT FOR HOME IMAGE =====
const homeImage = document.querySelector('.home-image');

window.addEventListener('scroll', () => {
  if (homeImage) {
    const scroll = window.scrollY;
    homeImage.style.transform = `translateY(calc(-50% + ${scroll * 0.2}px))`;
  }
});
