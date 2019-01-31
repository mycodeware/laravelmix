$(".home-carousel-swipe").owlCarousel({
    nav: true,
    dots: true,
    items: 1,
    loop: true
    // autoplay: true
});

$(".home-carousel-swipe .owl-prev").html('<i class="ico ico-prev"></i>');
$(".home-carousel-swipe .owl-next").html('<i class="ico ico-next"></i>');

var owl = $(".multi-item-carousel");
$(".multi-item-carousel").owlCarousel({
    stagePadding: 40,
    margin: 30,
    nav: true,
    dots: false,
    items: 8,
    responsive: {
        0: {
            items: 2
        },
        600: {
            items: 3
        },
        1000: {
            items: 7
        },
        1500: {
            items: 8
        }
    }
});
$(".multi-item-carousel .owl-prev").html('<i class="ico ico-prev"></i>');
$(".multi-item-carousel .owl-next").html('<i class="ico ico-next"></i>');
// Custom Navigation Events
$(".arrow").click(function() {
    owl.trigger("owl.next");
});

export function hideShow(classHide, classShow) {
    $("." + classHide).hide(0);
    $("." + classShow).fadeIn(300);
}

$("#btn_change_email").click(function() {
    hideShow("reset-step-2", "reset-step-1");
});

$("#btn_profile_change_email").click(function() {
    hideShow("profile-step-2", "profile-step-1");
});
