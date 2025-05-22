$(document).ready(function() {
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
        pauseOnHover: false,
        draggable: true,
        swipe: true,
        prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>',
        nextArrow: '<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>',
        responsive: [
            {
                breakpoint: 992,
                settings: {
                    dots: true,
                    arrows: true
                }
            },
            {
                breakpoint: 768,
                settings: {
                    dots: true,
                    arrows: true
                }
            },
            {
                breakpoint: 576,
                settings: {
                    dots: true,
                    arrows: false
                }
            }
        ]
    };

    // Initialize slider
    const $slider = $('#userSlider');
    $slider.slick(sliderConfig);

    // Handle slider click events
    $('.slider-item').on('click', function(e) {
        if (!$(e.target).closest('.hero-btn, .slick-arrow, .slick-dots').length) {
            const link = $(this).data('link');
            if (link) {
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

    // Dropdown hover effect
    $('.dropdown').hover(
        function() {
            $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeIn(300);
        },
        function() {
            $(this).find('.dropdown-menu').stop(true, true).delay(100).fadeOut(300);
        }
    );
});