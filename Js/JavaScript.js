let currentIndex = 0;
	const slides = document.querySelectorAll(".slide");
	const slidesContainer = document.getElementById("slides");
	const totalSlides = slides.length;
	let autoSlideInterval;
	
	
	// =====hero section======
	// ========Function to move the slides========
	function moveSlide(direction) {
	  currentIndex += direction;

	  // =====Loop around if we go out of bounds=======
	  if (currentIndex < 0) {
		currentIndex = totalSlides - 1;
	  }
	  if (currentIndex >= totalSlides) {
		currentIndex = 0;
	  }

	  updateSlidePosition();
	}

	// =======Function to update transform position=======
	function updateSlidePosition() {
	  slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
	}

	// ========Auto slide every 5 seconds=======
	function startAutoSlide() {
	  autoSlideInterval = setInterval(() => {
		moveSlide(1);
	  }, 5000);
	}

	// ======Stop auto slide when hovering over the hero section========
	function stopAutoSlide() {
	  clearInterval(autoSlideInterval);
	}

	// ========Start slider when page loads=====
	document.addEventListener("DOMContentLoaded", () => {
	  startAutoSlide();

	  // ======Pause when mouse enters====
	  document.querySelector(".hero").addEventListener("mouseenter", stopAutoSlide);

	  // ======Resume when mouse leaves=====
	  document.querySelector(".hero").addEventListener("mouseleave", startAutoSlide);
	});
	
	
	// ===========mid container section===========
	document.addEventListener("DOMContentLoaded", () => {
  let galleryIndex = 0;
  const galleryTrack = document.getElementById("gallery-track");
  const galleryItems = document.querySelectorAll(".gallery-item");

  function getItemWidth() {
    return galleryItems[0].offsetWidth + 10; // width + margin
  }

  function getVisibleItems() {
    return Math.floor(document.querySelector(".mid-gallery").offsetWidth / getItemWidth());
  }

  function moveGallery(direction) {
    const maxIndex = galleryItems.length - getVisibleItems();
    galleryIndex += direction;

    if (galleryIndex < 0) galleryIndex = 0;
    if (galleryIndex > maxIndex) galleryIndex = maxIndex;

    galleryTrack.style.transform = `translateX(-${galleryIndex * getItemWidth()}px)`;
  }

  // =====Button clicks========
  document.querySelector(".gallery-arrow.prev").addEventListener("click", () => moveGallery(-1));
  document.querySelector(".gallery-arrow.next").addEventListener("click", () => moveGallery(1));

  // ===========Auto-slide========
  setInterval(() => {
    const maxIndex = galleryItems.length - getVisibleItems();
    if (galleryIndex < maxIndex) {
      galleryIndex++;
    } else {
      galleryIndex = 0;
    }
    galleryTrack.style.transform = `translateX(-${galleryIndex * getItemWidth()}px)`;
  }, 3000);
});

	/// ========Login/signup modal is now handled by components/auth_modal.js========


  // ================= TENTS & TARPAULINS =================
document.querySelector('a[href="/tents-tarpaulins"]').addEventListener('click', function(e) {
  e.preventDefault();
  document.getElementById('tentMenu').style.display = 'flex';
});

function showSubMenu(category) {
  document.querySelectorAll('#tentMenu .mega-right').forEach(el => el.classList.add('hidden'));
  document.getElementById(category + '-sub').classList.remove('hidden');
}


// ================= COOKING EQUIPMENT =================
document.querySelector('a[href="/Cooking Equipment"]').addEventListener('click', function(e) {
  e.preventDefault();
  document.getElementById('cookingMenu').style.display = 'flex';
});

function showCookingSub(category) {
  document.querySelectorAll('#cookingMenu .mega-right').forEach(el => el.classList.add('hidden'));
  document.getElementById(category + '-sub').classList.remove('hidden');
}


// ================= SURVIVAL EQUIPMENT =================
document.querySelector('a[href="/Survival Equipment"]').addEventListener('click', function(e) {
  e.preventDefault();
  document.getElementById('survivalMenu').style.display = 'flex';
});

function showSurvivalSub(category) {
  document.querySelectorAll('#survivalMenu .mega-right').forEach(el => el.classList.add('hidden'));
  document.getElementById(category + '-sub').classList.remove('hidden');
}


// ================= UNIVERSAL CLOSE FUNCTION =================
function closeMegaMenu(menuId) {
  document.getElementById(menuId).style.display = 'none';
}

// ================= OPTIONAL: Close on Background Click =================
document.querySelectorAll('.mega-menu').forEach(menu => {
  menu.addEventListener('click', function(e) {
    if (e.target === this) { // only background, not inner content
      this.style.display = 'none';
    }
  });
});

// TESTTTT
 // Load homepage banner from localStorage
  window.onload = function() {
    const savedBanner = localStorage.getItem("homepageBanner");
    if (savedBanner) {
      document.getElementById("mainBanner").style.backgroundImage = `url(${savedBanner})`;
    }
  };

  window.onload = function() {
    const savedSlideshow = localStorage.getItem("homepageSlideshow");
    if (savedSlideshow) {
      const images = JSON.parse(savedSlideshow);
      const slidesContainer = document.getElementById("slides");
      slidesContainer.innerHTML = "";

      images.forEach((src, index) => {
        const slide = document.createElement("div");
        slide.classList.add("slide");
        slide.style.backgroundImage = `url(${src})`;
        if (index === 0) slide.classList.add("active"); // first slide active
        slidesContainer.appendChild(slide);
      });
    }
  };
// ================= PROMO CARDS =================
let promoIndex = 0;
const promoCards = document.querySelectorAll('.promo-card');
function showPromo(idx) {
  promoCards.forEach((card, i) => {
    card.style.display = (i === idx) ? 'block' : 'none';
  });
}
function movePromo(dir) {
  promoIndex += dir;
  if (promoIndex < 0) promoIndex = promoCards.length - 1;
  if (promoIndex >= promoCards.length) promoIndex = 0;
  showPromo(promoIndex);
}
showPromo(promoIndex);

// ================= USER DROPDOWN MENU =================
document.addEventListener('DOMContentLoaded', function() {
  const userDropdown = document.querySelector('.user-dropdown');
  const userMenuBtn = document.querySelector('.user-menu-btn');
  
  if (userDropdown && userMenuBtn) {
    // Toggle dropdown on button click
    userMenuBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      userDropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userDropdown.contains(e.target)) {
        userDropdown.classList.remove('active');
      }
    });
    
    // Close dropdown when pressing Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        userDropdown.classList.remove('active');
      }
    });
    
    // Close dropdown when clicking a menu item
    const menuItems = userDropdown.querySelectorAll('.user-dropdown-menu a');
    menuItems.forEach(item => {
      item.addEventListener('click', function() {
        userDropdown.classList.remove('active');
      });
    });
  }
});

// ================= USER DROPDOWN MENU =================
document.addEventListener('DOMContentLoaded', function() {
  const userDropdown = document.querySelector('.user-dropdown');
  const userMenuBtn = document.querySelector('.user-menu-btn');
  
  if (userDropdown && userMenuBtn) {
    // Toggle dropdown when button is clicked
    userMenuBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      userDropdown.classList.toggle('active');
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userDropdown.contains(e.target)) {
        userDropdown.classList.remove('active');
      }
    });
    
    // Close dropdown when pressing escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        userDropdown.classList.remove('active');
      }
    });
  }
});