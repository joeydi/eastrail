var gulp = require("gulp"),
    plumber = require("gulp-plumber"),
    add = require("gulp-add-src"),
    size = require("gulp-size"),
    sass = require("gulp-dart-sass"),
    postcss = require("gulp-postcss"),
    autoprefixer = require("autoprefixer"),
    jshint = require("gulp-jshint"),
    concat = require("gulp-concat"),
    uglify = require("gulp-uglify"),
    strip = require("gulp-strip-comments"),
    livereload = require("gulp-livereload"),
    sourcemaps = require("gulp-sourcemaps"),
    sprite = require("gulp-svg-sprite");

gulp.task("css", function () {
    var includes = [
        "../../../../wp-includes/css/dist/block-library/style.min.css",
        "./node_modules/magnific-popup/dist/magnific-popup.css",
        "./node_modules/swiper/swiper-bundle.min.css",
    ];

    return gulp.src(includes).pipe(concat("_plugins.scss")).pipe(gulp.dest("sass/global")).pipe(livereload());
});

gulp.task("sass", function () {
    var options = {
        sourceMap: true,
        outputStyle: "compressed",
        includePaths: ["node_modules"],
    };

    return gulp
        .src("./sass/**/*.scss")
        .pipe(
            plumber(function (error) {
                console.error(error.message);
                this.emit("end");
            })
        )
        .pipe(sourcemaps.init())
        .pipe(sass(options))
        .pipe(postcss([autoprefixer]))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest("./css"))
        .pipe(livereload());
});

gulp.task("icons", function () {
    var options = {
        shape: {
            dimension: {
                maxWidth: 100,
                maxHeight: 100,
            },
        },
        svg: {
            xmlDeclaration: false,
            doctypeDeclaration: false,
            namespaceIDs: false,
            namespaceClassnames: false,
        },
        mode: {
            symbol: true,
        },
    };

    return gulp.src("./icons/*.svg").pipe(sprite(options)).pipe(gulp.dest("icons"));
});

gulp.task("jshint", function () {
    var scripts = ["./js/utils.js", "./js/main.js"];

    return gulp.src(scripts).pipe(jshint()).pipe(jshint.reporter("jshint-stylish")).pipe(jshint.reporter("fail"));
});

gulp.task("js", function () {
    var main = ["./js/utils.js", "./js/main.js"];

    var plugins = [
        "./node_modules/fitvids/dist/fitvids.min.js",
        "./node_modules/gsap/dist/gsap.min.js",
        "./node_modules/gsap/dist/ScrollTrigger.min.js",
        "./node_modules/swiper/swiper-bundle.min.js",
        "./node_modules/lazysizes/lazysizes.min.js",
        "./node_modules/magnific-popup/dist/jquery.magnific-popup.min.js",
        "./node_modules/body-scroll-lock/lib/bodyScrollLock.min.js",
    ];

    return gulp
        .src(main)
        .pipe(
            plumber(function (error) {
                console.error(error.message);
                this.emit("end");
            })
        )
        .pipe(uglify())
        .pipe(add.prepend(plugins))
        .pipe(strip())
        .pipe(size({ showFiles: true }))
        .pipe(concat("main.min.js"))
        .pipe(gulp.dest("js"))
        .pipe(livereload());
});

gulp.task("watch", function () {
    livereload.listen();
    gulp.watch("./sass/**/*.scss", gulp.series("sass"));
    gulp.watch("./icons/*.svg", gulp.series("icons"));
    gulp.watch(["./js/main.js"], gulp.series("jshint", "js"));
});

gulp.task("default", gulp.series("css", "sass", "icons", "jshint", "js", "watch"));
