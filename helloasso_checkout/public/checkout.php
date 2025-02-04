<?php

namespace Paheko;

use Paheko\Entities\Accounting\Transaction;
use Paheko\Accounting\Transactions;
use Paheko\Plugin\HelloAsso_Checkout\API;

$status = isset($_GET['status']) ? $_GET['status'] : 'new';

$transaction = Transactions::get((int) $_GET['transaction_id']);

if (!$transaction) {
    throw new UserException('Cette écriture n\'existe pas');
}

$tpl->assign('qr_code_src', 'https://api.qrserver.com/v1/create-qr-code/?size=200x200');

$account = (array) $plugin->getConfig('account');
$accountId = array_keys($account)[0];

if ($status == 'new') {
    $totalAmount = $transaction->sum();

    $checkout = API::getInstance()->createCheckout($totalAmount, $transaction->label, "transaction_id=$transaction->id");

    $line = $transaction->getDebitLine();
    $line->id_account = 170;
    $line->save();

    $transaction->setPaymentReference((string) $checkout->id);
    $transaction->type = 5;
    $transaction->save();

    $tpl->assign('checkout_url', $checkout->url);
    $tpl->assign('hide_title', true);
} else {
    $checkout_id = intval($transaction->getPaymentReference());

    if ($checkout_id == null) {
        $transaction->notes = "Transaction non vérifiée";
        $transaction->save();
        throw new UserException("Le paiement a été enregistré mais il n'a pas pu être vérifié. Si besoin, contactez l'association.");
    }

    $checkout = API::getInstance()->getCheckout($checkout_id);
    $tpl->assign('checkout_url', $checkout->redirectUrl);

    if (!isset($checkout->order)) {
        if ($status == 'error') {
            $transaction->notes = "Paiement échoué";
            $transaction->save();

            throw new UserException("Le paiement a échoué. Vous n'avez pas été débité. Si besoin, contactez l'association.");
        } elseif ($status == 'canceled') {
            $transaction->notes = "Paiement inachevé";
            $transaction->save();

            $tpl->assign('checkout_url', $checkout->redirectUrl);
        } else {
            Utils::redirect("?transaction_id=$transaction->id&status=canceled");
        }
    } elseif ($status == 'success') {
        $line = $transaction->getDebitLine();

        $line->id_account = $accountId;
        $line->save();

        $transaction->type = 1;
        $transaction->notes = "Paiement réussi et vérifié (commande n°" . $checkout->order->id . ')';
        $transaction->save();
    }
}

$tpl->display(__DIR__ . '/../templates/checkout.tpl');