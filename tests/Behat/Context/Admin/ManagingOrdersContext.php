<?php

declare(strict_types=1);

namespace Tests\Sylius\AdminOrderCreationPlugin\Behat\Context\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Tests\Sylius\AdminOrderCreationPlugin\Behat\Page\Admin\NewOrderCustomerPageInterface;
use Tests\Sylius\AdminOrderCreationPlugin\Behat\Page\Admin\OrderCreatePageInterface;
use Tests\Sylius\AdminOrderCreationPlugin\Behat\Page\Admin\OrderIndexPageInterface;
use Tests\Sylius\AdminOrderCreationPlugin\Behat\Page\Admin\OrderShowPageInterface;
use Webmozart\Assert\Assert;

final class ManagingOrdersContext implements Context
{
    /** @var OrderIndexPageInterface */
    private $orderIndexPage;

    /** @var NewOrderCustomerPageInterface */
    private $newOrderCustomerPage;

    /** @var OrderCreatePageInterface */
    private $orderCreatePage;

    /** @var OrderShowPageInterface */
    private $orderShowPage;

    /** @var NotificationCheckerInterface */
    private $notificationChecker;

    public function __construct(
        OrderIndexPageInterface $orderIndexPage,
        NewOrderCustomerPageInterface $newOrderCustomerPage,
        OrderCreatePageInterface $orderCreatePage,
        OrderShowPageInterface $orderShowPage,
        NotificationCheckerInterface $notificationChecker
    ) {
        $this->orderIndexPage = $orderIndexPage;
        $this->newOrderCustomerPage = $newOrderCustomerPage;
        $this->orderCreatePage = $orderCreatePage;
        $this->orderShowPage = $orderShowPage;
        $this->notificationChecker = $notificationChecker;
    }

    /**
     * @When I create a new order for :customer
     */
    public function createNewOrderFor(CustomerInterface $customer): void
    {
        $this->orderIndexPage->open();
        $this->orderIndexPage->createOrder();

        $this->newOrderCustomerPage->selectCustomer($customer->getEmail());
        $this->newOrderCustomerPage->next();
    }

    /**
     * @When I create a new order for a new customer with email :email
     */
    public function createNewOrderForNewCustomerWithEmail(string $email): void
    {
        $this->orderIndexPage->open();
        $this->orderIndexPage->createOrder();

        $this->newOrderCustomerPage->createCustomer($email);
        $this->newOrderCustomerPage->next();
    }

    /**
     * @When I add :product to this order
     */
    public function addProductToThisOrder(ProductInterface $product): void
    {
        $this->orderCreatePage->addProduct($product->getName());
    }

    /**
     * @When /^I specify this order shipping (address as "[^"]+", "[^"]+", "[^"]+", "[^"]+" for "[^"]+")$/
     */
    public function specifyTheShippingAddressAs(AddressInterface $address): void
    {
        $this->orderCreatePage->specifyShippingAddress($address);
    }

    /**
     * @When I select :shippingMethodName shipping method
     */
    public function selectShippingMethod(string $shippingMethodName): void
    {
        $this->orderCreatePage->selectShippingMethod($shippingMethodName);
    }

    /**
     * @When I select :paymentMethodName payment method
     */
    public function selectPaymentMethod(string $paymentMethodName): void
    {
        $this->orderCreatePage->selectPaymentMethod($paymentMethodName);
    }

    /**
     * @When I place this order
     */
    public function placeThisOrder(): void
    {
        $this->orderCreatePage->placeOrder();
    }

    /**
     * @Then I should be notified that order has been successfully created
     */
    public function shouldBeNotifiedThatOrderHasBeenSuccessfullyCreated(): void
    {
        $this->notificationChecker->checkNotification(
            'Order has been successfully created',
            NotificationType::success()
        );
    }

    /**
     * @Then I should be able to copy payment link for a customer
     */
    public function shouldBeAbleToCopyPaymentLinkForCustomer(): void
    {
        Assert::true($this->orderShowPage->hasPaymentLinkToCopy());
    }

    /**
     * @Then there should be one not paid nor shipped order for :customer in the registry
     */
    public function thereShouldBeOneOrderForInTheRegistry(CustomerInterface $customer): void
    {
        $this->orderIndexPage->open();

        Assert::true($this->orderIndexPage->isSingleResourceOnPage([
            'customer' => $customer->getEmail(),
            'state' => 'New',
            'paymentState' => 'Awaiting payment',
            'shippingState' => 'Ready',
        ]));
    }
}