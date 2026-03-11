<?php


namespace App\Service;


use App\Entity\Plan;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;


class StripeService
{
    public function __construct(
        private string $secretKey,
        private string $webhookSecret,
        private EntityManagerInterface $em,
    ) {
        Stripe::setApiKey($this->secretKey);
    }


    /**
     * Crée une Checkout Session Stripe pour l'abonnement à un plan.
     * Retourne l'URL vers laquelle rediriger l'utilisateur.
     */
    public function createCheckoutSession(
        User $user,
        Plan $plan,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $params = [
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $plan->getStripePriceId(),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            // On stocke l'ID utilisateur pour le retrouver dans le webhook
            'metadata' => [
                'user_id' => $user->getId(),
                'plan_id' => $plan->getId(),
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => $user->getId(),
                    'plan_id' => $plan->getId(),
                ],
            ],
        ];

        // Si l'utilisateur a déjà un customer Stripe, on le réutilise ;
        // sinon on laisse Stripe en créer un via customer_email.
        if ($user->getStripeCustomerId() !== null) {
            $params['customer'] = $user->getStripeCustomerId();
        } else {
            $params['customer_email'] = $user->getEmail();
        }

        $session = Session::create($params);

        // On persiste le customer ID dès la création de la session
        if ($user->getStripeCustomerId() === null && $session->customer !== null) {
            $user->setStripeCustomerId($session->customer);
            $this->em->flush();
        }

        return $session->url;
    }


    /**
     * Vérifie la signature du webhook Stripe et retourne l'événement.
     * Lève une exception si la signature est invalide.
     */
    public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
    }
}
