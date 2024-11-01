const RESURSBANK_GET_ORDER_CONTENT = (controllerUrl, orderId) => {
    'use strict';

    const actions = [
        'woocommerce_add_order_fee',
        'woocommerce_remove_order_coupon',
        'woocommerce_add_coupon_discount',
        'woocommerce_calc_line_taxes',
        'woocommerce_add_order_item',
        'woocommerce_remove_order_item'
    ];

    let url = '';

    /**
     * Perform AJAX request to fetch order content.
     */
    const fetchContent = (event, request, settings) => {
        if (
            url === '' ||
            !settings.hasOwnProperty('data') ||
            typeof settings.data !== 'string'
        ) {
            return;
        }

        const action = new URLSearchParams(settings.data).get('action');

        if (actions.includes(action)) {
            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
                .then(handleFetchResponse)
                .then(handleFetchData)
                .catch(handleFetchFatalError);
        }
    };

    /**
     * Handle network/JS errors during fetch request.
     *
     * @param {any} error
     */
    const handleFetchFatalError = (error) => {
        if (error instanceof Error) {
            errorHandler(
                'Fatal: Failed to fetch updated order content. Please reload the page.'
            );
            console.log(error);
        }
    }

    /**
     * Resolve JSON data from request response.
     *
     * @param {Response} response
     * @returns {Promise<any>|undefined}
     */
    const handleFetchResponse = (response) => {
        if (!response.ok) {
            errorHandler(
                'Response Error: Failed to fetch updated order content. Please reload the page.'
            );
        } else {
            return response.json();
        }
    };

    /**
     * Apply new list of stores to stores select element.
     *
     * @param {any} data
     */
    const handleFetchData = (data) => {
        if (typeof data === 'undefined') {
            return;
        }

        jQuery('#resursbank_payment_info').html(data.payment_info);
        let orderNotes = jQuery('#woocommerce-order-notes').find('.inside');
        orderNotes.empty();
        orderNotes.append(data.order_notes);

        let messages = "";
        let messageBag = JSON.parse(data.messages);
        for (let iMessage = 0; iMessage < messageBag.length ; iMessage ++) {
            messages += messageBag[iMessage].message + "\r\n";
        }
        if (messages !== '') {
            alert(messages);
        }

        if (data.error) {
            errorHandler(data.error);
        } else {
            console.log(data);
        }
    };

    /**
     * Error handler.
     *
     * @param {string} message
     */
    const errorHandler = (message) => {
        alert(String(message));
    };

    const init = () => {
        url = new URL(controllerUrl);
        url.searchParams.append('orderId', orderId);

        jQuery(document).ajaxSuccess(fetchContent);
    };

   init();
};
