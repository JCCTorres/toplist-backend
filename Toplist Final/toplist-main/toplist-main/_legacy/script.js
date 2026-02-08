// Carousel functionality for the old fade-style carousel
let slideIndex = 0;
function showFadeSlides() {
    let slides = document.getElementsByClassName('carousel-slide fade');
    
    // Hide all slides
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = 'none';
    }
    
    // Increment slideIndex
    slideIndex++;
    
    // Reset to first slide if at the end
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }
    
    // Show the current slide
    if (slides.length > 0 && slides[slideIndex - 1]) {
        slides[slideIndex - 1].style.display = 'block';
    
        // Change image every 3 seconds
        setTimeout(showFadeSlides, 3000);
    }
}

// New carousel for homepage with sliding effect
function setupSlideCarousel() {
    const slideContainer = document.querySelector('.carousel-slide.flex');
    if (!slideContainer) return;
    
    const images = slideContainer.querySelectorAll('img');
    const imageCount = images.length;
    let currentIndex = 0;
    
    // Set initial position
    slideContainer.style.width = `${imageCount * 100}%`;
    images.forEach(img => {
        img.style.width = `${100 / imageCount}%`;
    });
    
    // Function to move to next slide
    function moveToNextSlide() {
        currentIndex = (currentIndex + 1) % imageCount;
        slideContainer.style.transform = `translateX(-${currentIndex * (100 / imageCount)}%)`;
        setTimeout(moveToNextSlide, 5000);
    }
    
    // Start the carousel
    setTimeout(moveToNextSlide, 5000);
}

// Initialize resort dropdown functionality
function setupResortDropdown() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(dropdownToggle => {
        const resortDropdown = dropdownToggle.closest('.resort-selector').querySelector('.resort-dropdown');
        const selectedResortText = dropdownToggle.querySelector('span');
        const resortOptions = resortDropdown.querySelectorAll('div');
        
        if (!dropdownToggle || !resortDropdown) return;
        
        // Toggle resort dropdown
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resortDropdown.classList.toggle('hidden');
            resortDropdown.classList.toggle('active');
        });
        
        // Handle resort selection
        resortOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const value = this.getAttribute('data-value');
                const text = this.textContent;
                
                selectedResortText.textContent = text;
                resortDropdown.classList.add('hidden');
                resortDropdown.classList.remove('active');
                
                // Add selected value as a data attribute to the parent for form submission
                dropdownToggle.parentElement.setAttribute('data-selected', value);
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        dropdownToggles.forEach(dropdownToggle => {
            const resortDropdown = dropdownToggle.closest('.resort-selector').querySelector('.resort-dropdown');
            if (resortDropdown && !dropdownToggle.contains(event.target) && !resortDropdown.contains(event.target)) {
                resortDropdown.classList.add('hidden');
                resortDropdown.classList.remove('active');
            }
        });
    });
}

// Initialize date picker
function setupDatePicker() {
    // First ensure flatpickr is loaded
    if (typeof flatpickr !== 'function') {
        console.error('Flatpickr not found. Make sure to include the flatpickr script in your HTML.');
        return;
    }
    
    // Find all date range toggles on the page 
    const dateRangeToggles = document.querySelectorAll('#date-range-toggle');
    if (dateRangeToggles.length === 0) {
        console.warn('No date-range-toggle elements found.');
        return;
    }
    
    dateRangeToggles.forEach(dateRangeToggle => {
        const dateRangePickerContainer = dateRangeToggle.closest('.travel-dates');
        if (!dateRangePickerContainer) {
            console.warn('Could not find travel-dates container for a date toggle.');
            return;
        }
        
        const dateRangePickerInput = dateRangePickerContainer.querySelector('#date-range-picker');
        if (!dateRangePickerInput) {
            console.warn('Could not find date-range-picker input for a travel-dates container.');
            return;
        }
        
        // Ensure the container can show overflow
        dateRangePickerContainer.style.overflow = 'visible';

        // Check if we're on mobile
        const isMobile = window.innerWidth < 768;

        // Initialize flatpickr date range picker
        const datePicker = flatpickr(dateRangePickerInput, {
            mode: 'range',
            minDate: 'today',
            dateFormat: 'Y-m-d',
            position: 'auto',
            allowInput: false,
            static: true,
            disableMobile: false, // Allow native mobile experience when needed
            appendTo: isMobile ? document.body : undefined, // Append to body on mobile for better positioning
            onClose: function(selectedDates, dateStr) {
                if (selectedDates.length > 0) {
                    const startDate = new Date(selectedDates[0]);
                    const endDate = selectedDates[1] ? new Date(selectedDates[1]) : startDate;
                    
                    const formattedStartDate = startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    const formattedEndDate = endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    
                    dateRangeToggle.querySelector('span').textContent = `${formattedStartDate} - ${formattedEndDate}`;
                } else {
                    // Reset text if dates are cleared
                    dateRangeToggle.querySelector('span').textContent = 'Travel Dates';
                }
            },
            onOpen: function(selectedDates, dateStr, instance) {
                // Ensure the calendar has high z-index on open
                const calendarElem = instance.calendarContainer;
                if (calendarElem) {
                    calendarElem.style.zIndex = "9999";
                    // On mobile, append to body and center
                    if (isMobile) {
                        document.body.appendChild(calendarElem);
                        calendarElem.style.position = "fixed";
                        calendarElem.style.top = "50%";
                        calendarElem.style.left = "50%";
                        calendarElem.style.transform = "translate(-50%, -50%)";
                        calendarElem.style.maxWidth = "90vw";
                    }
                }
            },
            onError: function(error) {
                console.error('Flatpickr error:', error);
            }
        });
        
        // Open the date picker when the toggle area is clicked
        dateRangeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Date range toggle clicked - opening picker');
            datePicker.open();
        });
        
        // Prevent the hidden input from interfering with clicks if it overlaps
        dateRangePickerInput.addEventListener('click', (e) => e.stopPropagation());

    });
}

