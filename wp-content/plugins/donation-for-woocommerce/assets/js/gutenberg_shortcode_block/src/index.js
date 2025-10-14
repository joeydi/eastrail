import { SelectControl, RadioControl, TextControl, Flex, FlexBlock, FlexItem} from "@wordpress/components"
const { registerBlockType } = wp.blocks;

var wpep_block_container = {
    "display": 'flex',
    "justify-content": 'space-between',
    "align-items": 'center',
    "flex-wrap": 'wrap',
    "width": '100%',
};

registerBlockType('wc-donation/shortcode', {

    title: 'WC Donation',
    description: 'Block to add donation campaign shortcode to the page',
    icon: 'format-aside',
    category: 'layout',
    attributes: {
        donation_ids: {
            type: 'array',
            default: [""]

        },
        is_block: {
            type: 'boolean',
        },
        formattypes: {
            type: 'string',
            default: "block"
        },
        displaytype: {
            type: 'string',
            default: "page"
        },
        popup_header: {
            type: 'string',
            default: "Campaigns"
        },
        popup_button: {
            type: 'string',
            default: "View Campaigns"
        },
        display_button: {
            type: 'string',
            default: "auto_display"
        }
    },
    edit(props) {

        if ( props.attributes.displaytype == 'page' ) {
            var style = { display: "none" };
        }

        var options = [ { value: '', label: 'Select a Campaign', disabled: true } ];
        var displayformatoptions = [ 
            { value:'list', label: 'List' },
            { value:'block', label: 'Block' },
            { value:'table', label: 'Table' },
            { value:'grid', label: 'Grid' } 
        ]
        var displaytypeoptions = [
            { label: 'Page', value: 'page' },
            { label: 'Popup', value: 'popup' }
        ]
        var displaybuttonoptions = [
            { label: 'Auto Display', value: 'auto_display' },
            { label: 'Button Display', value: 'button_display' }
        ]
        var p = wc_donation_forms.forms;

        for (var key in p) {
            if (p.hasOwnProperty(key)) {
                var form_id = p[key].ID;
                var form_title = p[key].title;
                if (props.attributes.donation_ids.includes(form_id)) {
                    options.push({'value':form_id, 'label': form_title, selected: true})
                } else {
                    options.push({'value':form_id, 'label': form_title})
                }
            }
        }

        var donation_ids = props.attributes.donation_ids;
 
        function wpep_shortcode_change(e) {
            // props.attributes.donation_ids = e
            props.setAttributes({donation_ids: e})
            props.setAttributes({is_block: true})

        }

        function display_format_change(e) {
            props.setAttributes({formattypes: e})
        }

        function display_type_change(e) {
            if( e == 'page' ){
                document.getElementById("popup-settings").style.display = "none";
            }else{
                document.getElementById("popup-settings").style.display = "flex";
            }
            props.setAttributes({displaytype: e})
        }
        
        function display_popup_title(e) {
            props.setAttributes({popup_header: e})
        }

        function display_popup_button(e) {
            props.setAttributes({popup_button: e})
        }

        function display_button_change(e) {
            props.setAttributes({display_button : e})
        }
        return (
            <>
                <Flex>
                    <FlexBlock style={{marginRight: "10px"}}>
                        <SelectControl className="abc" label='Select Campaign : ' help='Select the Campaign you want to display.' defaultValue={props.attributes.donation_ids} multiple onChange={wpep_shortcode_change} options={options} /> 
                    </FlexBlock>
                    <FlexBlock style={{marginRight: "10px"}}>
                        <SelectControl label='Campaign Display Format : ' help='Select the style you want to display.' defaultValue={props.attributes.formattypes} onChange={display_format_change} options={displayformatoptions}/>
                    </FlexBlock>
                    <FlexItem>
                        <RadioControl label="Campaign Display Type" help="Campaign Display Type" selected={props.attributes.displaytype} options={displaytypeoptions} onChange={display_type_change} />
                    </FlexItem>                
                </Flex>
                <Flex id="popup-settings" style={style}>
                    <FlexBlock style={{marginRight: "10px"}}>
                        <TextControl label="Pop-up Header Text" help="Enter the title for the popup screen" value={props.attributes.popup_header} onChange={display_popup_title} />
                    </FlexBlock>
                    <FlexBlock style={{marginRight: "10px"}}>
                        <TextControl label="Button Text" help="Enter the text for the button" value={props.attributes.popup_button} onChange={display_popup_button} />
                    </FlexBlock>
                    <FlexItem>
                        <RadioControl label="Display Popup" help="Display popup via button or on page load" selected={props.attributes.display_button} options={displaybuttonoptions} onChange={display_button_change} />
                    </FlexItem>                
                </Flex>
            </>
        );

    },
    save(props) {
        return null;
    }

});
