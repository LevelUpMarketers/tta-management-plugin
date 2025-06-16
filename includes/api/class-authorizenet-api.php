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
}
