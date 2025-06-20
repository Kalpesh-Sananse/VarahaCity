$(document).ready(function() {
    // Debug: Check if slider element exists
    console.log('Slider element found:', $('#userSlider').length);
    console.log('Slider slides count:', $('#userSlider .slider-item').length);
    
    // Wait for images to load before initializing slider
    const $slider = $('#userSlider');
    const $sliderItems = $slider.find('.slider-item');
    
    // Slider configuration
    const sliderConfig = {
        dots: true,
        infinite: true,
        speed: 1000,
        fade: true,
        cssEase: 'linear',
        autoplay: true,
        autoplaySpeed: 3000, // 5 seconds rotation
        arrows: true,
        pauseOnHover: true,
        draggable: true,
        swipe: true,
        touchMove: true,
        adaptiveHeight: false,
        variableWidth: false,
        centerMode: false,
        slidesToShow: 1,
        slidesToScroll: 1,
        lazyLoad: 'ondemand',
        prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>',
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    dots: true,
                    arrows: true,
                    autoplay: true,
                    autoplaySpeed: 5000
                }
            },
            {
                breakpoint: 768,
                settings: {
                    dots: true,
                    arrows: true,
                    autoplay: true,
                    autoplaySpeed: 5000
                }
            },
            {
                breakpoint: 576,
                settings: {
                    dots: true,
                    arrows: false,
                    autoplay: true,
                    autoplaySpeed: 5000
                }
            }
        ]
    };

    // Function to initialize slider
    function initializeSlider() {
        try {
            // Destroy existing slider if it exists
            if ($slider.hasClass('slick-initialized')) {
                $slider.slick('unslick');
            }
            
            // Initialize slider
            $slider.slick(sliderConfig);
            
            console.log('Slider initialized successfully');
            
            // Handle slider events
            $slider.on('afterChange', function(event, slick, currentSlide) {
                console.log('Slide changed to:', currentSlide);
            });
            
            $slider.on('init', function(event, slick) {
                console.log('Slider init event fired');
                // Trigger animations for first slide
                setTimeout(function() {
                    $('.hero-title, .hero-description, .hero-btn').addClass('animated');
                }, 100);
            });
            
        } catch (error) {
            console.error('Error initializing slider:', error);
        }
    }

    // Check if slider has content
    if ($sliderItems.length > 0) {
        // Initialize slider immediately if slides exist
        initializeSlider();
    } else {
        console.warn('No slider items found');
    }
    
    // Handle slider click events (avoid conflicts with slick controls)
    $(document).on('click', '.slider-item', function(e) {
        // Don't trigger if clicking on slick controls or hero button
        if (!$(e.target).closest('.hero-btn, .slick-arrow, .slick-dots').length) {
            const link = $(this).data('link');
            if (link && link !== '#' && link !== '') {
                window.location.href = link;
            }
        }
    });

    // Navbar scroll effect
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
        } else {
            $('.navbar').removeClass('scrolled');
        }
    });

  
