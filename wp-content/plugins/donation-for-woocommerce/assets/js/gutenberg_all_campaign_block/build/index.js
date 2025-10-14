(function () {
    "use strict";
    const { registerBlockType } = wp.blocks;
    const { createElement: el } = wp.element;

    registerBlockType( "wc-donation/all-campaign-shortcode", {
        title: "WC All Donation",
        description: "Gutenberg Block for All Campaigns.",
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
                el( "p", null, "WC Show All Donation Block" )
            );
        },
        save(props) {
            return null;
        }
    });
})();
