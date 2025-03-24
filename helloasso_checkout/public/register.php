<?php
namespace Paheko;

use KD2\UserSession;

use Paheko\DB;
use Paheko\Users\Users;
use Paheko\Users\DynamicFields;
use Paheko\Services\Fees;
use Paheko\Services\Services;
use Paheko\Entities\Services\Service_User;

use Paheko\Plugin\HelloAsso_Checkout\API;

$csrf_key = 'helloasso_checkout_register';

if (!isset($_GET['service_id']))
    throw new UserException("Une activité (service_id) doit être spécifiée dans l'URL");

$service_id = (int) $_GET['service_id'];
$service = Services::get($service_id);
if ($service == null)
    throw new UserException("Aucune activité ne correspond au service_id $service_id");

if (isset($_GET['checkoutIntentId'])) {
    $checkout_id = (int) $_GET['checkoutIntentId'];
    $checkout = API::getInstance()->getCheckout($checkout_id);
    $_POST = (array) $checkout->metadata;
}

$form = new Form;
$tpl->assign_by_ref('form', $form);

$formFees = isset($_GET['fees']) ? (array) $_GET['fees'] : [];
$account = (array) $plugin->getConfig('account');

$fees = array_filter(Fees::listAllByService(), fn($fee) => $fee->id_service == $service_id);
if (!empty($formFees))
    $fees = array_filter($fees, fn($fee) => in_array($fee->id, $formFees));
usort($fees, fn($a, $b) => $a->amount - $b->amount);

$selected_fee = $form('fee') != null ? current(array_filter($fees, fn($fee) => (string) $fee->id == (int) $form('fee'))) : null;

$tpl->assign('layout', 'public');
$tpl->assign('status', 'validate');

$fields = array_filter(DynamicFields::getInstance()->all(), fn ($field) => $field->user_access_level > 0);

$user = getUser($tpl, $form);
$tpl->assign_by_ref('user', $user);

$tpl->assign(compact('csrf_key', 'service', 'fields', 'fees'));

$form->runIf('validate', function () use ($tpl, $form, $user, $service, $selected_fee) {
    $user->selfCheck();
    
	$session = new UserSession(DB::getInstance());
    $session->start(true);
    $session->set('user', $_POST);
    $session->save();
    $session->keepAlive();

    $first_name = "";
    $last_name = "";
    $name_fields = DynamicFields::getInstance()->getNameFields();
    foreach ($name_fields as $name_field) {
        $user->{$name_field} = trim(preg_replace("/[^a-zA-Z -]/", "", $form($name_field)));
        if ($name_field == 'first_name' || $name_field == 'prenom')
            $first_name = $form($name_field);
        else if (count($name_fields) > 1 && ($name_field == 'last_name' || $name_field == 'nom'))
            $last_name = $form($name_field);
    }

    $payer = [
        'firstName' => $first_name,
        'lastName' => $last_name,
        'email' => $form('email')
    ];

    $checkout = API::getInstance()->createCheckout($selected_fee->amount, $service->label . ' - ' . $selected_fee->label, "service_id=$service->id", $payer, $_POST);

    $tpl->assign('checkout', $checkout);
    $tpl->assign('status', 'checkout');

    Utils::redirect($checkout->url);
}, $csrf_key);

if (isset($_GET['checkoutIntentId'])) {
    switch ($_GET['code']) {
        case 'succeeded':
            $user->setNumberIfEmpty();
            $user->save();

            $users = [$user->id => Users::getName($user->id)];
            $service_user_form = [
                'id_service' => $service_id,
                'id_fee' => $selected_fee->id,
                'amount' => $selected_fee->amount / 100,
                'create_payment' => 1,
                'account_selector' => $account,
                'notes' => "Commande n° " . $checkout->order->id,
                'paid' => 1,
                'date' => new \DateTime
            ];
            Service_User::createFromForm($users, null, false, $service_user_form);

            redirect($service_id, 'success', 2);
            break;
        case 'canceled':
            redirect($service_id, 'canceled', 2);
            break;
        default:
            redirect($service_id, 'error', 2);
            break;
    }
}

$tpl->display(__DIR__ . '/../templates/register.tpl');

function getUser($tpl, $form)
{
    $user = Users::create();
    $user->importForm();

    $login_field = DynamicFields::getLoginField();

    if (!empty($form($login_field))) {
        $existing_user = Users::getFromLogin($form($login_field));
        $exists = !empty($existing_user);
        $tpl->assign('exists', $exists);

        if ($exists) {
            $user = $existing_user;
        }
    }

    return $user;
}

function redirect($service_id, $status, $step = 0)
{
    Utils::redirect(Utils::getSelfURI(['service_id' => $service_id, 'status' => $status, 'step' => $step]));
}