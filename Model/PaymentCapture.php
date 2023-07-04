<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Payment\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class PaymentCapture implements \AlbertMage\Payment\Api\PaymentCaptureInterface
{
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Order model object
     *
     * @var Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $paymentGateway;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $paymentRawData;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
    )
    {
        $this->eventManager = $eventManager;
        $this->messageManager = $messageManager;
    }

    /**
     * Declare order model object
     *
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Retrieve order model object
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }


    /**
     * Declare payment gateway
     *
     * @param string $paymentGateway
     * @return $this
     */
    public function setPaymenGateway($paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;

        return $this;
    }

    /**
     * Retrieve payment gateway
     *
     * @return string
     */
    public function getPaymenGateway()
    {
        return $this->paymentGateway;
    }

    /**
     * Declare TransactionId
     *
     * @param string $TransactionId
     * @return $this
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Retrieve TransactionId
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Declare PaymentRawData
     *
     * @param array $PaymentRawData
     * @return $this
     */
    public function setPaymentRawData($paymentRawData)
    {
        $this->paymentRawData = $paymentRawData;

        return $this;
    }

    /**
     * Retrieve PaymentRawData
     *
     * @return array
     */
    public function getPaymentRawData()
    {
        return $this->paymentRawData;
    }

    public function capture()
    {
        $this->addTransactionToOrder();

        $this->generateInvoice();

        $this->getOrder()->addStatusHistoryComment('Automatically INVOICED')->setIsCustomerNotified(true);

        //$this->getOrder()->setState(Order::STATE_PAYMENT_REVIEW)->setStatus(Order::STATE_PAYMENT_REVIEW);

        $this->getOrder()->save();

        $this->eventManager->dispatch('sales_order_payment_catpure', ['order' => $this->getOrder()]);

        return $this;
    }

    /**
     * Create Invoice Based on Order Object
     * @return $this
     */
    public function generateInvoice()
    {
        $order = $this->getOrder();

        try {
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }
            if(!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                        __('The order does not allow an invoice to be created.')
                    );
            }

            $invoice = $order->prepareInvoice();
            if (!$invoice) {
                throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t save the invoice right now.'));
            }
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            $order->addRelatedObject($invoice);

        } catch (\Exception $e) {
            
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $invoice;
    }

    /**
     * ad Transaction to order
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function addTransactionToOrder() {
        // Prepare payment object
        $payment = $this->getOrder()->getPayment();
        $payment->setMethod($this->getPaymenGateway()); 
        $payment->setLastTransId($this->getTransactionId());
        $payment->setTransactionId($this->getTransactionId());
        $payment->setAdditionalInformation([Transaction::RAW_DETAILS => $this->getPaymentRawData()]);

        // Prepare transaction
        $transaction = $payment->addTransaction(Transaction::TYPE_CAPTURE, null, true);
    }
}