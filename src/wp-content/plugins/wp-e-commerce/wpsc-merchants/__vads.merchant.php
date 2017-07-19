<?php
/**
###COMMON_PHP_FILE_HEADER###
 */

$nzshpcrt_gateways[$num] = array(
    'name' => __('PayZen', 'wp-e-commerce'),
    'api_version' => 2.0,
    'class_name' => 'wpsc_merchant___vads',
    'has_recurring_billing' => true,
    'display_name' => __('Manual Payment', 'wp-e-commerce'),
    'wp_admin_cannot_cancel' => false,
    'requirements' => array(
        'php_version' => 5.3
    ),
    'form' => 'form___vads',
    'internalname' => 'wpsc_merchant___vads',
);

$image = apply_filters('wpsc_merchant_image', '', $nzshpcrt_gateways[$num]['internalname']);
if (! empty($image)) {
    $nzshpcrt_gateways[$num]['image'] = $image;
}

require_once(dirname(__FILE__) . '/__vads-includes/__VadsApi.php');

class wpsc_merchant___vads extends wpsc_merchant
{

    var $name = '';

    function __construct($purchase_id = null, $is_receiving = false)
    {
        $this->name = __('PayZen', 'wp-e-commerce');
        parent::__construct($purchase_id, $is_receiving);
    }

    function submit()
    {
        echo '<pre>' . print_r($this->cart_data, true) . '</pre>';
        die();

        // process shop language
        $lang = get_locale() ? substr(get_locale(), 0, 2) : null;
        $__vads_language = __VadsApi::isSupportedLanguage($lang) ? $lang : get_option('__vads_language');

        // process currency
        $__vads_currency = __VadsApi::findCurrencyByAlphaCode($this->cart_data['store_currency']);

        // amount
        $amount = $this->cart_data['total_price'];

        // 3-DS activation according to amount
        $threeds_mpi = null;
        if (! empty(get_option('__vads_3ds_min_amount')) && $amount < get_option('__vads_3ds_min_amount')) {
            $threeds_mpi = '2';
        }

        // effective used version
        include ABSPATH . WPINC . '/version.php';
        $version = $wp_version . '-' . WPSC_VERSION;

        $data = array(
            'amount' => $__vads_currency->convertAmountToInteger($amount),
            'contrib' => '###CONTRIB_PARAM###/' . $version . '/' . PHP_VERSION,
            'currency' => $__vads_currency->getNum(),
            'language' => $__vads_language,
            //TODO 'order_id' => $order->order_number,
            'threeds_mpi' => $threeds_mpi,

            //TODO 'cust_id' => $this->user->user_id,
            'cust_email' => $this->cart_data['email_address'],

            'cust_first_name' => $this->cart_data['first_name'],
            'cust_last_name' => $this->cart_data['last_name'],
            'cust_address' => $this->cart_data['address'],
            'cust_zip' => $this->cart_data['post_code'],
            'cust_city' => $this->cart_data['city'],
            'cust_state' => $this->cart_data['state'],
            'cust_country' => $this->cart_data['country'],
            'cust_phone' => $this->cart_data['phone'],

            'ship_to_first_name' => $this->cart_data['first_name'],
            'ship_to_last_name' => $this->cart_data['last_name'],
            'ship_to_street' => $this->cart_data['address'],
            'ship_to_city' => $this->cart_data['city'],
            'ship_to_state' => $this->cart_data['state'],
            'ship_to_country' => $this->cart_data['country'],
            'ship_to_zip' => $this->cart_data['post_code'],

            'url_return' => add_query_arg('sessionid', $this->cart_data['session_id'], $this->cart_data['transaction_results_url']),
            'url_cancel' => add_query_arg('sessionid', $this->cart_data['session_id'], $this->cart_data['shopping_cart_url'])
        );

         $params = array(
             'site_id',
             'key_test',
             'key_prod',
             'ctx_mode',
             'platform_url',
             'available_languages',
             'capture_delay',
             'validation_mode',
             'payment_cards',
             'redirect_enabled',
             'redirect_success_timeout',
             'redirect_success_message',
             'redirect_error_timeout',
             'redirect_error_message',
             'return_mode'
         );

        foreach ($params as $param) {
            $data[$param] = get_option('__vads_' . $param);
        }

        require_once(dirname(__FILE__) . '/__vads-includes/__VadsRequest.php');
        $request = new __VadsRequest();
        $request->setFromArray($data);

        echo $request->getRequestHtmlForm();
        exit;
    }
}