// Implement smooth scrolling for anchor links
function setupSmoothScrolling() {
    // Select all navigation links in the header
    const navLinks = document.querySelectorAll('header nav a');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Only apply smooth scrolling for hash links (links to sections within the page)
            if (href && href.startsWith('#')) { // Check if href exists and starts with #
                e.preventDefault();
                
                // Get the target ID from the href attribute
                const targetId = href.substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    // Get the height of the fixed header
                    const header = document.querySelector('header');
                    const headerHeight = header ? header.offsetHeight : 0;
                    
                    // Calculate the position to scroll to (accounting for fixed header)
                    const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                    
                    // Smooth scroll to the target
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            } 
            // For normal page links (like guest.html or links on other pages), let the browser handle navigation naturally
        });
    });
}

// Function to handle service card interactions
function setupServiceCards() {
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        // Add enhanced hover effects that are handled by CSS now
        card.addEventListener('mouseenter', function() {
            // The animations are now handled by CSS
            console.log('Card hover effect active');
        });
        
        // Add click event to navigate to detail pages if needed
        card.addEventListener('click', function() {
            // For future implementation: Navigate to service detail pages
            // Currently just logging that the card was clicked
            console.log('Service card clicked');
        });
    });
}

// Function to setup complimentary extras carousel
function setupExtrasCarousel() {
    const carousel = document.querySelector('.container .relative.flex.justify-center');
    if (!carousel) return;
    
    const itemsContainer = carousel.querySelector('.flex.justify-center.gap-8');
    const items = carousel.querySelectorAll('.flex-none');
    const prevButton = carousel.querySelector('button:first-of-type');
    const nextButton = carousel.querySelector('button:last-of-type');
    
    if (!itemsContainer || !items.length || !prevButton || !nextButton) return;
    
    let currentPosition = 0;
    const itemWidth = 280; // Width + gap
    const visibleItems = Math.floor(itemsContainer.offsetWidth / itemWidth);
    const maxPosition = Math.max(0, items.length - visibleItems);
    
    // Update navigation button states
    function updateNavButtons() {
        prevButton.disabled = currentPosition <= 0;
        prevButton.style.opacity = prevButton.disabled ? '0.5' : '1';
        
        nextButton.disabled = currentPosition >= maxPosition;
        nextButton.style.opacity = nextButton.disabled ? '0.5' : '1';
    }
    
    // Slide the carousel to the current position
    function slideCarousel() {
        const offset = currentPosition * -itemWidth;
        itemsContainer.style.transform = `translateX(${offset}px)`;
        updateNavButtons();
    }
    
    // Add event listeners to navigation buttons
    prevButton.addEventListener('click', () => {
        if (currentPosition > 0) {
            currentPosition--;
            slideCarousel();
        }
    });
    
    nextButton.addEventListener('click', () => {
        if (currentPosition < maxPosition) {
            currentPosition++;
            slideCarousel();
        }
    });
    
    // Initialize the carousel
    itemsContainer.style.transition = 'transform 0.5s ease';
    updateNavButtons();
    
    // Handle window resize events
    window.addEventListener('resize', () => {
        const newVisibleItems = Math.floor(itemsContainer.offsetWidth / itemWidth);
        const newMaxPosition = Math.max(0, items.length - newVisibleItems);
        
        // Adjust current position if needed
        if (currentPosition > newMaxPosition) {
            currentPosition = newMaxPosition;
            slideCarousel();
        }
        
        // Update max position
        maxPosition = newMaxPosition;
        updateNavButtons();
    });
}

