<?php
// Output to backend
function advanced_booking_calendar_more_features() {
	
	if (!current_user_can(abc_booking_admin_capabilities())) {
		wp_die("You don't have access to this page.");
    }
    global $abcUrl;
	wp_enqueue_script('uikit-js', $abcUrl.'backend/js/uikit.min.js', array('jquery'));
    wp_enqueue_style('uikit', $abcUrl.'/frontend/css/uikit.gradient.min.css');
    $output = '<div class="wrap">
            <h3>'.__('Add more features with our Pro Version', 'advanced-booking-calendar').' <a class="uk-button uk-button-primary" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreButtonTitle" target="_blank">'.__('Get it here', 'advanced-booking-calendar').'</a></h3>
                <div class="uk-grid" data-uk-grid-margin="">
                    <div class="uk-width-medium-1-3 uk-row-first">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreStripe" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-cc-stripe"></i> '.__('Stripe Integration (Credit Card)', 'advanced-booking-calendar').'</h3>
                            '.__('Stripe is an easy way to accept credit card. After the credit card details are entered and validated bookings get automatically confirmed.
                            </br>', 'advanced-booking-calendar').'
                            '.__('You can enter your Payment Credentials, select the currency and activate or deactivate the Stripe Test Environment.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MorePayPal" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-cc-paypal"></i> '.__('PayPal Gateway', 'advanced-booking-calendar').'</h3>
                            '.__('You can offer PayPal payments to your guests. A valid and paid booking is set to ‘confirmed’ automatically. Just enter your PayPal API credentials and you are ready to go.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MorePaymentFees" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-legal"></i> '.__('Payment Fees', 'advanced-booking-calendar').'</h3>
                            '.__('Your guests want to pay via PayPal but you don’t like their payment fees? Just let your guest decide if an additional fee is worth using PayPal or Stripe.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreDiscountCodes" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-ticket"></i> '.__('Discount Codes', 'advanced-booking-calendar').'</h3>
                            '.__('Use Discount Codes to raise the number of consecutive nights per stay by offering Discounts at a certain number of nights. Just use the new Coupon feature and start promoting your accommodation with Coupons.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreCustomInputs" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-cogs"></i> '.__('Custom Inputs', 'advanced-booking-calendar').'</h3>
                            '.__('The Pro Version offers you three custom inputs for your booking form. Just activate them in the settings, name them anything you want and decide whether they are optional or mandatory. They will be shown in the booking list and you can use them in the email templates.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreDiscountsLength" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-diamond"></i> '.__('Discount by Length of Stay', 'advanced-booking-calendar').'</h3>
                            '.__('You can add discounts which will be applied automatically when a given number of nights is reached.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreExtrasCalendars" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-object-group"></i> '.__('Combine Extras with Calendars', 'advanced-booking-calendar').'</h3>
                            '.__('If you have some Extras, which are not available in certain rooms, you can now manage them at will. Just tick the check boxes for each calendar and the extra will only show up for those rooms.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreIcalExport" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-calendar-check-o"></i> '.__('iCal Export', 'advanced-booking-calendar').'</h3>
                            '.__('Enable an iCal export of your confirmed bookings for each calendar (just edit an existing calendar).', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreMailchimp" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-paper-plane"></i> '.__('MailChimp', 'advanced-booking-calendar').'</h3>
                            '.__('Automatically add your guests to your MailChimp list and send them personal newsletters. When configuring the integration you have two options: Use double-opt-in or not and show a checkbox in the booking form or sign the guests up automatically. You can also change the text of the label for the subscribe-checkbox.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreTimedEmails" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-clock-o"></i> '.__('Send timed Emails to your Guests', 'advanced-booking-calendar').'</h3>
                            '.__('Create Reminder and Feedback Emails which will be sent before the guests arrival or after his departure. You define how many days before or after a stay the Emails will be send and of course their content.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                    <div class="uk-width-medium-1-3">
                        <a class="uk-panel uk-panel-box" href="https://www.booking-calendar-plugin.com/pro-download/?cmp=MoreUnlimited" target="_blank">
                            <h3 class="uk-panel-title"><i class="uk-icon-unlock-alt"></i> '.__('Unlimited Extras and Rooms', 'advanced-booking-calendar').'</h3>
                            '.__('If you have some Extras, which are not available in certain rooms, you can now manage them at will. The number of Extras and Rooms is not limited in the Pro Version. Create as many as you want.', 'advanced-booking-calendar').'
                        </a>
                    </div>
                </div>
            </div>';
    echo $output;
}    
?>