import { SelectControl, Flex, FlexBlock} from "@wordpress/components"
const { registerBlockType } = wp.blocks;

registerBlockType('wc-donation/checkout-block', {
    title: 'WC Donation Checkout',
    description: 'Easily checkout with the donation campaign on a single page.',
    icon: 'format-aside',
    category: 'layout',
    attributes: {
        campaign_id: {
            type: 'int',
            default: 0
        },
        style: {
            type: 'string',
            default: ''
        },
    },
    edit(props) {
        var options = [ 
            { 'ID': '', 'title': 'Select a Campaign' },
            ...wc_donation_forms.forms
        ];

        const campaign_options = Object.values(options).map((campaign) => ({
            label: campaign.title,
            value: campaign.ID,
        }));

        return (
            <>
                <Flex>
                    <FlexBlock style={{marginRight: "10px"}}>
                        <SelectControl 
                            label='Select Donation Campaign : ' 
                            help='Select the Campaign you want to display.' 
                            defaultValue={props.attributes.campaign_id} 
                            onChange={(e) => props.setAttributes({ campaign_id: e })} 
                            options={campaign_options} 
                        /> 
                    </FlexBlock> 
                    <FlexBlock style={{marginRight: "10px"}}>
                        <SelectControl 
                        label='Select Style : ' 
                        help='Select the Style of the campaign.' 
                        defaultValue={props.attributes.style}
                        onChange={(e) => props.setAttributes({ style: e })}
                        options={wc_donation_checkout_block.style_options} 
                    /> 
                    </FlexBlock> 
                </Flex>
            </>
        );

    },
    save(props) {
        return null;
    }

});
