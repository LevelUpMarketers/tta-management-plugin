<?php
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use net\authorize\api\constants\ANetEnvironment;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TTA_AuthorizeNet_API {
    protected $login_id;
    protected $transaction_key;
    protected $environment;

    public function __construct( $login_id = null, $transaction_key = null, $sandbox = null ) {
        $this->login_id        = $login_id        ?: ( defined( 'TTA_AUTHNET_LOGIN_ID' ) ? TTA_AUTHNET_LOGIN_ID : '' );
        $this->transaction_key = $transaction_key ?: ( defined( 'TTA_AUTHNET_TRANSACTION_KEY' ) ? TTA_AUTHNET_TRANSACTION_KEY : '' );
        if ( null === $sandbox ) {
            $sandbox = defined( 'TTA_AUTHNET_SANDBOX' ) ? TTA_AUTHNET_SANDBOX : true;
        }
        $this->environment = $sandbox ? ANetEnvironment::SANDBOX : ANetEnvironment::PRODUCTION;
    }

    /**
     * Charge a credit card using the Authorize.Net API.
     *
     * @param float $amount
     * @param string $card_number
     * @param string $exp_date  Format YYYY-MM
     * @param string $card_code
     * @param array $billing
     * @return array { success:bool, transaction_id?:string, error?:string }
     */
    public function charge( $amount, $card_number, $exp_date, $card_code, array $billing = [] ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber( $card_number );
        $creditCard->setExpirationDate( $exp_date );
        $creditCard->setCardCode( $card_code );

        $paymentOne = new AnetAPI\PaymentType();
        $paymentOne->setCreditCard( $creditCard );

        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType( 'authCaptureTransaction' );
        $transactionRequest->setAmount( $amount );
        $transactionRequest->setPayment( $paymentOne );

        if ( $billing ) {
            $address = new AnetAPI\CustomerAddressType();
            $address->setFirstName( $billing['first_name'] ?? '' );
            $address->setLastName( $billing['last_name'] ?? '' );
            $address->setAddress( $billing['address'] ?? '' );
            $address->setCity( $billing['city'] ?? '' );
            $address->setState( $billing['state'] ?? '' );
            $address->setZip( $billing['zip'] ?? '' );
            $transactionRequest->setBillTo( $address );
        }

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setTransactionRequest( $transactionRequest );

        $controller = new AnetController\CreateTransactionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            $tresponse = $response->getTransactionResponse();
            if ( $tresponse && $tresponse->getResponseCode() === '1' ) {
                return [
                    'success'         => true,
                    'transaction_id'  => $tresponse->getTransId(),
                ];
            }
            $err = $tresponse && $tresponse->getErrors()
                ? $tresponse->getErrors()[0]->getErrorText()
                : 'Transaction failed';
            return [ 'success' => false, 'error' => $err ];
        }

        $err = $response && $response->getMessages()->getMessage()
            ? $response->getMessages()->getMessage()[0]->getText()
            : 'API error';
        return [ 'success' => false, 'error' => $err ];
    }

    /**
     * Create a recurring subscription via the Authorize.Net API.
     *
     * @param float  $amount     Monthly charge amount.
     * @param string $card_number Credit card number.
     * @param string $exp_date   Expiration date YYYY-MM.
     * @param string $card_code  Card code/CVV.
     * @param array  $billing      Billing fields first_name,last_name,address,city,state,zip.
     * @param string $name         Optional subscription name.
     * @param string $description  Optional subscription description.
     * @return array { success:bool, subscription_id?:string, error?:string }
     */
    public function create_subscription( $amount, $card_number, $exp_date, $card_code, array $billing = [], $name = 'Membership Subscription', $description = '' ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber( $card_number );
        $creditCard->setExpirationDate( $exp_date );
        $creditCard->setCardCode( $card_code );

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard( $creditCard );

        $schedule = new AnetAPI\PaymentScheduleType();
        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
        $interval->setLength( 1 );
        $interval->setUnit( 'months' );
        $schedule->setInterval( $interval );
        $schedule->setStartDate( new DateTime( date( 'Y-m-d' ) ) );
        $schedule->setTotalOccurrences( 9999 );

        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName( $name );
        if ( $description ) {
            $subscription->setDescription( $description );
        }
        $subscription->setPaymentSchedule( $schedule );
        $subscription->setAmount( $amount );
        $subscription->setPayment( $payment );

        if ( $billing ) {
            $bill = new AnetAPI\NameAndAddressType();
            $bill->setFirstName( $billing['first_name'] ?? '' );
            $bill->setLastName( $billing['last_name'] ?? '' );
            $bill->setAddress( $billing['address'] ?? '' );
            $bill->setCity( $billing['city'] ?? '' );
            $bill->setState( $billing['state'] ?? '' );
            $bill->setZip( $billing['zip'] ?? '' );
            $subscription->setBillTo( $bill );
        }

        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setSubscription( $subscription );

        $controller = new AnetController\ARBCreateSubscriptionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            $id = $response->getSubscriptionId();
            return [ 'success' => true, 'subscription_id' => $id ];
        }

        $err = $response && $response->getMessages()->getMessage()
            ? $response->getMessages()->getMessage()[0]->getText()
            : 'API error';
        return [ 'success' => false, 'error' => $err ];
    }

    /**
     * Cancel an existing subscription.
     *
     * @param string $subscription_id Subscription ID returned by Authorize.Net.
     * @return array { success:bool, error?:string }
     */
    public function cancel_subscription( $subscription_id ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $request = new AnetAPI\ARBCancelSubscriptionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setSubscriptionId( $subscription_id );

        $controller = new AnetController\ARBCancelSubscriptionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            return [ 'success' => true ];
        }

        $err = $response && $response->getMessages()->getMessage()
            ? $response->getMessages()->getMessage()[0]->getText()
            : 'API error';
        return [ 'success' => false, 'error' => $err ];
    }

    /**
     * Fetch subscription details such as the masked card number.
     *
     * @param string $subscription_id Subscription ID.
     * @return array { success:bool, card_last4?:string, error?:string }
     */
    public function get_subscription_details( $subscription_id ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $request = new AnetAPI\ARBGetSubscriptionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setSubscriptionId( $subscription_id );

        $controller = new AnetController\ARBGetSubscriptionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            $card  = $response->getSubscription()->getPayment()->getCreditCard();
            $masked = $card ? $card->getCardNumber() : '';
            $last4  = preg_match( '/(\d{4})$/', $masked, $m ) ? $m[1] : '';
            return [ 'success' => true, 'card_last4' => $last4 ];
        }

        $err = $response && $response->getMessages()->getMessage()
            ? $response->getMessages()->getMessage()[0]->getText()
            : 'API error';
        return [ 'success' => false, 'error' => $err ];
    }

    /**
     * Update the payment information for a subscription.
     *
     * @param string $subscription_id Subscription ID.
     * @param string $card_number      Credit card number.
     * @param string $exp_date         Expiration date YYYY-MM.
     * @param string $card_code        Card code/CVV.
     * @return array { success:bool, error?:string }
     */
    public function update_subscription_payment( $subscription_id, $card_number, $exp_date, $card_code ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $card = new AnetAPI\CreditCardType();
        $card->setCardNumber( $card_number );
        $card->setExpirationDate( $exp_date );
        $card->setCardCode( $card_code );

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard( $card );

        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setPayment( $payment );

        $request = new AnetAPI\ARBUpdateSubscriptionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setSubscriptionId( $subscription_id );
        $request->setSubscription( $subscription );

        $controller = new AnetController\ARBUpdateSubscriptionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            return [ 'success' => true ];
        }

        $err = $response && $response->getMessages()->getMessage()
            ? $response->getMessages()->getMessage()[0]->getText()
            : 'API error';
        return [ 'success' => false, 'error' => $err ];
    }
}
