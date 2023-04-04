/* global jQuery, google */

var $ = $ || jQuery,
    ET = ET || {};

gsap.registerPlugin(ScrollTrigger);

ET.mq = {
    xs: "(min-width: 375px)",
    sm: "(min-width: 576px)",
    md: "(min-width: 768px)",
    lg: "(min-width: 992px)",
    xl: "(min-width: 1200px)",
    xxl: "(min-width: 1530px)",
};

ET.initHeaderMenu = function () {
    var html = $("html"),
        toggle = $(".menu-toggle"),
        menuWrap = document.querySelector(".menu-wrap"),
        submenu_items = $("header .menu > li.menu-item-has-children"),
        header_links = $("header .menu a[href]");

    // Hide menu when clicking outside
    $("body").on("click", function (e) {
        var parent = $(e.target).closest(".menu-wrap");
        if (!$(e.target).is(".menu-toggle") && !$(e.target).closest(".menu-toggle").length && !parent.length) {
            html.removeClass("menu-active");
        }
    });

    // Hide dropdowns when scrolling
    var lastScrollY = window.scrollY;
    $(window).on(
        "scroll",
        throttle(function () {
            if (window.scrollY > lastScrollY) {
                submenu_items.removeClass("hover");
            }
            lastScrollY = window.scrollY;
        }, 100)
    );

    toggle.on("click", function () {
        $("header").css({ transform: "none" });

        html.toggleClass("menu-active");
        if (html.hasClass("menu-active")) {
            bodyScrollLock.disableBodyScroll(menuWrap);

            // Hide menu and re-enable scrolling when the page is resized
            $(window).one("resize", function () {
                html.removeClass("menu-active");
                bodyScrollLock.clearAllBodyScrollLocks();
            });

            gsap.fromTo(
                "header .menu-header > *, header .menu > li, header .utility > *",
                {
                    opacity: 0,
                    x: 100,
                },
                {
                    opacity: 1,
                    x: 0,
                    duration: 0.5,
                    stagger: 0.025,
                    ease: "power2.out",
                    clearProps: true,
                }
            );
        } else {
            bodyScrollLock.enableBodyScroll(menuWrap);
        }
    });

    gsap.set("header .sub-menu", { height: 0 });
    $(".sub-menu-toggle").on("click", function () {
        var submenu = $(this).closest("li").find(".sub-menu");

        $(this).closest("li").toggleClass("active");

        gsap.to(submenu, {
            height: $(this).closest("li").hasClass("active") ? "auto" : 0,
            duration: 0.5,
            ease: "power2.out",
        });
    });

    submenu_items.on("mouseenter mousemove", function () {
        $(this).addClass("hover");
    });

    submenu_items.on("mouseleave", function () {
        $(this).removeClass("hover");
    });

    header_links.on("click", function (e) {
        $(this).trigger("blur");
        submenu_items.removeClass("hover");
    });
};

