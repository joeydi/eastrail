/* global jQuery, google */

var $ = $ || jQuery,
    ET = ET || {};

gsap.registerPlugin(DrawSVGPlugin, ScrollTrigger, SplitText);

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

    gsap.set("header .menu > li > .sub-menu", { height: 0 });
    $(".sub-menu-toggle").on("click", function () {
        var thisParent = $(this).closest("li"),
            thisMenu = thisParent.find("> .sub-menu"),
            activeParents = $("ul.menu > li.active").not(thisParent),
            activeMenus = activeParents.find("> .sub-menu");

        activeParents.removeClass("active");
        thisParent.toggleClass("active");

        gsap.to(activeMenus, {
            height: 0,
            duration: 0.5,
            ease: "power2.out",
        });

        gsap.to(thisMenu, {
            height: thisParent.hasClass("active") ? "auto" : 0,
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
        html.removeClass("menu-active");
        bodyScrollLock.clearAllBodyScrollLocks();
    });
};

ET.initHeaderScrollBehavior = function () {
    var header = $("header");

    $(window).on(
        "scroll",
        throttle(function () {
            header.toggleClass("minify", window.scrollY > 100);
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

        gsap.to(banner.find("picture"), {
            yPercent: 20,
            ease: "none",
            scrollTrigger: {
                trigger: banner,
                start: "top bottom",
                end: "bottom top",
                scrub: true,
            },
        });
    });
};

ET.initFooterCTA = function () {
    var section = $("section.footer-cta"),
        pathRadius = 8;

    if (!section.length) {
        return;
    }

    var d2 = "M0,0 L{0},0 C{1},0 500,{2} 500,{3} L500,{4} C500,{5} {6},100 {7},100 L1000,100";

    var h1 = section.find("h1"),
        svg1 = section.find("svg.footer-cta-path-1"),
        path1 = section.find("svg.footer-cta-path-1 path"),
        svg2 = section.find("svg.footer-cta-path-2"),
        path2 = section.find("svg.footer-cta-path-2 path"),
        btn = section.find(".btn");

    var split = new SplitText(h1, {
        type: "words",
    });

    var path1Timeline = new gsap.timeline({
        scrollTrigger: {
            // markers: true,
            trigger: svg1,
            start: "top 90%",
            // end: "bottom center",
            // scrub: true,
        },
    });

    // path1Timeline.from(
    //     path1,
    //     {
    //         drawSVG: 0,
    //         duration: 3,
    //         ease: "power2.inOut",
    //     },
    //     0
    // );

    path1Timeline.from(split.words, {
        duration: 1,
        x: -100,
        autoAlpha: 0,
        stagger: 0.015,
        ease: "power2.out",
    });

    var path2Timeline = new gsap.timeline({
        scrollTrigger: {
            // markers: true,
            trigger: svg2,
            start: "top 90%",
            // end: "bottom center",
            // scrub: true,
        },
    });

    path2Timeline.from(
        path2,
        {
            drawSVG: 0,
            duration: 3,
            ease: "power2.out",
        },
        0
    );

    path2Timeline.from(
        btn,
        {
            opacity: 0,
            scale: 0.75,
            x: -40,
            duration: 1,
            ease: "back.out",
        },
        0.85
    );

    var redrawPath = function () {
        var h1Rect = h1[0].getBoundingClientRect(),
            path1Width = h1Rect.x - 16,
            path2Rect = svg2[0].getBoundingClientRect(),
            path2Width = h1Rect.x + h1Rect.width - path2Rect.x,
            scaleRatio = 1000 / path2Width,
            pathRadiusScaled = pathRadius * scaleRatio;

        svg1.width(path1Width);
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
    };

    $(window).on("load resize", throttle(redrawPath, 100));

    redrawPath();
};

ET.initTimeline = function () {
    var section = $(".timeline");

    if (!section.length) {
        return;
    }

    ScrollTrigger.matchMedia({
        // large
        "(min-width: 768px)": function () {
            $(".milestone").each(function () {
                var milestone = $(this),
                    image = milestone.find(".image"),
                    even = milestone.hasClass("even");

                gsap.fromTo(
                    image,
                    {
                        yPercent: even ? 100 : 50,
                    },
                    {
                        yPercent: even ? -100 : -50,
                        ease: "none",
                        scrollTrigger: {
                            trigger: milestone,
                            start: "top bottom",
                            end: "bottom top",
                            scrub: 1,
                        },
                    }
                );
            });
        },
    });
};

ET.initMapEmbed = function () {
    var container = $("main.map-embed");

    if (!container.length) {
        return;
    }

    var infowindow = new google.maps.InfoWindow();
    // var bounds = new google.maps.LatLngBounds();

    var mapNode = container.find(".map")[0];
    var options = {
        center: {
            lat: 47.63,
            lng: -122.18,
        },
        zoom: 12,
        maxZoom: 18,
        // mapId: "5f57204fb11ce09d",
        mapTypeId: google.maps.MapTypeId.ROADMAP,
    };

    var map = new google.maps.Map(mapNode, options);
    ET.map = map;

    // Import the GeoJSON data and style programmatically

    var geojson = container.data("geojson");
    map.data.loadGeoJson(geojson);

    map.data.setStyle(function (feature) {
        var ascii = feature.getProperty("ascii");
        var color = ascii > 91 ? "red" : "blue";
        return {
            fillColor: color,
            strokeWeight: 1,
        };
    });

    var colors = {
        // Open Trail Sections (Green/Yellow/Blue)
        133408389: "blue",
        // Connections
        752318534: "blue",
        // Parks
        499410108: "blue",
        // Other Trail Systems
        2333824518: "blue",
        // Future Trails & Projects (Red)
        1160417020: "red",
        // Parking
        3979213705: "blue",
        // Restrooms
        603738419: "blue",
    };

    map.data.setStyle(function (feature) {
        var group = feature.getProperty("group");

        return {
            strokeColor: feature.getProperty("stroke"),
            strokeWeight: 4,
            icon: [
                ET.template_directory_url,
                "/static/img/map-marker-",
                feature.getProperty("marker-symbol"),
                "-",
                colors[group],
                ".svg",
            ].join(""),
        };
    });

    map.data.addListener("click", function (event) {
        var title = event.feature.getProperty("title"),
            description = event.feature.getProperty("description"),
            html = [title ? "<strong>" + title + "</strong><br/>" : "", description].join("");

        infowindow.setContent(html);
        infowindow.setPosition(event.latLng);
        infowindow.setOptions({ pixelOffset: new google.maps.Size(0, -32) });
        infowindow.open(map);
    });

    // Set up click handlers for data layers UI

    var groups = container.find(".group-layer"),
        features = container.find(".feature-layer"),
        visibiltyToggles = container.find(".visibility");

    groups.on("click", function (e) {
        if (!$(e.target).is("button") && !$(e.target).closest("button").length) {
            $(this).closest("li").find("ul").slideToggle();
        }
    });

    features.on("click", function (e) {
        var id = $(this).data("feature"),
            feature = map.data.getFeatureById(id);

        if (!feature) {
            console.error("Feature with ID " + id + " not found.");
            return;
        }

        var bounds = new google.maps.LatLngBounds();
        feature.getGeometry().forEachLatLng(function (latLng) {
            bounds.extend(latLng);
        });
        map.fitBounds(bounds);
    });

    visibiltyToggles.on("click", function (e) {
        $(this).closest("li").toggleClass("hidden");
    });
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
    ET.initTimeline();
});

$(window).on("load", function () {
    ET.initFooterCTA();
});
