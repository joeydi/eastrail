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

ET.cleanupFunctions = [];
ET.cleanup = function () {
    // Execute each of the cleanup functions
    for (var i = 0; i < ET.cleanupFunctions.length; i++) {
        ET.cleanupFunctions[i]();
    }

    // Reset the array
    ET.cleanupFunctions = [];
};

ET.initSwup = function () {
    var linkNotExpression =
        ':not([data-no-swup], [target="_blank"], [href*=".pdf"], [href*=".jpg"], [href*=".png"], [href*=".gif"])';
    var linkSelectors = [
        'a[href^="' + window.location.origin + '"]' + linkNotExpression,
        'a[href^="/"]' + linkNotExpression,
        'a[href^="#"]' + linkNotExpression,
    ];

    ET.swup = new Swup({
        containers: ["#main"],
        linkSelector: linkSelectors.join(", "),
        plugins: [
            new SwupMorphPlugin({
                containers: ["#wpadminbar"],
            }),
            new SwupBodyClassPlugin(),
            new SwupScrollPlugin({
                offset: function () {
                    return header.height() + 20;
                },
            }),
        ],
    });

    // Disable scroll animation for page transitions
    // var scrollPlugin = ET.swup.findPlugin("ScrollPlugin");

    // ET.swup.on("animationOutStart", function () {
    //     scrollPlugin.options.animateScroll = false;
    // });

    // ET.swup.on("animationInDone", function () {
    //     scrollPlugin.options.animateScroll = true;
    // });

    ET.swup.on("pageView", function () {
        // Hide menu and search form if shown
        $("html").removeClass("menu-active").removeClass("search-active");
        bodyScrollLock.clearAllBodyScrollLocks();

        // Scroll to the top
        // window.scrollTo({
        //     top: 0,
        //     behavior: "instant",
        // });

        // Update active nav item
        $("header li").removeClass("current-menu-item");
        $("header a[href='" + window.location.href + "']")
            .closest("li")
            .addClass("current-menu-item");

        // Track pageview in Google Analytics
        // if (typeof ga !== "undefined") {
        //     ga("gtm10.set", "page", window.location.pathname);
        //     ga("gtm10.send", "pageview");
        // }

        objectFitPolyfill();
        ET.newPageReady();
    });
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
                "header .menu-header > *, header .menu-search, header .menu-buttons li, header .menu > li, header .utility > *",
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

    submenu_items.append('<button class="sub-menu-toggle"><svg><use xlink:href="#chevron-down" /></svg></button>');

    gsap.set(".sub-menu", { height: 0 });
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

ET.initSearchOverlay = function () {
    var html = $("html"),
        toggle = $(".search-toggle"),
        close = $("section.search-form .close"),
        input = $('section.search-form input[type="text"]');

    toggle.on("click", function (e) {
        html.toggleClass("search-active");

        if (html.hasClass("search-active")) {
            input.trigger("focus");
        }
    });

    close.on("click", function (e) {
        html.removeClass("search-active");
    });
};

ET.initSearchForms = function () {
    $(document).on("submit", "form.menu-search, .search-form form, form.search", function (e) {
        e.preventDefault();

        var form = $(this),
            action = form.attr("action"),
            data = form.serialize();

        ET.swup.loadPage({ url: action + "?" + data });

        window.setTimeout(function () {
            document.activeElement.blur();
            form[0].reset();
        }, 500);
    });
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

ET.initLocationsMap = function () {
    var container = $(".locations-map");

    if (!container.length) {
        return;
    }

    var infowindow = new google.maps.InfoWindow();
    var bounds = new google.maps.LatLngBounds();

    var mapNode = container.find(".map")[0];
    var options = {
        zoom: 15,
        mapId: "5f57204fb11ce09d",
        // mapTypeId: google.maps.MapTypeId.ROADMAP,
    };

    var map = new google.maps.Map(mapNode, options);

    var locations = $(".location-excerpt");
    locations.each(function () {
        var location = $(this),
            lat = location.data("lat"),
            lng = location.data("lng");

        if (lat && lng) {
            latLng = new google.maps.LatLng(location.data("lat"), location.data("lng"));
        }

        var marker = new google.maps.Marker({
            position: latLng,
            map: map,
            icon: {
                url: ET.template_directory_url + "/static/img/map-marker.png",
                scaledSize: new google.maps.Size(30, 40),
            },
            zIndex: 1,
        });

        bounds.extend(latLng);

        var content = location.find(".content").html();

        google.maps.event.addListener(marker, "click", function () {
            infowindow.setContent(content);
            infowindow.open(map, marker);
        });

        google.maps.event.addListener(marker, "mouseover", function () {
            marker.setIcon({
                url: ET.template_directory_url + "/static/img/map-marker-active.png",
                scaledSize: new google.maps.Size(30, 40),
            });
        });

        google.maps.event.addListener(marker, "mouseout", function () {
            marker.setIcon({
                url: ET.template_directory_url + "/static/img/map-marker.png",
                scaledSize: new google.maps.Size(30, 40),
            });
        });
    });

    map.fitBounds(bounds);

    google.maps.event.addListener(map, "click", function () {
        infowindow.close();
    });

    ScrollTrigger.matchMedia({
        "(min-width: 992px)": function () {
            ScrollTrigger.create({
                trigger: container,
                start: "bottom bottom-=40px",
                endTrigger: $(".locations-list"),
                end: "bottom bottom-=40px",
                pin: true,
            });
        },
    });
};

ET.initForms = function () {
    var forms = $("div.gform");

    forms.each(function () {
        var form = $(this),
            id = form.data("id");

        $.get(
            ET.ajaxurl,
            {
                action: "load_gravity_form",
                id: id,
            },
            function (data) {
                form.html(data);
            }
        );
    });
};

ET.initPharmacyTable = function () {
    $("#tablepress-4").on("init.dt", function () {
        // Remove the "Search" label
        $(".dataTables_filter label")
            .contents()
            .filter(function () {
                return this.nodeType === Node.TEXT_NODE;
            })
            .remove();

        // Replace with new hidden label
        $(".dataTables_filter label").prepend('<span class="visually-hidden">Search</span>');

        // Add the .form-control class to the search input, and update the placeholder text
        $(".dataTables_filter input").addClass("form-control").attr("placeholder", "Search by pharmacy, address, city");

        // Remove the "Show XX entries" label
        $(".dataTables_length label")
            .contents()
            .filter(function () {
                return this.nodeType === Node.TEXT_NODE;
            })
            .remove();

        // Replace with new label
        $(".dataTables_length label").prepend('<span class="nowrap me-10">Per Page</span>');

        // Add the .form-control class to the length select
        $(".dataTables_length select").addClass("form-control form-control-sm").css("minWidth", "60px");
    });

    $("#tablepress-4").on("draw.dt", function () {
        // Add arrow icons to pagination buttons
        $(".paginate_button.previous").html(
            '<svg class="icon"><use xlink:href="#arrow-left" /></svg><span class="visually-hidden">Previous</span>'
        );
        $(".paginate_button.next").html(
            '<span class="visually-hidden">Next</span> <svg class="icon"><use xlink:href="#arrow-right" /></svg>'
        );
    });
};

// This get's called once on initial DOM ready
ET.initOnce = function () {
    ET.initSwup();
    ET.initHeaderMenu();
    ET.initHeaderScrollBehavior();
    ET.initSearchOverlay();
    ET.initSearchForms();
};

// This get's called as soon as the new content is in the DOM
ET.newPageReady = function () {
    ET.cleanup();
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
    ET.initLocationsMap();
    ET.initForms();
    ET.initPharmacyTable();
};

$(document).ready(function () {
    ET.initOnce();
    ET.newPageReady();
});

$(window).on("load", function () {
    objectFitPolyfill();
});
