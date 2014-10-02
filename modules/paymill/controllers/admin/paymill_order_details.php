<?php

class paymill_order_details extends oxAdminDetails
{
    
    /**
     * Render the yapital order detail template
     *
     * @return string
     */
    public function render()
    {
        parent::render();
        if ($this->_getPaymentSid() === 'paymill_cc' || $this->_getPaymentSid() === 'paymill_elv' ) {
            return 'paymill_order_details.tpl';
        }

        return 'paymill_order_no_details.tpl';
    }
    
    /**
     * Is refund possible
     * 
     * @return boolean
     */
    public function canRefund()
    {
        $transaction = oxNew('paymill_transaction');
        $transaction->load($this->getEditObjectId());
        
        return $this->getEditObject()->getTotalOrderSum() > 0 &&  !is_null($transaction->paymill_transaction__transaction_id->rawValue);
    }
    
    /**
     * Get the maximal possible refund amount
     *
     * @return float
     */
    private function _getRefundAmount()
    {
        return $this->getEditObject()->getTotalOrderSum();
    }
    
    /**
     * Refund the selected paymill transaction
     */
    public function refundTransaction()
    {
        $oxOrder = $this->getEditObject();
        
        $transaction = oxNew('paymill_transaction');
        $transaction->load($this->getEditObjectId());
        
        //Create Refund
        $params = array(
            'transactionId' => $transaction->transaction_id->rawValue,
            'params' => array('amount' => $this->_getRefundAmount())
        );

        $refundsObject = new Services_Paymill_Refunds(                
            trim(oxRegistry::getConfig()->getShopConfVar('PAYMILL_PRIVATEKEY')),
            paymill_util::API_ENDPOINT
        );
        
        try {
            $refund = $refundsObject->create($params);
        } catch (Exception $ex) {
            
        }

        if (isset($refund['response_code']) && $refund['response_code'] == 20000) {
            $oxOrder->assign(array('oxorder__oxdiscount' => $this->_getRefundAmount()));
            $oxOrder->reloadDiscount(false);
            $oxOrder->recalculateOrder();
            oxRegistry::getSession()->setVariable('success', true);
        } else {
            oxRegistry::getSession()->setVariable('error', true);
        }
    }
    
    /**
     * Return error flag
     * 
     * @return boolean
     */
    public function hasError()
    {
        $flag = false;
        if (oxRegistry::getSession()->hasVariable('error') && oxRegistry::getSession()->getVariable('error')) {
            $flag = true;
            oxRegistry::getSession()->deleteVariable('error');
        }
        
        return $flag;
    }
    
    /**
     * Return error flag
     * 
     * @return boolean
     */
    public function hasSuccess()
    {
        $flag = false;
        if (oxRegistry::getSession()->hasVariable('success') && oxRegistry::getSession()->getVariable('success')) {
            $flag = true;
            oxRegistry::getSession()->deleteVariable('success');
        }
        
        return $flag;
    }

    /**
     * Return payment id
     *
     * @return string
     */
    protected function _getPaymentSid()
    {
        if (is_null($this->_paymentSid)) {
            $order = $this->getEditObject();
            $this->_paymentSid = $this->_getPaymentType($order);
        }

        return $this->_paymentSid;
    }

    /**
     * Return payment type of give order
     *
     * @param oxOrder $order
     * @return string
     */
    protected function _getPaymentType($order)
    {
        $data = false;
        if (isset($order)) {
            $data = $order->getPaymentType()->oxuserpayments__oxpaymentsid->value;
        }

        return $data;
    }


    /**
     * Returns editable order object
     * 
     * @return oxorder|null
     */
    public function getEditObject()
    {
        $orderId = $this->getEditObjectId();

        if (is_null($this->_oEditObject) && isset($orderId) && $orderId != "-1") {
            $this->_oEditObject = oxNew("oxorder");
            $this->_oEditObject->load($orderId);
        }

        return $this->_oEditObject;
    }
}