ET.initHeaderScrollBehavior = function () {
    var lastScrollPosition = 0,
        header = $("header"),
        headerHeight = header[0].getBoundingClientRect().height,
        mainNav = header.find(".main-nav"),
        mainNavPosition = mainNav.offset().top,
        mainNavHeight = mainNav[0].getBoundingClientRect().height,
        headerPosition = 0,
        minHeaderPosition = -headerHeight + mainNavHeight,
        isDesktop = window.matchMedia(ET.mq.lg).matches;

    var root = document.documentElement;
    root.style.setProperty("--headerHeight", headerHeight + "px");
    root.style.setProperty("--mainNavHeight", mainNavHeight + "px");

    $(window).on(
        "resize",
        debounce(function () {
            isDesktop = window.matchMedia(ET.mq.lg).matches;
            headerHeight = header[0].getBoundingClientRect().height;
            root.style.setProperty("--headerHeight", headerHeight + "px");
            mainNavPosition = mainNav.offset().top;
            mainNavHeight = mainNav[0].getBoundingClientRect().height;
            root.style.setProperty("--mainNavHeight", mainNavHeight + "px");
            minHeaderPosition = -headerHeight + mainNavHeight;
        }, 100)
    );

    $(window).on(
        "scroll",
        throttle(function () {
            var scrollY = Math.max(0, window.scrollY),
                scrollDelta = scrollY - lastScrollPosition;

            if (scrollDelta < 0) {
                // If scrolling up, animate the header to 0
                if (isDesktop || scrollY < mainNavPosition) {
                    headerPosition = 0;
                    gsap.to(header, {
                        y: headerPosition,
                        duration: 0.35,
                        ease: "power2.out",
                        overwrite: true,
                    });
                }
            } else {
                // If scrolling down, animate the header up by the scrollDelta
                headerPosition = Math.min(Math.max(headerPosition - scrollDelta, minHeaderPosition), 0);
                gsap.to(header, {
                    y: headerPosition,
                    duration: 0.05,
                    ease: "none",
                    overwrite: true,
                });
            }

            // If the header has scrolled up to it's minHeaderPosition, minify it
            if (headerPosition === minHeaderPosition && !header.hasClass("minify")) {
                header.addClass("minify");
                window.setTimeout(function () {
                    mainNavHeight = mainNav[0].getBoundingClientRect().height;
                    root.style.setProperty("--mainNavHeight", mainNavHeight + "px");
                    ScrollTrigger.refresh();
                }, 250);
            }

            // If the header has scrolled up to the top of the page, unminify it
            if (scrollY < 50 && header.hasClass("minify")) {
                header.removeClass("minify");
                window.setTimeout(function () {
                    mainNavHeight = mainNav[0].getBoundingClientRect().height;
                    root.style.setProperty("--mainNavHeight", mainNavHeight + "px");
                    ScrollTrigger.refresh();
                }, 250);
            }

            lastScrollPosition = scrollY;
        }, 50)
    );
};

ET.initContentScrollTriggers = function () {
    var triggerSelector = "[data-scroll-fade], [data-scroll-fade-children] > *";

    ScrollTrigger.batch(triggerSelector, {
        start: "top bottom",
        end: "bottom top",
        interval: 0.125,
        batchMax: 64,
        onEnter: function (batch) {
            gsap.to(batch, {
                opacity: 1,
                y: 0,
                duration: 1,
                ease: "expo.out",
                stagger: 0.375 / batch.length,
                overwrite: true,
            });
        },
    });
};

ET.initFitVids = function () {
    fitvids();
};

ET.initShareLinks = function () {
    $("[data-share]").on("click", function (e) {
        e.preventDefault();
        var url = $(this).attr("href");
        popupwindow(url, "Share", 600, 460);
    });
};

ET.initPopupLinks = function () {
    $("a.popup, .popup a").on("click", function () {
        // Hide menu and search form if shown
        $("html").removeClass("menu-active");
        bodyScrollLock.clearAllBodyScrollLocks();
    });

    $("a.popup, .popup a").magnificPopup({
        type: "iframe",
        mainClass: "mfp-fade",
        removalDelay: 510,
        closeBtnInside: false,
        disableOn: function () {
            return window.matchMedia(ET.mq.lg).matches;
        },
    });
};

ET.getTextStyles = function (el) {
    var styles = window.getComputedStyle(el),
        textStyles = [];

    if (styles.textTransform !== "none") {
        textStyles.push("<tr><td>Text Transform</td><td>" + styles.textTransform + "</td></tr>");
    }

    textStyles.push("<tr><td>Font Weight</td><td>" + styles.fontWeight + "</td></tr>");
    textStyles.push("<tr><td>Font Size</td><td>" + styles.fontSize + "</td></tr>");
    textStyles.push("<tr><td>Line Height</td><td>" + styles.lineHeight + "</td></tr>");

    if (styles.fontStyle !== "normal") {
        textStyles.push("<tr><td>Font Style</td><td>" + styles.fontStyle + "</td></tr>");
    }

    if (styles.letterSpacing !== "normal") {
        textStyles.push("<tr><td>Letter Spacing</td><td>" + styles.letterSpacing + "</td></tr>");
    }

    return '<table class="table text-blue" style="width: auto;">' + textStyles.join("") + "</table>";
};

