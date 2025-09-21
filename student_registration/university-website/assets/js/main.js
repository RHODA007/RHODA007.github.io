// ====== Dark Mode Toggle ======
const toggleBtn = document.getElementById('toggleDark');

if(toggleBtn) {
    // Load dark mode from localStorage
    if(localStorage.getItem("dark-mode") === "enabled"){
        document.body.classList.add("dark-mode");
        toggleBtn.textContent = "☀️ Light Mode";
    }

    toggleBtn.addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");

        if(document.body.classList.contains("dark-mode")){
            localStorage.setItem("dark-mode", "enabled");
            toggleBtn.textContent = "☀️ Light Mode";
        } else {
            localStorage.setItem("dark-mode", "disabled");
            toggleBtn.textContent = "🌙 Dark Mode";
        }
    });
}

// ====== Background Slideshow ======
let slides = document.querySelectorAll('.bg-slide');
let currentSlide = 0;

if(slides.length > 0){
    setInterval(() => {
        slides[currentSlide].style.opacity = 0;
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].style.opacity = 1;
    }, 5000);
}

// ====== Carousel Words ======
const words = ["Innovate","Learn","Code","Create","Succeed"];
let wordIndex = 0;
const carouselWord = document.getElementById('carouselWord');

if(carouselWord){
    setInterval(() => { 
        wordIndex = (wordIndex + 1) % words.length; 
        carouselWord.textContent = words[wordIndex]; 
    }, 3000);
}

// ====== Sparkles Effect ======
const sparklesContainer = document.getElementById('sparkles');
if(sparklesContainer){
    for(let i = 0; i < 25; i++){
        const s = document.createElement('div');
        s.classList.add('sparkle');
        s.style.left = Math.random() * 100 + 'vw';
        s.style.top = Math.random() * 100 + 'vh';
        s.style.width = s.style.height = (Math.random() * 4 + 2) + 'px';
        s.style.animationDuration = (5 + Math.random() * 10) + 's';
        s.style.opacity = Math.random() * 0.7 + 0.3;
        sparklesContainer.appendChild(s);
    }
}

// ====== Newsletter Form ======
const newsletterForm = document.getElementById('newsletter-form');
if(newsletterForm){
    newsletterForm.addEventListener('submit', function(e){
        e.preventDefault();
        const msg = document.getElementById('newsletter-msg');
        if(msg){
            msg.textContent = "Thanks for subscribing!";
            msg.style.color = "#ff6b81";
        }
        this.reset();
    });
}
const NUM_SPARKLES = 40;
const container = document.getElementById('sparkles-container');

for (let i = 0; i < NUM_SPARKLES; i++) {
    const sparkle = document.createElement('div');
    sparkle.classList.add('sparkle');

    sparkle.style.top = Math.random() * 100 + 'vh';
    sparkle.style.left = Math.random() * 100 + 'vw';

    const size = 3 + Math.random() * 6;
    sparkle.style.width = size + 'px';
    sparkle.style.height = size + 'px';

    sparkle.style.animationDuration = (5 + Math.random() * 10) + 's';

    container.appendChild(sparkle);
}
