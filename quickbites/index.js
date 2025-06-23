let currentSlide = 0;
  const slides = document.querySelectorAll('.slide');
  const dotsContainer = document.getElementById('dots-container');

  // Create dots dynamically
  slides.forEach((_, idx) => {
    const dot = document.createElement('span');
    dot.classList.add('dot');
    if (idx === 0) dot.classList.add('active');
    dot.setAttribute('onclick', `goToSlide(${idx})`);
    dotsContainer.appendChild(dot);
  });

  const dots = document.querySelectorAll('.dot');

  function showSlide(index) {
    slides.forEach((slide, i) => {
      slide.classList.remove('active');
      dots[i].classList.remove('active');
      if (i === index) {
        slide.classList.add('active');
        dots[i].classList.add('active');
      }
    });
    currentSlide = index;
  }

  function moveSlide(step) {
    let newIndex = (currentSlide + step + slides.length) % slides.length;
    showSlide(newIndex);
  }

  function goToSlide(index) {
    showSlide(index);
  }

  // Auto Slide
  setInterval(() => {
    moveSlide(1);
  }, 5000);


  function addToCart(name, price, image) {
    let cart = JSON.parse(localStorage.getItem("quickbite-cart")) || [];
    let existing = cart.find(item => item.name === name);
    if (existing) {
      existing.qty += 1;
    } else {
      cart.push({ name, price, image, qty: 1 });
    }
    localStorage.setItem("quickbite-cart", JSON.stringify(cart));
    alert(name + " added to cart!");
  }

  // Testimonial Carousel Script 
  let currentTestimonial = 0;
const testimonials = document.querySelectorAll('.testimonial-carousel .testimonial');

function showTestimonial(index) {
  testimonials.forEach((t, i) => {
    t.classList.remove('active');
    if (i === index) t.classList.add('active');
  });
}

function nextTestimonial() {
  currentTestimonial = (currentTestimonial + 1) % testimonials.length;
  showTestimonial(currentTestimonial);
}

setInterval(nextTestimonial, 5000);
