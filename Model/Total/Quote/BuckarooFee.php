<?php
/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) 2015 Total Internet Group B.V. (http://www.tig.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Model\Total\Quote;

class BuckarooFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Factory
     */
    protected $configProviderFactory;

    /**
     * @var \TIG\Buckaroo\Model\ConfigProvider\Method\Factory
     */
    protected $configProviderMethodFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    public $priceCurrency;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    public $catalogHelper;

    /**
     * @param \TIG\Buckaroo\Model\ConfigProvider\Factory        $configProviderFactory
     * @param \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderMethodFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Catalog\Helper\Data                      $catalogHelper
     */
    public function __construct(
        \TIG\Buckaroo\Model\ConfigProvider\Factory $configProviderFactory,
        \TIG\Buckaroo\Model\ConfigProvider\Method\Factory $configProviderMethodFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Helper\Data $catalogHelper
    ) {
        $this->setCode('buckaroo_fee');

        $this->configProviderFactory = $configProviderFactory;
        $this->configProviderMethodFactory = $configProviderMethodFactory;
        $this->priceCurrency = $priceCurrency;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     *
     * @throws \LogicException
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        /** @noinspection PhpUndefinedMethodInspection */
        $total->setBuckarooFee(0);
        /** @noinspection PhpUndefinedMethodInspection */
        $total->setBaseBuckarooFee(0);

        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $paymentMethod = $quote->getPayment()->getMethod();
        if (!$paymentMethod || strpos($paymentMethod, 'tig_buckaroo_') !== 0) {
            return $this;
        }

        $methodInstance = $quote->getPayment()->getMethodInstance();
        if (!$methodInstance instanceof \TIG\Buckaroo\Model\Method\AbstractMethod) {
            return $this;
        }

        $basePaymentFee = $this->getBaseFee($methodInstance, $quote);

        if ($basePaymentFee < 0.01) {
            return $this;
        }

        $paymentFee = $this->priceCurrency->convert($basePaymentFee, $quote->getStore());

        /** @noinspection PhpUndefinedMethodInspection */
        $quote->setBuckarooFee($paymentFee);
        /** @noinspection PhpUndefinedMethodInspection */
        $quote->setBaseBuckarooFee($basePaymentFee);

        /** @noinspection PhpUndefinedMethodInspection */
        $total->setBuckarooFee($paymentFee);
        /** @noinspection PhpUndefinedMethodInspection */
        $total->setBaseBuckarooFee($basePaymentFee);

        /** @noinspection PhpUndefinedMethodInspection */
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $basePaymentFee);
        /** @noinspection PhpUndefinedMethodInspection */
        $total->setGrandTotal($total->getGrandTotal() + $paymentFee);

        return $this;
    }

    /**
     * Add buckaroo fee information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $totals = [
            'code' => $this->getCode(),
            'title' => $this->getLabel(),
            'buckaroo_fee' => $total->getBuckarooFee(),
            'base_buckaroo_fee' => $total->getBaseBuckarooFee(),
            'buckaroo_fee_incl_tax' => $total->getBuckarooFeeInclTax(),
            'base_buckaroo_fee_incl_tax' => $total->getBaseBuckarooFeeInclTax(),
            'buckaroo_fee_tax_amount' => $total->getBuckarooFeeTaxAmount(),
            'buckaroo_fee_base_tax_amount' => $total->getBuckarooFeeBaseTaxAmount(),
        ];

        return $totals;
    }

    /**
     * @param \TIG\Buckaroo\Model\Method\AbstractMethod $methodInstance
     * @param \Magento\Quote\Model\Quote                $quote
     *
     * @return bool|false|float
     * @throws \TIG\Buckaroo\Exception
     */
    public function getBaseFee(
        \TIG\Buckaroo\Model\Method\AbstractMethod $methodInstance,
        \Magento\Quote\Model\Quote $quote
    ) {
        $buckarooPaymentMethodCode = $methodInstance->buckarooPaymentMethodCode;
        if (!$this->configProviderMethodFactory->has($buckarooPaymentMethodCode)) {
            return false;
        }

        $configProvider = $this->configProviderMethodFactory->get($buckarooPaymentMethodCode);
        $basePaymentFee = trim($configProvider->getPaymentFee());

        if (is_numeric($basePaymentFee)) {
            /** Payment fee is a number */
            return $this->getFeePrice($basePaymentFee);
        } elseif (strpos($basePaymentFee, '%') === false) {
            /** Payment fee is invalid */
            return false;
        }

        /** Payment fee is a percentage */
        $percentage = floatval($basePaymentFee);
        if ($quote->getShippingAddress()) {
            $address = $quote->getShippingAddress();
        } else {
            $address = $quote->getBillingAddress();
        }

        $total = 0;
        /** @noinspection PhpUndefinedMethodInspection */
        $feePercentageMode = $this->configProviderFactory->get('account')->getFeePercentageMode();
        switch ($feePercentageMode) {
            case 'subtotal':
                $total = $address->getBaseSubtotal();
                break;
            case 'subtotal_incl_tax':
                $total = $address->getBaseSubtotalTotalInclTax();
                break;
        }

        $basePaymentFee = ($percentage / 100) * $total;

        return $basePaymentFee;
    }

    /**
     * Get payment fee price with correct tax
     *
     * @param  float                             $price
     * @param null                               $priceIncl
     *
     * @param \Magento\Framework\DataObject|null $pseudoProduct
     *
     * @return float
     * @throws \TIG\Buckaroo\Exception
     */
    public function getFeePrice($price, $priceIncl = null, \Magento\Framework\DataObject $pseudoProduct = null)
    {
        if (is_null($pseudoProduct)) {
            $pseudoProduct = new \Magento\Framework\DataObject();
        }

        $feeConfig = $this->configProviderFactory->get('buckaroo_fee');
        /** @noinspection PhpUndefinedMethodInspection */
        $pseudoProduct->setTaxClassId($feeConfig->getTaxClass());

        /** @noinspection PhpUndefinedMethodInspection */
        if (is_null($priceIncl)
            && $feeConfig->getPaymentFeeTax()
                == \TIG\Buckaroo\Model\Config\Source\TaxClass\Calculation::DISPLAY_TYPE_INCLUDING_TAX
        ) {
            $priceIncl = true;
        } else {
            $priceIncl = false;
        }

        $price = $this->catalogHelper->getTaxPrice(
            $pseudoProduct,
            $price,
            false,
            null,
            null,
            null,
            null,
            $priceIncl
        );

        return $price;
    }

    /**
     * Get Buckaroo label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Payment Fee');
    }
}