// Ensure window scrolls properly
window.addEventListener('load', function() {
    // Delay to ensure DOM is fully loaded
    setTimeout(() => {
        // Force a small scroll to activate the scrolling mechanism
        window.scrollBy(0, 1);
        window.scrollBy(0, -1);
        
        console.log('Window loaded, scroll reset');
    }, 500);
});

// Handle responsive layout
function handleResponsiveLayout() {
    const isMobile = window.innerWidth < 768;
    const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
    
    // Get relevant elements
    const mainSearchBar = document.getElementById('main-search-bar');
    const navContainer = document.querySelector('header .container');
    const headerButtons = document.querySelector('header .flex.space-x-2');
    
    // Apply responsive classes based on screen size
    if (isMobile) {
        // Ensure search bar is stacked vertically on mobile
        if (mainSearchBar) {
            mainSearchBar.style.flexDirection = 'column';
            mainSearchBar.style.borderRadius = '1rem';
            
            // Make each section full width
            const sections = mainSearchBar.querySelectorAll('.search-bar-section');
            sections.forEach(section => {
                section.style.width = '100%';
            });
        }
        
        // Make the header more compact
        if (navContainer) {
            navContainer.style.flexDirection = 'column';
            navContainer.style.padding = '0.5rem';
        }
        
        // Ensure buttons are visible
        if (headerButtons) {
            headerButtons.style.width = '100%';
            headerButtons.style.justifyContent = 'center';
            headerButtons.style.marginTop = '0.5rem';
        }
    } else {
        // Reset styles for larger screens
        if (mainSearchBar) {
            mainSearchBar.style.flexDirection = 'row';
            
            const sections = mainSearchBar.querySelectorAll('.search-bar-section');
            sections.forEach(section => {
                section.style.width = '';
            });
        }
        
        if (navContainer) {
            navContainer.style.flexDirection = isTablet ? 'column' : 'row';
            navContainer.style.padding = '';
        }
        
        if (headerButtons) {
            headerButtons.style.width = isTablet ? '100%' : '';
            headerButtons.style.justifyContent = isTablet ? 'center' : '';
            headerButtons.style.marginTop = isTablet ? '0.5rem' : '';
        }
    }
}

// Main initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded - initializing components');
    
    // Add WhatsApp button to all pages except homepage
    const whatsappBtn = document.querySelector('.fixed.bottom-6.right-6');
    if (whatsappBtn && window.location.pathname !== '/' && window.location.pathname !== '/index.html') {
        whatsappBtn.classList.remove('hidden');
    }
    
    // Initialize the appropriate carousel based on which elements exist
    if (document.querySelector('.carousel-slide.fade')) {
        showFadeSlides();
    }
    
    if (document.querySelector('.carousel-slide.flex')) {
        setupSlideCarousel();
    }

    // Initialize search bar components
    setupResortDropdown();
    
    // Ensure flatpickr is loaded before initializing the date picker
    if (typeof flatpickr === 'function') {
        console.log('Flatpickr loaded, initializing date picker');
        setupDatePicker();
    } else {
        console.error('Flatpickr not loaded yet, attempting to load dynamically or wait...');
        // If flatpickr might load later, add a check
        let attempts = 0;
        const checkFlatpickr = setInterval(() => {
            attempts++;
            if (typeof flatpickr === 'function') {
                console.log('Flatpickr loaded after delay, initializing date picker');
                setupDatePicker();
                clearInterval(checkFlatpickr);
            } else if (attempts > 10) { // Stop checking after 5 seconds
                console.error('Flatpickr failed to load after multiple attempts.');
                clearInterval(checkFlatpickr);
            }
        }, 500);
    }
    
    // Add hero-text class to the hero heading
    const heroHeading = document.querySelector('.text-black.text-4xl.font-normal');
    if (heroHeading) {
        heroHeading.classList.add('hero-text');
    }

    // Handle fixed header transparency when scrolling
    const header = document.querySelector('header');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Set the home section to take the full viewport height
    const adjustHomeHeight = () => {
        const homeSection = document.getElementById('home');
        if (homeSection) {
            const headerHeight = document.querySelector('header').offsetHeight;
            homeSection.style.height = `calc(100vh - ${headerHeight}px)`;
            // Ensure the video fills the entire home section
            const videoBackground = document.querySelector('.video-background');
            if (videoBackground) {
                videoBackground.style.height = '100vh';
            }
        }
    };
    
    // Run on load and resize
    adjustHomeHeight();
    window.addEventListener('resize', adjustHomeHeight);

    // Initialize smooth scrolling
    setupSmoothScrolling(); // Re-enabled smooth scrolling

    // Setup service cards interaction
    setupServiceCards();
    
    // Setup complimentary extras carousel
    setupExtrasCarousel();

    // Handle responsive layout
    handleResponsiveLayout();
});

// Add resize event listener
window.addEventListener('resize', function() {
    handleResponsiveLayout();
});
