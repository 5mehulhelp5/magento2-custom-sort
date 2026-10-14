<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Perspective\LoyaltyEngine\Api\LoyaltyManagementInterface;

/**
 * Mutation resolver for removing loyalty points discount from cart.
 */
class RemoveLoyaltyPointsFromCart implements ResolverInterface
{
    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param LoyaltyManagementInterface $loyaltyManagement
     */
    public function __construct(
        private readonly GetCartForUser $getCartForUser,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly LoyaltyManagementInterface $loyaltyManagement
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }

        $maskedCartId = (string)$args['input']['cart_id'];
        $currentUserId = $context->getUserId();

        if (!$currentUserId) {
            throw new GraphQlAuthorizationException(__('Only registered customers can remove loyalty points.'));
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        try {
            $cart = $this->getCartForUser->execute($maskedCartId, $currentUserId, $storeId);
        } catch (\Exception $e) {
            if (is_numeric($maskedCartId)) {
                try {
                    $cart = $this->cartRepository->get((int)$maskedCartId);
                } catch (NoSuchEntityException) {
                    throw new GraphQlNoSuchEntityException(__('Could not find a cart with ID "%1".', $maskedCartId));
                }

                if ((int)$cart->getCustomerId() !== (int)$currentUserId) {
                    throw new GraphQlAuthorizationException(
                        __('The current user cannot perform operations on this cart.')
                    );
                }
            } else {
                throw $e;
            }
        }

        try {
            $this->loyaltyManagement->removePoints((int)$cart->getId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart
            ]
        ];
    }
}
