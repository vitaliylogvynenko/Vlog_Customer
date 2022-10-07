<?php
/**
 * @author Vitalii Lohvynenko
 * @copyright Copyright (c) 2021 Vitalii Lohvynenko
 */
namespace Vlog\Customer\Model;

use Exception;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Custom send mail
 */
class SendMail
{
    const TEMPLATE_ID = 'vlog_customer_create_account_email_template';

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param CustomerInterface $customer
     */
    public function send(CustomerInterface $customer)
    {
        try {
            $this->transportBuilder
                ->setTemplateIdentifier(self::TEMPLATE_ID)
                ->setTemplateOptions(
                    [
                        'area'  => \Magento\Framework\App\Area::AREA_ADMINHTML,
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                )
                ->setTemplateVars(
                    [
                        'customer' => [
                            'firstname' => $customer->getFirstname(),
                            'lastname' => $customer->getLastname(),
                            'email' => $customer->getEmail(),
                        ],
                    ]
                )
                ->addTo(
                    $this->getToEmail($customer->getWebsiteId()),
                    $this->getToName($customer->getWebsiteId())
                )
                ->setFromByScope(
                    'general'
                )
                ->getTransport()
                ->sendMessage();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getToEmail($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_support/email',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @return mixed
     */
    public function getToName($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_support/name',
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
