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
     * Log raw API responses to the internal debug log.
     *
     * @param string $context  Context label describing the request.
     * @param mixed  $response Response object returned by the SDK.
     * @return void
     */
    protected function log_response( $context, $response ) {
        if ( ! $response ) {
            TTA_Debug_Logger::log( $context . ': [no response]' );
            return;
        }

        $result   = '';
        $msg_code = '';
        $msg_text = '';

        if ( method_exists( $response, 'getMessages' ) && $response->getMessages() ) {
            $result = $response->getMessages()->getResultCode();
            $msgs   = $response->getMessages()->getMessage();
            if ( $msgs ) {
                $first    = $msgs[0];
                $msg_code = method_exists( $first, 'getCode' ) ? $first->getCode() : '';
                $msg_text = method_exists( $first, 'getText' ) ? $first->getText() : '';
            }
        }

        $summary = trim( $result . ' ' . $msg_code . ' ' . $msg_text );
        TTA_Debug_Logger::log( $context . ': ' . ( $summary ?: '[no details]' ) );
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
            if ( defined( 'TTA_AUTHNET_SANDBOX' ) ) {
                $sandbox = TTA_AUTHNET_SANDBOX;
            } else {
                $sandbox = (bool) get_option( 'tta_authnet_sandbox', false );
            }
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
     * Retry the most recent charge for a subscription using its stored profile.
     *
     * @param string $subscription_id Subscription ID.
     * @return array { success:bool, transaction_id?:string, error?:string }
     */
    public function retry_subscription_charge( $subscription_id ) {
        $details = $this->get_subscription_details( $subscription_id );
        if ( ! $details['success'] ) {
            return [ 'success' => false, 'error' => $details['error'] ?? 'Unknown error' ];
        }
        $profile_id = $details['profile_id'] ?? '';
        $payment_profile_id = $details['payment_profile_id'] ?? '';
        $amount = $details['amount'] ?? 0;
        if ( ! $profile_id || ! $payment_profile_id || ! $amount ) {
            return [ 'success' => false, 'error' => 'Missing profile information' ];
        }

        return $this->charge_profile( $profile_id, $payment_profile_id, $amount );
    }

    /**
     * Charge an existing customer profile/payment profile.
     *
     * @param string $profile_id         Customer profile ID.
     * @param string $payment_profile_id Payment profile ID.
     * @param float  $amount             Amount to charge.
     * @return array { success:bool, transaction_id?:string, error?:string }
     */
    public function charge_profile( $profile_id, $payment_profile_id, $amount ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $profileToCharge = new AnetAPI\CustomerProfilePaymentType();
        $profileToCharge->setCustomerProfileId( $profile_id );
        $payProf = new AnetAPI\PaymentProfileType();
        $payProf->setPaymentProfileId( $payment_profile_id );
        $profileToCharge->setPaymentProfile( $payProf );

        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType( 'authCaptureTransaction' );
        $transactionRequest->setAmount( $amount );
        $transactionRequest->setProfile( $profileToCharge );

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setTransactionRequest( $transactionRequest );

        $controller = new AnetController\CreateTransactionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );
        $this->log_response( 'charge_profile', $response );

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
     * Void a previous transaction that has not yet settled.
     *
     * @param string $transaction_id Original Authorize.Net transaction ID.
     * @return array { success:bool, transaction_id?:string, error?:string }
     */
    public function void( $transaction_id ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $transactionRequest = new AnetAPI\TransactionRequestType();
        $transactionRequest->setTransactionType( 'voidTransaction' );
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
                'error'   => $this->format_error( $response, $tresponse, 'Void failed' ),
            ];
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
    }

    /**
     * Retrieve the current status for a transaction.
     *
     * @param string $transaction_id Authorize.Net transaction ID.
     * @return array { success:bool, status?:string, error?:string }
     */
    public function get_transaction_status( $transaction_id ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $auth = new AnetAPI\MerchantAuthenticationType();
        $auth->setName( $this->login_id );
        $auth->setTransactionKey( $this->transaction_key );

        $request = new AnetAPI\GetTransactionDetailsRequest();
        $request->setMerchantAuthentication( $auth );
        $request->setTransId( $transaction_id );

        $controller = new AnetController\GetTransactionDetailsController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            $txn = $response->getTransaction();
            if ( $txn && method_exists( $txn, 'getTransactionStatus' ) ) {
                return [
                    'success' => true,
                    'status'  => $txn->getTransactionStatus(),
                ];
            }
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
    }

    /**
     * Retrieve detailed information for a transaction.
     *
     * @param string $transaction_id Authorize.Net transaction ID.
     * @return array { success:bool, amount?:float, email?:string, error?:string }
     */
    public function get_transaction_details( $transaction_id ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $auth = new AnetAPI\MerchantAuthenticationType();
        $auth->setName( $this->login_id );
        $auth->setTransactionKey( $this->transaction_key );

        $request = new AnetAPI\GetTransactionDetailsRequest();
        $request->setMerchantAuthentication( $auth );
        $request->setTransId( $transaction_id );

        $controller = new AnetController\GetTransactionDetailsController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );
        $this->log_response( 'get_transaction_details', $response );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            $txn   = $response->getTransaction();
            $bill  = $txn ? $txn->getBillTo() : null;
            $email = '';
            if ( $bill && method_exists( $bill, 'getEmail' ) ) {
                $email = strtolower( trim( (string) $bill->getEmail() ) );
            }
            if ( '' === $email && $txn && $txn->getCustomer() && method_exists( $txn->getCustomer(), 'getEmail' ) ) {
                $email = strtolower( trim( (string) $txn->getCustomer()->getEmail() ) );
            }

            return [
                'success' => true,
                'amount'  => $txn ? $txn->getSettleAmount() : 0,
                'email'   => $email,
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
    public function create_subscription( $amount, $card_number, $exp_date, $card_code, array $billing = [], $name = 'Membership Subscription', $description = '', $start_date = null ) {
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
        if ( null === $start_date ) {
            $start_date = date( 'Y-m-d' );
            if ( $this->environment === ANetEnvironment::SANDBOX ) {
                $start_date = date( 'Y-m-d', strtotime( '-1 day' ) );
            }
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
            $addr = trim( ( $billing['address'] ?? '' ) . ( empty( $billing['address2'] ) ? '' : ' ' . $billing['address2'] ) );
            $bill->setAddress( $addr );
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
            $sub      = $response->getSubscription();
            $status   = $sub && method_exists( $sub, 'getStatus' ) ? strtolower( $sub->getStatus() ) : '';
            $profile  = $sub ? $sub->getProfile() : null;
            $pay_prof = $profile ? $profile->getPaymentProfile() : null;
            $profile_id = $profile && method_exists( $profile, 'getCustomerProfileId' ) ? $profile->getCustomerProfileId() : '';
            $payment_profile_id = $profile && method_exists( $profile, 'getCustomerPaymentProfileId' ) ? $profile->getCustomerPaymentProfileId() : '';
            $payment  = $pay_prof ? $pay_prof->getPayment() : null;
            $card     = $payment ? $payment->getCreditCard() : null;
            $masked   = $card ? $card->getCardNumber() : '';
            $last4    = preg_match( '/(\d{4})$/', $masked, $m ) ? $m[1] : '';
            $exp      = $card && method_exists( $card, 'getExpirationDate' ) ? $card->getExpirationDate() : '';
            $amount   = $sub && method_exists( $sub, 'getAmount' ) ? floatval( $sub->getAmount() ) : 0.0;
            $bill     = $pay_prof && method_exists( $pay_prof, 'getBillTo' ) ? $pay_prof->getBillTo() : null;
            $billing  = [];
            if ( $bill ) {
                $billing = [
                    'first_name' => $bill->getFirstName(),
                    'last_name'  => $bill->getLastName(),
                    'address'    => $bill->getAddress(),
                    'city'       => $bill->getCity(),
                    'state'      => $bill->getState(),
                    'zip'        => $bill->getZip(),
                ];
            }

            $data = [
                'success'            => true,
                'card_last4'         => $last4,
                'status'             => $status,
                'amount'             => $amount,
                'exp_date'           => $exp,
                'billing'            => $billing,
                'profile_id'         => $profile_id,
                'payment_profile_id' => $payment_profile_id,
            ];

            if ( $include_transactions ) {
                $amount   = $sub->getAmount();
                $txn_list = [];
                $txns     = $sub->getArbTransactions();
                if ( $txns ) {
                    $tx_list = [];
                    if ( is_array( $txns ) ) {
                        $tx_list = $txns;
                    } elseif ( is_object( $txns ) && method_exists( $txns, 'getArbTransaction' ) ) {
                        $tx_list = $txns->getArbTransaction();
                    }
                    foreach ( $tx_list as $tx ) {
                        $ts = $tx->getSubmitTimeUTC();
                        if ( $ts instanceof \DateTime ) {
                            $ts = $ts->format( 'Y-m-d H:i:s' );
                        }
                        $txn_list[] = [
                            'id'    => $tx->getTransId(),
                            'date'  => $ts,
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
    public function update_subscription_payment( $subscription_id, $card_number = '', $exp_date = '', $card_code = '', array $billing = [] ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $subscription = new AnetAPI\ARBSubscriptionType();
        if ( $card_number && $exp_date ) {
            $card = new AnetAPI\CreditCardType();
            $card->setCardNumber( $card_number );
            $card->setExpirationDate( $exp_date );
            if ( $card_code ) {
                $card->setCardCode( $card_code );
            }
            $payment = new AnetAPI\PaymentType();
            $payment->setCreditCard( $card );
            $subscription->setPayment( $payment );
        }

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
     * Update the monthly amount and optional name for an existing subscription.
     *
     * @param string $subscription_id Subscription ID.
     * @param float  $amount          New monthly amount.
     * @param string $name            Optional new subscription name.
     * @return array { success:bool, error?:string }
     */
    public function update_subscription_amount( $subscription_id, $amount, $name = '' ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setAmount( $amount );
        if ( $name ) {
            $subscription->setName( $name );
        }

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

    /**
     * Find the most recent transaction for an email with a matching description.
     *
     * @param string $email       Customer email address.
     * @param string $description Description text to search within the order description.
     * @param int    $days_back   How many days of batches to search.
     * @return array|null { id:string, amount:float, date:string }
     */
    /**
     * Locate the most recent settled transaction for an email matching any invoice description.
     *
     * @param string   $email        Customer email address.
     * @param string[] $descriptions Array of invoice description fragments to search for.
     * @param int      $days_back    How many days to look back through settled transactions.
     * @return array|null            Transaction details or null if none found.
     */
    public function find_transaction_by_name_and_invoice_description( $first_name, $last_name, array $descriptions, $days_back = null ) {
        if ( null === $days_back ) {
            $days_back = defined( 'TTA_AUTHNET_IMPORT_LOOKBACK_DAYS' ) ? TTA_AUTHNET_IMPORT_LOOKBACK_DAYS : 93;
        }
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return null;
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $now  = new \DateTime();
        $from = ( clone $now )->modify( '-' . intval( $days_back ) . ' days' );

        $batch_request = new AnetAPI\GetSettledBatchListRequest();
        $batch_request->setMerchantAuthentication( $merchantAuthentication );
        $batch_request->setFirstSettlementDate( $from );
        $batch_request->setLastSettlementDate( $now );

        $batch_controller = new AnetController\GetSettledBatchListController( $batch_request );
        $batch_response   = $batch_controller->executeWithApiResponse( $this->environment );
        $this->log_response( 'get_settled_batch_list', $batch_response );

        if ( ! $batch_response || 'Ok' !== $batch_response->getMessages()->getResultCode() ) {
            return null;
        }

        $batches = $batch_response->getBatchList();
        if ( ! $batches ) {
            return null;
        }

        usort( $batches, function ( $a, $b ) {
            return $b->getSettlementTimeUTC()->getTimestamp() - $a->getSettlementTimeUTC()->getTimestamp();
        } );

        foreach ( $batches as $batch ) {
            $list_request = new AnetAPI\GetTransactionListRequest();
            $list_request->setMerchantAuthentication( $merchantAuthentication );
            $list_request->setBatchId( $batch->getBatchId() );

            $list_controller = new AnetController\GetTransactionListController( $list_request );
            $list_response   = $list_controller->executeWithApiResponse( $this->environment );
            $this->log_response( 'get_transaction_list', $list_response );

            if ( ! $list_response || 'Ok' !== $list_response->getMessages()->getResultCode() || ! $list_response->getTransactions() ) {
                continue;
            }

            $transactions = $list_response->getTransactions();
            usort( $transactions, function ( $a, $b ) {
                return $b->getSubmitTimeUTC()->getTimestamp() - $a->getSubmitTimeUTC()->getTimestamp();
            } );

            foreach ( $transactions as $summary ) {
                $detail_request = new AnetAPI\GetTransactionDetailsRequest();
                $detail_request->setMerchantAuthentication( $merchantAuthentication );
                $detail_request->setTransId( $summary->getTransId() );
                $detail_controller = new AnetController\GetTransactionDetailsController( $detail_request );
                $detail_response   = $detail_controller->executeWithApiResponse( $this->environment );
                $this->log_response( 'get_transaction_details', $detail_response );

                if ( ! $detail_response || 'Ok' !== $detail_response->getMessages()->getResultCode() ) {
                    continue;
                }

                $txn   = $detail_response->getTransaction();
                $txn   = $detail_response->getTransaction();
                $bill  = $txn->getBillTo();
                $order = $txn->getOrder();
                $desc  = '';

                if ( $order ) {
                    $desc = trim( $order->getDescription() . ' ' . $order->getInvoiceNumber() );
                }

                $first      = $bill ? strtolower( trim( $bill->getFirstName() ) ) : '';
                $last       = $bill ? strtolower( trim( $bill->getLastName() ) ) : '';
                $first_name = strtolower( trim( $first_name ) );
                $last_name  = strtolower( trim( $last_name ) );

                if ( false !== stripos( $first, $first_name ) && false !== stripos( $last, $last_name ) ) {
                    foreach ( $descriptions as $needle ) {
                        if ( false !== stripos( $desc, $needle ) ) {
                            return [
                                'id'      => $summary->getTransId(),
                                'amount'  => $txn->getSettleAmount(),
                                'date'    => $txn->getSubmitTimeUTC()->format( 'Y-m-d' ),
                                'details' => $desc,
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Retrieve settled transactions associated with a billing email.
     *
     * @param string $email     Billing email address.
     * @param int    $days_back How many days back to search.
     * @return array[] Array of transactions.
     */
    public function find_transactions_by_email( $email, $days_back = null, $limit = 20, $max_requests = 200 ) {
        $days_back = $days_back ?? ( defined( 'TTA_AUTHNET_IMPORT_LOOKBACK_DAYS' ) ? TTA_AUTHNET_IMPORT_LOOKBACK_DAYS : 93 );
        $matches   = [];
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return $matches;
        }

        $email = strtolower( trim( (string) $email ) );
        TTA_Debug_Logger::log( 'find_transactions_by_email lookup=' . ( $email ?: '[none]' ) );

        $merchant_auth = new AnetAPI\MerchantAuthenticationType();
        $merchant_auth->setName( $this->login_id );
        $merchant_auth->setTransactionKey( $this->transaction_key );

        $seen      = [];
        $end       = new \DateTime();
        $remaining = max( 1, intval( $days_back ) );
        $scanned   = 0;

        while ( $remaining > 0 && $scanned < $max_requests && count( $matches ) < $limit ) {
            $chunk = min( 31, $remaining );
            $from  = ( clone $end )->modify( '-' . $chunk . ' days' );

            $batch_request = new AnetAPI\GetSettledBatchListRequest();
            $batch_request->setMerchantAuthentication( $merchant_auth );
            $batch_request->setFirstSettlementDate( $from );
            $batch_request->setLastSettlementDate( $end );

            $batch_controller = new AnetController\GetSettledBatchListController( $batch_request );
            $batch_response   = $batch_controller->executeWithApiResponse( $this->environment );
            $this->log_response( 'get_settled_batch_list', $batch_response );

            if ( $batch_response && 'Ok' === $batch_response->getMessages()->getResultCode() && $batch_response->getBatchList() ) {
                foreach ( $batch_response->getBatchList() as $batch ) {
                    if ( $scanned >= $max_requests || count( $matches ) >= $limit ) {
                        break;
                    }

                    $list_request = new AnetAPI\GetTransactionListRequest();
                    $list_request->setMerchantAuthentication( $merchant_auth );
                    $list_request->setBatchId( $batch->getBatchId() );

                    $list_controller = new AnetController\GetTransactionListController( $list_request );
                    $list_response   = $list_controller->executeWithApiResponse( $this->environment );
                    $this->log_response( 'get_transaction_list', $list_response );

                    if ( ! $list_response || 'Ok' !== $list_response->getMessages()->getResultCode() || ! $list_response->getTransactions() ) {
                        continue;
                    }

                    foreach ( $list_response->getTransactions() as $summary ) {
                        if ( $scanned >= $max_requests || count( $matches ) >= $limit ) {
                            break;
                        }
                        if ( isset( $seen[ $summary->getTransId() ] ) ) {
                            continue;
                        }
                        $scanned++;

                        $detail_request = new AnetAPI\GetTransactionDetailsRequest();
                        $detail_request->setMerchantAuthentication( $merchant_auth );
                        $detail_request->setTransId( $summary->getTransId() );
                        $detail_controller = new AnetController\GetTransactionDetailsController( $detail_request );
                        $detail_response   = $detail_controller->executeWithApiResponse( $this->environment );
                        $this->log_response( 'get_transaction_details', $detail_response );

                        if ( ! $detail_response || 'Ok' !== $detail_response->getMessages()->getResultCode() ) {
                            continue;
                        }

                        $txn   = $detail_response->getTransaction();
                        $bill  = $txn->getBillTo();
                        $order = $txn->getOrder();

                        $bill_email = '';
                        if ( $bill && method_exists( $bill, 'getEmail' ) ) {
                            $bill_email = strtolower( trim( (string) $bill->getEmail() ) );
                        }
                        if ( '' === $bill_email && $txn->getCustomer() && method_exists( $txn->getCustomer(), 'getEmail' ) ) {
                            $bill_email = strtolower( trim( (string) $txn->getCustomer()->getEmail() ) );
                        }

                        if ( '' === $bill_email ) {
                            $ship_to    = method_exists( $txn, 'getShipTo' ) ? $txn->getShipTo() : null;
                            $customer   = $txn->getCustomer();
                            $profile_id = $customer && method_exists( $customer, 'getCustomerProfileId' ) ? $customer->getCustomerProfileId() : '';
                            $pay_prof   = $customer && method_exists( $customer, 'getCustomerPaymentProfileId' ) ? $customer->getCustomerPaymentProfileId() : '';
                            $ship_email = '';

                            if ( $ship_to && method_exists( $ship_to, 'getEmail' ) ) {
                                $ship_email = strtolower( trim( (string) $ship_to->getEmail() ) );
                                if ( $ship_email ) {
                                    $bill_email = $ship_email;
                                }
                            }

                            if ( '' === $bill_email ) {
                                TTA_Debug_Logger::log(
                                    sprintf(
                                        'find_transactions_by_email alt fields: ship_email=%s profile_id=%s payment_profile_id=%s',
                                        $ship_email ?: '[none]',
                                        $profile_id ?: '[none]',
                                        $pay_prof ?: '[none]'
                                    )
                                );
                            }
                        }

                        if ( $bill_email === $email ) {
                            TTA_Debug_Logger::log( 'find_transactions_by_email match=' . $bill_email );
                            $seen[ $summary->getTransId() ] = true;
                            $matches[]                      = [
                                'id'                 => $summary->getTransId(),
                                'amount'             => $txn->getSettleAmount(),
                                'date'               => $txn->getSubmitTimeUTC()->format( 'Y-m-d' ),
                                'transaction_status' => $txn->getTransactionStatus(),
                                'invoice'            => $order ? (string) $order->getInvoiceNumber() : '',
                                'details'            => $order ? (string) $order->getDescription() : '',
                            ];
                        }
                    }
                }
            }

            $end       = $from;
            $remaining -= $chunk;
        }

        return $matches;
    }

    /**
     * Create a subscription based on a previous transaction.
     *
     * @param string $transaction_id Original transaction ID.
     * @param float  $amount         Subscription amount.
     * @param string $name           Subscription name.
     * @param string $description    Order description.
     * @param string $start_date     YYYY-MM-DD start date.
     * @return array { success:bool, subscription_id?:string, error?:string }
     */
    public function create_subscription_from_transaction( $transaction_id, $amount, $name, $description = '', $start_date = null ) {
        if ( empty( $this->login_id ) || empty( $this->transaction_key ) ) {
            return [ 'success' => false, 'error' => 'Authorize.Net credentials not configured' ];
        }

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName( $this->login_id );
        $merchantAuthentication->setTransactionKey( $this->transaction_key );

        $profile_request = new AnetAPI\CreateCustomerProfileFromTransactionRequest();
        $profile_request->setMerchantAuthentication( $merchantAuthentication );
        $profile_request->setTransId( $transaction_id );

        $profile_controller = new AnetController\CreateCustomerProfileFromTransactionController( $profile_request );
        $profile_response   = $profile_controller->executeWithApiResponse( $this->environment );
        $this->log_response( 'create_customer_profile_from_transaction', $profile_response );

        if ( ! $profile_response || 'Ok' !== $profile_response->getMessages()->getResultCode() ) {
            return [ 'success' => false, 'error' => $this->format_error( $profile_response, null, 'Profile creation failed' ) ];
        }

        $customer_profile_id = $profile_response->getCustomerProfileId();
        $payment_profiles    = $profile_response->getCustomerPaymentProfileIdList();
        $payment_profile_id  = $payment_profiles ? $payment_profiles[0] : null;
        if ( ! $payment_profile_id ) {
            return [ 'success' => false, 'error' => 'Payment profile missing' ];
        }

        $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
        $interval->setLength( 1 );
        $interval->setUnit( 'months' );

        $schedule = new AnetAPI\PaymentScheduleType();
        $schedule->setInterval( $interval );
        $schedule->setStartDate( $start_date ? new \DateTime( $start_date ) : new \DateTime( 'now' ) );
        $schedule->setTotalOccurrences( 9999 );

        $order = new AnetAPI\OrderType();
        if ( $description ) {
            $order->setDescription( $description );
        }

        $profile = new AnetAPI\CustomerProfileIdType();
        $profile->setCustomerProfileId( $customer_profile_id );
        $profile->setCustomerPaymentProfileId( $payment_profile_id );

        $subscription = new AnetAPI\ARBSubscriptionType();
        $subscription->setName( $name );
        $subscription->setOrder( $order );
        $subscription->setPaymentSchedule( $schedule );
        $subscription->setAmount( $amount );
        $subscription->setProfile( $profile );

        $request = new AnetAPI\ARBCreateSubscriptionRequest();
        $request->setMerchantAuthentication( $merchantAuthentication );
        $request->setSubscription( $subscription );

        $controller = new AnetController\ARBCreateSubscriptionController( $request );
        $response   = $controller->executeWithApiResponse( $this->environment );
        $this->log_response( 'create_subscription_from_transaction', $response );

        if ( $response && 'Ok' === $response->getMessages()->getResultCode() ) {
            return [
                'success'         => true,
                'subscription_id' => $response->getSubscriptionId(),
            ];
        }

        return [
            'success' => false,
            'error'   => $this->format_error( $response, null, 'API error' ),
        ];
    }
}
