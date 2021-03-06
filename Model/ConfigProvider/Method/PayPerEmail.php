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

use TIG\Buckaroo\Model\Method\PayPerEmail as MethodPayPerEmail;

/**
 * @method getCm3DueDate()
 * @method getMaxStepIndex()
 * @method getPaymentMethod()
 * @method getPaymentMethodAfterExpiry()
 * @method getSchemeKey()
 * @method getActiveStatusCm3()
 */
class PayPerEmail extends AbstractConfigProvider
{
    const XPATH_ALLOWED_CURRENCIES               = 'buckaroo/tig_buckaroo_payperemail/allowed_currencies';

    const XPATH_ALLOW_SPECIFIC                   = 'payment/tig_buckaroo_payperemail/allowspecific';
    const XPATH_SPECIFIC_COUNTRY                 = 'payment/tig_buckaroo_payperemail/specificcountry';

    const XPATH_PAYPEREMAIL_ACTIVE               = 'payment/tig_buckaroo_payperemail/active';
    const XPATH_PAYPEREMAIL_PAYMENT_FEE          = 'payment/tig_buckaroo_payperemail/payment_fee';
    const XPATH_PAYPEREMAIL_PAYMENT_FEE_LABEL    = 'payment/tig_buckaroo_payperemail/payment_fee_label';
    const XPATH_PAYPEREMAIL_ACTIVE_STATUS        = 'payment/tig_buckaroo_payperemail/active_status';
    const XPATH_PAYPEREMAIL_ORDER_STATUS_SUCCESS = 'payment/tig_buckaroo_payperemail/order_status_success';
    const XPATH_PAYPEREMAIL_ORDER_STATUS_FAILED  = 'payment/tig_buckaroo_payperemail/order_status_failed';

    const XPATH_PAYPEREMAIL_ACTIVE_STATUS_CM3           = 'payment/tig_buckaroo_payperemail/active_status_cm3';
    const XPATH_PAYPEREMAIL_SEND_MAIL                   = 'payment/tig_buckaroo_payperemail/send_mail';
    const XPATH_PAYPEREMAIL_SCHEME_KEY                  = 'payment/tig_buckaroo_payperemail/scheme_key';
    const XPATH_PAYPEREMAIL_MAX_STEP_INDEX              = 'payment/tig_buckaroo_payperemail/max_step_index';
    const XPATH_PAYPEREMAIL_CM3_DUE_DATE                = 'payment/tig_buckaroo_payperemail/cm3_due_date';
    const XPATH_PAYPEREMAIL_PAYMENT_METHOD              = 'payment/tig_buckaroo_payperemail/payment_method';
    const XPATH_PAYPEREMAIL_PAYMENT_METHOD_AFTER_EXPIRY = 'payment/tig_buckaroo_payperemail/payment_method_after_expiry';
    const XPATH_PAYPEREMAIL_VISIBLE_FRONT_BACK          = 'payment/tig_buckaroo_payperemail/visible_front_back';
    const XPATH_PAYPEREMAIL_IS_VISIBLE_FOR_AREA_CODE    = 'payment/tig_buckaroo_payperemail/is_visible_for_area_code';

    /**
     * @return array
     */
    public function getConfig()
    {
        if (!$this->scopeConfig->getValue(self::XPATH_PAYPEREMAIL_ACTIVE)) {
            return [];
        }

        $paymentFeeLabel = $this->getBuckarooPaymentFeeLabel(MethodPayPerEmail::PAYMENT_METHOD_CODE);

        return [
            'payment' => [
                'buckaroo' => [
                    'payperemail' => [
                        'paymentFeeLabel'   => $paymentFeeLabel,
                        'allowedCurrencies' => $this->getAllowedCurrencies(),
                    ],
                    'response' => [],
                ],
            ],
        ];
    }

    /**
     * @return float
     */
    public function getPaymentFee()
    {
        $paymentFee = $this->scopeConfig->getValue(
            self::XPATH_PAYPEREMAIL_PAYMENT_FEE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $paymentFee ? $paymentFee : false;
    }

    /**
     * @return bool
     */
    public function getSendMail()
    {
        $sendMail = $this->scopeConfig->getValue(
            self::XPATH_PAYPEREMAIL_SEND_MAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $sendMail ? true : false;
    }

    /**
     * @param $areaCode
     * @return bool
     */
    public function isVisibleForAreaCode($areaCode)
    {
        if (null === $this->getVisibleFrontBack()) {
            return false;
        }

        $forFrontend = ('frontend' === $this->getVisibleFrontBack() || 'both' === $this->getVisibleFrontBack());
        $forBackend = ('backend' === $this->getVisibleFrontBack() || 'both' === $this->getVisibleFrontBack());

        if (($areaCode == 'adminhtml' && !$forBackend) || ($areaCode != 'adminhtml' && !$forFrontend)) {
            return false;
        }

        return true;
    }
}
