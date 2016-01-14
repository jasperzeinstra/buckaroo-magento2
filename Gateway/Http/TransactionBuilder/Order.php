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

namespace TIG\Buckaroo\Gateway\Http\TransactionBuilder;

class Order extends AbstractTransactionBuilder
{
    /**
     * @return array
     */
    public function getBody()
    {
        $order = $this->getOrder();

        /** @var \TIG\Buckaroo\Model\ConfigProvider\Account $accountConfig */
        $accountConfig = $this->configProviderFactory->get('account');

        $ip = $order->getRemoteIp();
        if (!$ip) {
            $ip = $_SERVER['SERVER_ADDR'];
        }

        // By default test mode is off
        $testMode = 0;
        // First check the extension's own setting, which is overruling if set to test
        if ($accountConfig->getActive() == "1") {
            $testMode = 1;
            \Log::add('Buckaroo is set to test mode: ' . $accountConfig->getActive());
        } else {
            // The extension itself isn't set to test, so get the method
            \Log::add('Buckaroo is not set to test mode: ' . $accountConfig->getActive());
        }

        $body = [
            'test' => $testMode,
            'Currency' => $order->getOrderCurrencyCode(),
            'AmountDebit' => $order->getBaseGrandTotal(),
            'AmountCredit' => 0,
            'Invoice' => $order->getIncrementId(),
            'Order' => $order->getIncrementId(),
            'Description' => $accountConfig->getTransactionLabel(),
            'ClientIP' => [
                '_' => $ip,
                'Type' => strpos($ip, ':') === false ? 'IPv4' : 'IPv6',
            ],
            'ReturnURL' => $this->urlBuilder->getRouteUrl('buckaroo/redirect/process'),
            'ReturnURLCancel' => $this->urlBuilder->getRouteUrl('buckaroo/redirect/process'),
            'ReturnURLError' => $this->urlBuilder->getRouteUrl('buckaroo/redirect/process'),
            'ReturnURLReject' => $this->urlBuilder->getRouteUrl('buckaroo/redirect/process'),
            'OriginalTransactionKey' => $this->originalTransactionKey,
            'StartRecurrent' => $this->startRecurrent,
            'PushURL' => $this->urlBuilder->getDirectUrl('rest/V1/buckaroo/push'),
            'Services' => [
                'Service' => $this->getServices()
            ],
        ];

        return $body;
    }
}
