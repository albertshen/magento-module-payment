<?php 
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Payment\Api;
 
/**
 * @author Albert Shen <albertshen1206@gmail.com>
 */
interface PaymentCaptureInterface {

    /**
     * Declare order model object
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order);

    /**
     * Retrieve order model object
     *
     * @return Order
     */
    public function getOrder();

    /**
     * Declare payment gateway
     *
     * @param string $paymentGateway
     * @return $this
     */
    public function setPaymenGateway($paymentGateway);

    /**
     * Retrieve payment gateway
     *
     * @return string
     */
    public function getPaymenGateway();

    /**
     * Declare TransactionId
     *
     * @param string $TransactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Retrieve TransactionId
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * Declare PaymentRawData
     *
     * @param array $PaymentRawData
     * @return $this
     */
    public function setPaymentRawData($paymentRawData);

    /**
     * Retrieve PaymentRawData
     *
     * @return array
     */
    public function getPaymentRawData();

    /**
     *
     * @return $this
     */
    public function capture();


}