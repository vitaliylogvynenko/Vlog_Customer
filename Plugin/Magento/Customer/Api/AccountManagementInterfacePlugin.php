<?php
/**
 * @author Vitalii Lohvynenko
 * @copyright Copyright (c) 2021 Vitalii Lohvynenko
 */
namespace Vlog\Customer\Plugin\Magento\Customer\Api;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Psr\Log\LoggerInterface;
use Vlog\Customer\Model\SendMail;

/**
 * Modify customer registration for storefront and admin area
 */
class AccountManagementInterfacePlugin
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SendMail
     */
    private $sendMail;

    /**
     * @param LoggerInterface $logger
     * @param SendMail $sendMail
     */
    public function __construct(
        LoggerInterface $logger,
        SendMail $sendMail
    ) {
        $this->logger = $logger;
        $this->sendMail = $sendMail;
    }

    /**
     * @param AccountManagementInterface $subject
     * @param CustomerInterface $customer
     * @param string|null $password
     * @param string $redirectUrl
     * @return array
     */
    public function beforeCreateAccount(
        AccountManagementInterface $subject,
        CustomerInterface $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        $customer->setFirstname(
            str_replace(' ', '', $customer->getFirstname())
        );
        return [$customer, $password, $redirectUrl];
    }

    /**
     * @param AccountManagementInterface $subject
     * @param $result
     * @param CustomerInterface $customer
     * @return mixed
     */
    public function afterCreateAccount(
        AccountManagementInterface $subject,
        $result,
        CustomerInterface $customer
    ) {
        $this->logger->info(
            __(
                'New customer created. First Name: %1, Last Name: %2, Email: %3.',
                $customer->getFirstname(),
                $customer->getLastname(),
                $customer->getEmail()
            )
        );
        $this->sendMail->send($customer);
        return $result;
    }
}