ET.initStyleGuide = function () {
    var textStyleContainers = $("[data-text-styles]");

    textStyleContainers.each(function () {
        var container = $(this),
            children = container.find("tr");

        container.data("children", children);
    });

    var updateStyleGuide = function () {
        textStyleContainers.each(function () {
            var container = $(this),
                selector = container.data("text-styles"),
                target = $(selector),
                styles;

            if (target.length) {
                styles = ET.getTextStyles(target[0]);
            } else {
                styles = "Target not found";
            }

            styles = $(styles).append(container.data("children"));
            container.html(styles);
        });
    };

    $(window).resize(updateStyleGuide);

    updateStyleGuide();
};

ET.initPageHeader = function () {
    var pageHeaderSliders = $("section.page-header-large .swiper");

    pageHeaderSliders.each(function () {
        var slider = $(this);

        new Swiper(slider[0], {
            loop: true,
            keyboard: true,
            autoplay: {
                delay: 5000,
            },
            effect: "fade",
        });
    });
};

ET.initSliders = function () {
    var defaultSliders = $(".default-slider");

    defaultSliders.each(function () {
        var slider = $(this);

        new Swiper(slider[0], {
            autoHeight: true,
            loop: true,
            keyboard: true,
            mousewheel: {
                forceToAxis: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    });

    var imageSliders = $(".image-slider");

    imageSliders.each(function () {
        var slider = $(this);

        new Swiper(slider[0], {
            autoHeight: true,
            slidesPerView: 1,
            spaceBetween: 10,
            loop: true,
            keyboard: true,
            mousewheel: {
                forceToAxis: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                768: {
                    slidesPerView: 3,
                },
            },
        });
    });
};

ET.initTestimonials = function () {
    var sliders = $(".testimonials-slider");

    sliders.each(function () {
        var slider = $(this);

        new Swiper(slider[0], {
            autoHeight: true,
            loop: true,
            keyboard: true,
            mousewheel: {
                forceToAxis: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            pagination: {
                el: ".swiper-pagination",
                type: "bullets",
                clickable: true,
            },
        });
    });
};

ET.loadPosts = function (url) {
    var section = $(".post-grid"),
        content = section.find(".posts"),
        header = $("header");

    gsap.to(section.find(".posts"), {
        opacity: 0,
        duration: 0.25,
        ease: "power2.in",
        overwrite: true,
        onComplete: function () {
            $.get(url, function (data) {
                var children = $(data).find(".post-grid .posts");
                content.after(children).remove();
                $(".pagination a").attr("data-no-swup", "");
                ET.initContentScrollTriggers();
            });
        },
    });

    var rect = section[0].getBoundingClientRect();
    if (rect.top < 0) {
        window.scrollTo({
            top: section.offset().top - header.outerHeight() - 100,
            behavior: "smooth",
        });
    }

    // window.history.replaceState(null, "", url);
};

ET.initPostFilters = function () {
    var form = $("form.post-filter"),
        preset = $("form.preset-filter"),
        action = form.attr("action"),
        data;

    if (!form.length) {
        return;
    }

    if (preset.length) {
        data = form.serialize();
        ET.loadPosts(action + "?" + data);
    }

    form.find("select[multiple]").select2({
        placeholder: "All locations",
    });

    action = action.replace(window.location.protocol + "//", "").replace(window.location.hostname, "");

    form.on("submit", function (e) {
        e.preventDefault();
        data = form.serialize();
        ET.loadPosts(action + "?" + data);
    });
};

ET.initPostFiltersReset = function () {
    var form = $("form.post-filter"),
        button = form.find(".reset-filters");

    if (!button.length) {
        return;
    }

    var inputs = form.find('input[type="text"], select');

    form.on("submit", function () {
        var filterHasValue = false;

        inputs.each(function () {
            if ($(this).val()) {
                filterHasValue = true;
            }
        });

        gsap.to(button, {
            opacity: filterHasValue ? 1 : 0,
            duration: 0.25,
            onComplete: function () {
                button.prop("disabled", !filterHasValue);
            },
        });
    });

    button.on("click", function () {
        inputs.val("");
        form.find("select[multiple]").trigger("change");
        form.trigger("submit");
    });
};

ET.initPostFiltersPagination = function () {
    var section = $("section.post-grid"),
        form = $("form.post-filter"),
        input = form.find('input[name="posts_per_page"]');

    if (!input.length) {
        return;
    }

    section.on("change", 'select[name="posts_per_page"]', function () {
        var posts_per_page = $(this).val();
        input.val(posts_per_page);
        form.trigger("submit");
    });
};

ET.initPagination = function () {
    var section = $("section.post-grid");

    if (!section.length) {
        return;
    }

    $(".pagination a").attr("data-no-swup", "");

    section.on("click", ".pagination a", function (e) {
        e.preventDefault();
        var href = $(this).attr("href");
        ET.loadPosts(href);
    });
};

ET.initFAQs = function () {
    var section = $("section.faqs");

    if (!section.length) {
        return;
    }

    section.find("dt").on("click", function () {
        $(this).toggleClass("active");
    });
};

ET.initBanners = function () {
    var banners = $("section.banner-image");

    if (!banners.length) {
        return;
    }

    banners.each(function () {
        var banner = $(this);

        gsap.fromTo(
            banner.find("img"),
            {
                scale: 1.25,
            },
            {
                scale: 1,
                scrollTrigger: {
                    trigger: banner,
                    start: "top bottom",
                    end: "bottom top",
                    scrub: true,
                    ease: "expo.out",
                },
            }
        );
    });
};

ET.initFooterCTA = function () {
    var section = $("section.footer-cta"),
        pathRadius = 10;

    if (!section.length) {
        return;
    }

    var d = "M0,0 L480,0 C490,0 500,9 500,20 L500,80 C500,90 509,100 520,100 L1000,100";
    var d2 = "M0,0 L{0},0 C{1},0 500,{2} 500,{3} L500,{4} C500,{5} {6},100 {7},100 L1000,100";

    var h1 = section.find("h1"),
        span1 = section.find(".path-wrap-1"),
        path1 = section.find("svg.footer-cta-path-1 path"),
        span2 = section.find(".path-wrap-2"),
        svg2 = section.find("svg.footer-cta-path-2"),
        path2 = section.find("svg.footer-cta-path-2 path");

    console.log(path2);

    var redrawPath = function () {
        var h1Rect = h1[0].getBoundingClientRect(),
            path2Rect = path2[0].getBoundingClientRect(),
            path2Width = h1Rect.x + h1Rect.width - path2Rect.x,
            scaleRatio = path2Width / 1000,
            pathRadiusScaled = pathRadius * scaleRatio;

        console.log(h1Rect);
        console.log(path2Rect);
        console.log(path2Width);

        svg2.width(path2Width);

        d2Formatted = d2
            .replace("{0}", 500 - pathRadiusScaled * 2)
            .replace("{1}", 500 - pathRadiusScaled)
            .replace("{2}", pathRadius * 0.9)
            .replace("{3}", pathRadius * 2)
            .replace("{4}", 100 - pathRadius * 2)
            .replace("{5}", 100 - pathRadius)
            .replace("{6}", 500 + pathRadiusScaled * 0.9)
            .replace("{7}", 500 + pathRadiusScaled * 2);

        path2.attr("d", d2Formatted);

        console.log(path2);
    };

    $(window).on("resize", throttle(redrawPath, 100));

    redrawPath();
};

$(document).ready(function () {
    ET.initHeaderMenu();
    ET.initHeaderScrollBehavior();
    ET.initContentScrollTriggers();
    ET.initFitVids();
    ET.initShareLinks();
    ET.initPopupLinks();
    ET.initStyleGuide();
    ET.initPageHeader();
    ET.initSliders();
    ET.initTestimonials();
    ET.initPostFilters();
    ET.initPostFiltersReset();
    ET.initPostFiltersPagination();
    ET.initPagination();
    ET.initFAQs();
    ET.initBanners();
    ET.initFooterCTA();
});
