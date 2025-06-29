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

    /**
     * Log raw API responses to the PHP error log and debug log.
     *
     * @param string $context Context label describing the request.
     * @param mixed  $response Response object returned by the SDK.
     * @return void
     */
    protected function log_response( $context, $response ) {
        $msg = $context . ': ' . print_r( $response, true );
        error_log( '[TTA] ' . $msg );
        if ( class_exists( 'TTA_Debug_Logger' ) ) {
            TTA_Debug_Logger::log( $msg );
        }
    }

    /**
     * Format an error message from an API response.
     *
     * @param mixed $response  Response object returned by the Authorize.Net SDK.
     * @param mixed $tresponse Optional transaction response.
     * @param string $default  Fallback message.
     * @return string
     */
    protected function format_error( $response, $tresponse = null, $default = 'Transaction failed' ) {
        $code = '';
        $text = '';

        if ( $tresponse && method_exists( $tresponse, 'getErrors' ) && $tresponse->getErrors() ) {
            $err  = $tresponse->getErrors()[0];
            $code = method_exists( $err, 'getErrorCode' ) ? $err->getErrorCode() : '';
            $text = method_exists( $err, 'getErrorText' ) ? $err->getErrorText() : '';
        }

        if ( '' === $text && $tresponse && method_exists( $tresponse, 'getMessages' ) && $tresponse->getMessages() ) {
            $msg  = $tresponse->getMessages()[0];
            $code = method_exists( $msg, 'getCode' ) ? $msg->getCode() : '';
            $text = method_exists( $msg, 'getDescription' ) ? $msg->getDescription() : '';
        }

        if ( '' === $text && $response && method_exists( $response, 'getMessages' ) && $response->getMessages()->getMessage() ) {
            $m    = $response->getMessages()->getMessage()[0];
            $code = method_exists( $m, 'getCode' ) ? $m->getCode() : '';
            $text = method_exists( $m, 'getText' ) ? $m->getText() : '';
        }

        $text = trim( $text );
        if ( '' === $text ) {
            return $default;
        }
        $message = $code ? sprintf( '%s: %s', $code, $text ) : $text;
        $extra   = $this->error_help( $code );
        return $extra ? $message . ' (' . $extra . ')' : $message;
    }

    /**
     * Provide a human friendly explanation for common API error codes.
     *
     * @param string $code Error code returned by the API.
     * @return string
     */
    protected function error_help( $code ) {
        $map = [
            'E00001' => 'An unexpected error occurred. Please try again.',
            'E00002' => 'The login is invalid or the API account is inactive.',
            'E00003' => 'The referenced record was not found.',
            'E00007' => 'Credentials are invalid. Check your API Login ID and Transaction Key.',
            'E00027' => 'The transaction was declined by the processor or card issuer.',
        ];

        return $map[ $code ] ?? '';
    }

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
        $this->log_response( 'refund', $response );
        $this->log_response( 'charge', $response );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            $tresponse = $response->getTransactionResponse();
            if ( $tresponse && $tresponse->getResponseCode() === '1' ) {
                return [
                    'success'        => true,
                    'transaction_id' => $tresponse->getTransId(),
                ];
            }
            return [
                'success' => false,
                'error'   => $this->format_error( $response, $tresponse, 'Transaction failed' ),
            ];
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
    }

    /**
     * Issue a refund for a previous transaction.
     *
     * @param float  $amount        Refund amount.
     * @param string $transaction_id Original Authorize.Net transaction ID.
     * @param string $card_last4     Last four digits of the card.
     * @return array { success:bool, transaction_id?:string, error?:string }
     */
    public function refund( $amount, $transaction_id, $card_last4 ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $card = new AnetAPI\CreditCardType();
        $card->setCardNumber( $card_last4 );
        $card->setExpirationDate( 'XXXX' );

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard( $card );

        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType( 'refundTransaction' );
        $transactionRequest->setAmount( $amount );
        $transactionRequest->setPayment( $payment );
        $transactionRequest->setRefTransId( $transaction_id );

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setTransactionRequest( $transactionRequest );

        $controller = new AnetController\CreateTransactionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            $tresponse = $response->getTransactionResponse();
            if ( $tresponse && $tresponse->getResponseCode() === '1' ) {
                return [ 'success' => true, 'transaction_id' => $tresponse->getTransId() ];
            }
            return [
                'success' => false,
                'error'   => $this->format_error( $response, $tresponse, 'Refund failed' ),
            ];
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
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
        $start_date = date( 'Y-m-d' );
        if ( $this->environment === ANetEnvironment::SANDBOX ) {
            $start_date = date( 'Y-m-d', strtotime( '-1 day' ) );
        }
        $schedule->setStartDate( new DateTime( $start_date ) );
        $schedule->setTotalOccurrences( 9999 );

        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName( $name );
        if ( $description ) {
            $order = new AnetAPI\OrderType();
            $order->setDescription( $description );
            $subscription->setOrder( $order );
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
        $this->log_response( 'create_subscription', $response );

        $result_code  = '';
        $message_code = '';
        $message_text = '';
        if ( $response && $response->getMessages() ) {
            $result_code = $response->getMessages()->getResultCode();
            $msg         = $response->getMessages()->getMessage();
            if ( $msg ) {
                $message_code = $msg[0]->getCode();
                $message_text = $msg[0]->getText();
            }
        }

        if ( $response && 'Ok' === $result_code ) {
            $id = $response->getSubscriptionId();
            return [
                'success'        => true,
                'subscription_id' => $id,
                'result_code'    => $result_code,
                'message_code'   => $message_code,
                'message_text'   => $message_text,
            ];
        }

        return [
            'success'      => false,
            'error'        => $this->format_error( $response, null, 'API error' ),
            'result_code'  => $result_code,
            'message_code' => $message_code,
            'message_text' => $message_text,
        ];
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
        $this->log_response( 'cancel_subscription', $response );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            return [ 'success' => true ];
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
    }

    /**
     * Fetch subscription details such as the masked card number.
     *
     * @param string  $subscription_id     Subscription ID.
     * @param boolean $include_transactions Whether to return the ARB transaction list.
     * @return array { success:bool, card_last4?:string, transactions?:array, error?:string }
     */
    public function get_subscription_details( $subscription_id, $include_transactions = false ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $request = new AnetAPI\ARBGetSubscriptionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setSubscriptionId( $subscription_id );
        if ( $include_transactions ) {
            $request->setIncludeTransactions( true );
        }

        $controller = new AnetController\ARBGetSubscriptionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );
        $this->log_response( 'get_subscription_details', $response );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            $sub    = $response->getSubscription();
            $card   = $sub->getPayment()->getCreditCard();
            $masked = $card ? $card->getCardNumber() : '';
            $last4  = preg_match( '/(\d{4})$/', $masked, $m ) ? $m[1] : '';

            $data = [ 'success' => true, 'card_last4' => $last4 ];

            if ( $include_transactions ) {
                $amount   = $sub->getAmount();
                $txn_list = [];
                $txns     = $sub->getArbTransactions();
                if ( $txns ) {
                    foreach ( $txns->getArbTransaction() as $tx ) {
                        $txn_list[] = [
                            'id'    => $tx->getTransId(),
                            'date'  => $tx->getSubmitTimeUTC(),
                            'amount'=> $amount,
                        ];
                    }
                }
                $data['transactions'] = $txn_list;
            }

            return $data;
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
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
    public function update_subscription_payment( $subscription_id, $card_number, $exp_date, $card_code, array $billing = [] ) {
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

        $request = new AnetAPI\ARBUpdateSubscriptionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setSubscriptionId( $subscription_id );
        $request->setSubscription( $subscription );

        $controller = new AnetController\ARBUpdateSubscriptionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );
        $this->log_response( 'update_subscription_amount', $response );
        $this->log_response( 'update_subscription_payment', $response );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            return [ 'success' => true ];
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
    }

    /**
     * Update the monthly amount for an existing subscription.
     *
     * @param string $subscription_id Subscription ID.
     * @param float  $amount          New monthly amount.
     * @return array { success:bool, error?:string }
     */
    public function update_subscription_amount( $subscription_id, $amount ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setAmount( $amount );

        $request = new AnetAPI\ARBUpdateSubscriptionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setSubscriptionId( $subscription_id );
        $request->setSubscription( $subscription );

        $controller = new AnetController\ARBUpdateSubscriptionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            return [ 'success' => true ];
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
    }
}