function form___vads()
{
    $output = '
            <tr><td colspan="2"><h4>INFORMATIONS SUR LE MODULE</h4></td></tr>

            <tr>
                <td>Développé par</td>
                <td><a href="https://www.lyra-network.com/" target="_blank">Lyra Network</a></td>
            </tr>

            <tr>
                <td >Courriel de contact</td>
                <td><a href="mailto:###SUPPORT_EMAIL###">###SUPPORT_EMAIL###</a></td>
            </tr>

            <tr>
                <td>Version du module</td>
                <td>###CONTRIB_VERSION###</td>
            </tr>

            <tr>
                <td>Version de la plateforme</td>
                <td>###VADS_VERSION###</td>
            </tr>


            <tr><td colspan="2"><h4>PARAMETRES DE BASE</h4></td></tr>

            <tr>
                <td valign="top">Logs</td>
                <td>
                    <label><input type="radio" name="wpsc_options[__vads_log]" value="True"' . (get_option('__vads_log') == 'True' ? ' checked="checked"' : '') . '>Oui</label>&nbsp;&nbsp;&nbsp;
                    <label><input type="radio" name="wpsc_options[__vads_log]" value="False"' . (get_option('__vads_log') == 'False' ? ' checked="checked"' : '') . '>Non</label>
                    <br /><i>Activer / Désactiver les logs du module.</i>
                </td>
            </tr>

            <tr><td colspan="2"><h4>ACCES A LA PLATEFORME</h4></td></tr>
            <tr>
                <td valign="top">Identifiant de la boutique</td>
                <td><input type="text" name="wpsc_options[__vads_site_id]" value="' . (get_option('__vads_site_id') ? get_option('__vads_site_id') : '###SITE_ID###') . '" required><br /><i>L\'identifiant fourni par PayZen</i></td>
            </tr>

            <tr>
                <td valign="top">Certificat en mode test</td>
                <td><input type="text" name="wpsc_options[__vads_key_test]" value="' . (get_option('__vads_key_test') ? get_option('__vads_key_test') : '###KEY_TEST###') . '" required><br /><i> Certificat fourni par PayZen pour le mode test</i></td>
            </tr>

            <tr>
                <td valign="top">Certificat en mode production</td>
                <td><input type="text" name="wpsc_options[__vads_key_prod]" value="' . (get_option('__vads_key_prod') ? get_option('__vads_key_prod') : '###KEY_PROD###') . '" required><br /><i>Certificat fourni par PayZen pour le mode production</i></td>
            </tr>

            <tr>
                <td valign="top">Mode</td>
                <td>
                    <select name="wpsc_options[__vads_ctx_mode]" required>
                        <option' . (get_option('__vads_ctx_mode') == 'TEST' ? ' selected="selected"' : '') . ' value="TEST">TEST</option>
                        <option' . (get_option('__vads_ctx_mode') == 'PRODUCTION' ? ' selected="selected"' : '') . ' value="PRODUCTION">PRODUCTION</option>
                    </select><br /><i>Mode de fonctionnement du module</i>
                </td>
            </tr>

            <tr>
                <td valign="top">URL de la page de paiement</td>
                <td><input type="text" name="wpsc_options[__vads_platform_url]" value="'. (get_option('__vads_platform_url') ? get_option('__vads_platform_url') : '###GATEWAY###').'" required style="width:400px"><br /><i>Certificat fourni par PayZen pour le mode production</i></td>
            </tr>

            <tr>
                <td valign="top">URL de notification à copier dans le back office PayZen</td>
                <td valign="top">http://.....</td>
            </tr>


            <tr><td colspan="2"><h4>PAGE DE PAIEMENT</h4></td></tr>

            <tr>
                <td valign="top">Langue par défaut</td>
                <td>
                    <select name="wpsc_options[__vads_language]">';

    foreach (__VadsApi::getSupportedLanguages() as $code => $label) {
        $selected = get_option('__vads_language') == $code ? ' selected="selected"' : '';
        $output .= '    <option' . $selected . ' value="' . $code . '">' . $label . '</option>';
    }

    $output .= '    </select><br /><i>Sélectionner la langue par défaut à utiliser sur la page de paiement</i>
                </td>
            </tr>

            <tr>
                <td valign="top">Langue disponibles</td>
                <td>
                    <select name="wpsc_options[__vads_available_languages][]" multiple="multiple">';

    foreach (__VadsApi::getSupportedLanguages() as $code => $label) {
        $values = get_option('__vads_available_languages');

        $selected = (is_array($values) && in_array($code, $values)) ? ' selected="selected"' : '';
        $output .= '   <option' . $selected . ' value="' . $code . '">' . $label . '</option>';
    }

    $output .= '    </select><br /><i>Sélectionner les langues à proposer sur la page de paiement</i>
                </td>
            </tr>

            <tr>
                <td valign="top">Délai avant remise en banque</td>
                <td><input type="text" name="wpsc_options[__vads_capture_delay]" value="' . get_option('__vads_capture_delay') . '"><br /><i>Le nombre de jours avant la remise en banque (paramétrable sur votre Back Office PayZen</i></td>
            </tr>

            <tr>
                <td valign="top">Mode de validation</td>
                <td>
                    <select name="wpsc_options[__vads_validation_mode]">
                        <option' . (get_option('__vads_validation_mode') === ''  ? ' selected="selected"' : '') . ' value="">Par défaut</option>
                        <option' . (get_option('__vads_validation_mode') === '0' ? ' selected="selected"' : '') . ' value="0">Automatique</option>
                        <option' . (get_option('__vads_validation_mode') === '1' ? ' selected="selected"' : '') . ' value="1">Manuel</option>
                    </select><br /><i>En mode manuel, vous devrez confirmer les paiements dans le Back Office PayZen</i>
                </td>
            </tr>

            <tr>
                <td valign="top">Types de carte</td>
                <td>
                    <select name="wpsc_options[__vads_payment_cards][]" multiple="multiple" size="8">';

    foreach (__VadsApi::getSupportedCardTypes() as $code => $label) {
        $values = get_option('__vads_payment_cards');

        $selected = (is_array($values) && in_array($code, $values)) ? ' selected="selected"' : '';
        $output .= '   <option' . $selected . ' value="' . $code . '">' . $label . '</option>';
    }

    $output .= '    </select><br /><i>Le(s) type(s) de carte pouvant être utilisé(s) pour le paiement. Ne rien sélectionner pour utiliser la configuration de la plateforme.</i>
                </td>
            </tr>


            <tr><td colspan="2"><h4>3-DS SELECTIF</h4></td></tr>

            <tr>
                <td valign="top">Montant minimal pour lequel activer 3-DS</td>
                <td><input type="text" name="wpsc_options[__vads_3ds_min_amount]" value="' . get_option('__vads_3ds_min_amount') . '"><br /><i>Nécessite la souscription à l\'option 3-D Secure sélectif</i></td>
            </tr>


            <tr><td colspan="2"><h4>RETOUR A LA BOUTIQUE</h4></td></tr>

            <tr>
                <td valign="top">Redirection automatique</td>
                <td>
                    <label><input type="radio" name="wpsc_options[__vads_redirect_enabled]" value="True"' . (get_option('__vads_redirect_enabled') == 'True' ? ' checked="checked"' : '') . '>Oui</label>&nbsp;&nbsp;&nbsp;
                    <label><input type="radio" name="wpsc_options[__vads_redirect_enabled]" value="False"' . (get_option('__vads_redirect_enabled') == 'False' ? ' checked="checked"' : '') . '>Non</label>
                    <br /><i>Si activée, l\'acheteur sera redirigé automatiquement vers notre site lorsque le paiement a réussi.</i>
                </td>
            </tr>

            <tr>
               <td valign="top">Temps avant redirection (succés)</td>
               <td><input type="text" name="wpsc_options[__vads_redirect_success_timeout]" pattern="300|[1-2]?[0-9]{0,2}" value="' . (get_option('__vads_redirect_success_timeout') ? get_option('__vads_redirect_success_timeout') :  '5') . '"><br /><i>Temps en secondes (0-300) avant que l\'acheteur ne soit automatiquement redirigé vers votre site lorsque le paiement réussi</i></td>
            </tr>

            <tr>
                <td valign="top">Message avant redirection (succés)</td>
                <td>
                    <input type="text" name="wpsc_options[__vads_redirect_success_message]" value="'. (get_option('__vads_redirect_success_message') ? get_option('__vads_redirect_success_message') :  '###SUCCESS_MSG###') . '" style="width:400px;">
                    <br /><i>Message affiché sur la page de paiement avant rediretion lorsque le paiement a réussi.</i>
                </td>
            </tr>

            <tr>
                <td valign="top">Temps avant redirection (échec)</td>
                <td><input type="text" name="wpsc_options[__vads_redirect_error_timeout]" pattern="300|[1-2]?[0-9]{0,2}" value="' . (get_option('__vads_redirect_error_timeout') ? get_option('__vads_redirect_error_timeout') :  '5') . '"><br /><i>Temps en secondes (0-300) avant que l\'acheteur ne soit automatiquement redirigé vers votre site lorsque le paiement réussi</i></td>
            </tr>

            <tr>
                <td valign="top">Message avant redirection (échec)</td>
                <td>
                    <input type="text" name="wpsc_options[__vads_redirect_error_message]" value="'. (get_option('__vads_redirect_error_message') ? get_option('__vads_redirect_error_message') :  '###ERROR_MSG###') . '" style="width: 400px;">
                    <br /><i>Message affiché sur la page de paiement avant rediretion lorsque le paiement a échoué.</i>
                </td>
            </tr>

            <tr>
                <td valign="top">Mode de retour</td>
                <td>
                    <select name="wpsc_options[__vads_return_mode]">
                        <option' . (get_option('__vads_return_mode') == 'GET' ? ' selected="selected"' : '') . ' value="GET">GET</option>
                        <option' . (get_option('__vads_return_mode') == 'POST' ? ' selected="selected"' : '') . ' value="POST">POST</option>
                    </select><br /><i>Façon dont l\'acheteur transmettra le résultat du paiement lors de son retour à la boutique.</i>
               </td>
            </tr>';

    return $output;
}

function _wpsc_filter_notest_merchant_customer_notification_raw_message($message, $notification) {
    $purchase_log = $notification->get_purchase_log();

    if ($purchase_log->get('gateway') == 'wpsc_merchant___vads') {
        $message = get_option('payment_instructions', '') . "\r\n" . $message;
    }

    return $message;
}

add_filter(
    'wpsc_purchase_log_customer_notification_raw_message',
    '_wpsc_filter_notest_merchant_customer_notification_raw_message',
    10,
    2
);



add_filter(
    'wpsc_purchase_log_customer_html_notification_raw_message',
    '_wpsc_filter_notest_merchant_customer_notification_raw_message',
    10,
    2
);
