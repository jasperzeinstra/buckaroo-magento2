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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright Copyright (c) 2016 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Buckaroo\Model\ConfigProvider\Method;

class Giftcards extends AbstractConfigProvider
{
    const XPATH_GIFTCARDS_PAYMENT_FEE           = 'payment/tig_buckaroo_giftcards/payment_fee';
    const XPATH_GIFTCARDS_PAYMENT_FEE_LABEL     = 'payment/tig_buckaroo_giftcards/payment_fee_label';
    const XPATH_GIFTCARDS_ACTIVE                = 'payment/tig_buckaroo_giftcards/active';
    const XPATH_GIFTCARDS_ACTIVE_STATUS         = 'payment/tig_buckaroo_giftcards/active_status';
    const XPATH_GIFTCARDS_ORDER_STATUS_SUCCESS  = 'payment/tig_buckaroo_giftcards/order_status_success';
    const XPATH_GIFTCARDS_ORDER_STATUS_FAILED   = 'payment/tig_buckaroo_giftcards/order_status_failed';
    const XPATH_GIFTCARDS_ORDER_EMAIL           = 'payment/tig_buckaroo_giftcards/order_email';
    const XPATH_GIFTCARDS_AVAILABLE_IN_BACKEND  = 'payment/tig_buckaroo_giftcards/available_in_backend';
    const XPATH_GIFTCARDS_ALLOWED_GIFTCARDS     = 'payment/tig_buckaroo_giftcards/allowed_giftcards';

    const XPATH_ALLOWED_CURRENCIES = 'payment/tig_buckaroo_giftcards/allowed_currencies';

    const XPATH_ALLOW_SPECIFIC                  = 'payment/tig_buckaroo_giftcards/allowspecific';
    const XPATH_SPECIFIC_COUNTRY                = 'payment/tig_buckaroo_giftcards/specificcountry';

    /**
     * @var array
     */
    protected $allowedCurrencies = [
        'EUR'
    ];

    /**
     * @return array
     */
    public function getConfig()
    {
        if (!$this->scopeConfig->getValue(static::XPATH_GIFTCARDS_ACTIVE)) {
            return [];
        }

        $paymentFeeLabel = $this->getBuckarooPaymentFeeLabel(\TIG\Buckaroo\Model\Method\Giftcards::PAYMENT_METHOD_CODE);

        return [
            'payment' => [
                'buckaroo' => [
                    'giftcards' => [
                        'paymentFeeLabel' => $paymentFeeLabel,
                        'allowedCurrencies' => $this->getAllowedCurrencies(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return bool|float
     */
    public function getPaymentFee()
    {
        $paymentFee = $this->scopeConfig->getValue(
            self::XPATH_GIFTCARDS_PAYMENT_FEE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $paymentFee ? $paymentFee : false;
    }
}