// Dropdown functionality for both desktop and mobile
$(document).ready(function() {
    
    // Desktop hover effect (screens wider than 768px)
    if ($(window).width() > 768) {
        $('.dropdown').hover(
            function() {
                $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeIn(300);
            },
            function() {
                $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeOut(300);
            }
        );
    }
    
    // Mobile click/touch effect (screens 768px and below)
    if ($(window).width() <= 768) {
        $('.dropdown > a, .dropdown > .dropdown-toggle').on('click touchstart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $dropdown = $(this).parent('.dropdown');
            var $dropdownMenu = $dropdown.find('.dropdown-menu');
            
            // Close other open dropdowns
            $('.dropdown').not($dropdown).find('.dropdown-menu').fadeOut(300);
            
            // Toggle current dropdown
            if ($dropdownMenu.is(':visible')) {
                $dropdownMenu.fadeOut(300);
            } else {
                $dropdownMenu.fadeIn(300);
            }
        });
        
        // Close dropdown when clicking outside
        $(document).on('click touchstart', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.dropdown-menu').fadeOut(300);
            }
        });
        
        // Prevent dropdown from closing when clicking inside the menu
        $('.dropdown-menu').on('click touchstart', function(e) {
            e.stopPropagation();
        });
    }
    
    // Handle window resize to switch between desktop/mobile behavior
    $(window).resize(function() {
        // Remove all event handlers first
        $('.dropdown').off('mouseenter mouseleave');
        $('.dropdown > a, .dropdown > .dropdown-toggle').off('click touchstart');
        $(document).off('click touchstart');
        $('.dropdown-menu').off('click touchstart');
        
        // Reapply appropriate handlers based on screen size
        if ($(window).width() > 768) {
            // Desktop hover
            $('.dropdown').hover(
                function() {
                    $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeIn(300);
                },
                function() {
                    $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeOut(300);
                }
            );
        } else {
            // Mobile click/touch
            $('.dropdown > a, .dropdown > .dropdown-toggle').on('click touchstart', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $dropdown = $(this).parent('.dropdown');
                var $dropdownMenu = $dropdown.find('.dropdown-menu');
                
                $('.dropdown').not($dropdown).find('.dropdown-menu').fadeOut(300);
                
                if ($dropdownMenu.is(':visible')) {
                    $dropdownMenu.fadeOut(300);
                } else {
                    $dropdownMenu.fadeIn(300);
                }
            });
            
            $(document).on('click touchstart', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').fadeOut(300);
                }
            });
            
            $('.dropdown-menu').on('click touchstart', function(e) {
                e.stopPropagation();
            });
        }
    });
});

    // Handle window resize
    $(window).on('resize', function() {
        if ($slider.hasClass('slick-initialized')) {
            $slider.slick('refresh');
        }
    });

    // Category cards animation on scroll
    function animateOnScroll() {
        $('.category-card').each(function() {
            const elementTop = $(this).offset().top;
            const elementBottom = elementTop + $(this).outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();

            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                $(this).addClass('animate-in');
            }
        });
    }

    // Trigger animation on scroll
    $(window).on('scroll', animateOnScroll);
    
    // Trigger animation on load
    animateOnScroll();

    // Search functionality (if needed)
    $('.nav-icon .fa-search').parent().on('click', function(e) {
        e.preventDefault();
        // Add your search functionality here
        console.log('Search clicked');
    });

    // Cart functionality (if needed)
    $('.nav-icon .fa-shopping-cart').parent().on('click', function(e) {
        e.preventDefault();
        // Add your cart functionality here
        console.log('Cart clicked');
    });

    // User profile functionality (if needed)
    $('.nav-icon .fa-user').parent().on('click', function(e) {
        e.preventDefault();
        // Add your user profile functionality here
        console.log('User profile clicked');
    });

    // Shop Now button functionality
    $('.nav-btn').on('click', function(e) {
        e.preventDefault();
        // Add your shop now functionality here
        console.log('Shop Now clicked');
    });

    // Category card hover effects
    $('.category-card').hover(
        function() {
            $(this).find('.category-image').addClass('zoom-effect');
        },
        function() {
            $(this).find('.category-image').removeClass('zoom-effect');
        }
    );

    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        const target = $($(this).attr('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });

    // Error handling for images
    $('img').on('error', function() {
        console.log('Image failed to load:', $(this).attr('src'));
        // You can set a placeholder image here
        // $(this).attr('src', '../images/placeholder.jpg');
    });

    // Console log for debugging
    console.log('JavaScript initialized successfully');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Slick slider available:', typeof $.fn.slick !== 'undefined');
    
    // Force slider refresh after a short delay (for any timing issues)
    setTimeout(function() {
        if ($slider.hasClass('slick-initialized')) {
            $slider.slick('refresh');
            console.log('Slider refreshed after delay');
        }
    }, 1000);
});