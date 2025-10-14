(function () {
    "use strict";
    const { registerBlockType } = wp.blocks;
    const { createElement: el } = wp.element;

    registerBlockType( "wc-donation/feature-shortcode", {
        title: "WC Feature Donation",
        description: "Gutenberg Block for Featured Campaigns.",
        icon: "format-aside",
        category: "layout",
        attributes: {
            displayFormat: {
                type: 'string',
                default: 'grid'
            }
        },
        edit: (props) => {
            return el(
                "div",
                { className: props.className },
                el( "p", null, "WC Feature Donation Block" )
            );
        },
        save(props) {
            return null;
        }
    });
})();
