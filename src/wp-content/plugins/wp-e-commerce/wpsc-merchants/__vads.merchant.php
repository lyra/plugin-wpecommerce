<?php

/**
 * WP eCommerce Test Merchant Gateway
 * This is the file for the test merchant gateway
 *
 * @package wp-e-comemrce
 * @since 3.7.6
 * @subpackage wpsc-merchants
 */
$nzshpcrt_gateways[$num] = array(
		'name' => __( 'PayZen', 'wp-e-commerce' ),
		'api_version' => 2.0,
		'class_name' => 'wpsc_merchant___vads',
		'has_recurring_billing' => true,
		'display_name' => __( 'Manual Payment', 'wp-e-commerce' ),
		'wp_admin_cannot_cancel' => false,
		'requirements' => array(
				/// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
				///'php_version' => 5.0,
		),
		
		'form' => 'form___vads',
		'internalname' => 'wpsc_merchant___vads',
);
$image = apply_filters( 'wpsc_merchant_image', '', $nzshpcrt_gateways[$num]['internalname'] );
if ( ! empty( $image ) ) {
	$nzshpcrt_gateways[$num]['image'] = $image;
}



class wpsc_merchant___vads extends wpsc_merchant {
	
	var $name = '';
	
	function __construct( $purchase_id = null, $is_receiving = false ) {
		$this->name = __( 'Payzen', 'wp-e-commerce' );
		parent::__construct( $purchase_id, $is_receiving );
	}
	
	function submit() {
		$this->set_purchase_processed_by_purchid(2);
		
		$this->go_to_transaction_results($this->cart_data['session_id']);
		
		exit();
		
	}
}

function form___vads() {
	$output = "
		
 <tr><td colspan=2>INFORMATIONS SUR LE MODULE</tr></td> 
        
         
            <tr>
                <td> Développé par</td>
                <td> <a href='https://www.lyra-network.com/' onclick='window.open(this.href); return false;'>Lyra Network </a></td>
            </tr>
            <tr>
                <td >Courriel de contact</td>
                <td><a href=''> support@payzen.eu </a></td>
            </tr>
            <tr>
                <td>Version du module</td>
                <td><i>1.3.2</i></td>
            </tr>
            <tr>
                <td> Version de la plateforme </td>
                <td><i> V2 </i></td>
            </tr>
         
            <tr><td colspan=2><p> <a href='lien-docu' > <font color ='red'> <u color='red'>CLIQUER ICI POUR ACCEDER A LA DOCUMENTATION </u> </font> </a> </p></td></tr> 
              <tr><td colspan=2>PARAMETRES DE BASE</td> </tr>
                     <tr>
                         <td valign='top'>Logs</td> <td><input type='checkbox' name='log' checked>Activer/Désactiver <br> <i>Activer/Désactiver les logs du module. Le fichier de log sera enregistré dans...</i></td> 
                     </tr>
                    
                     <tr>
                         <td colspan=2>
                             ACCES A LA PLATEFORME
                         </td>
                     </tr>
                     
                     <tr>
                         <td valign='top'>Identifiant de la boutique</td>
                         <td><input type='text' name='__vads_site_id' value='" . (get_option( '__vads_site_id' ) ? get_option( '__vads_site_id' ) :  '###SITE_ID###') . "' required> <br> <i>L'identifiant fourni par Payzen</i></td>
                     </tr>
                     
                     <tr>
                         <td valign='top'>Certificat en mode test</td>
                         <td><input type='text' name='__vads_key_test' value='". (get_option( '__vads_key_test' ) ? get_option( '__vads_key_test' ) :  '###KEY_TEST###')."' required><br><i> Certificat fourni par payzen pour le mode test</i></td>
                     </tr>
                     <tr>
                         <td valign='top'>Certificat en mode production</td>
                         <td><input type='text' name='__vads_key_prod' value='". (get_option( '__vads_key_prod' ) ? get_option( '__vads_key_prod' ) :  '###KEY_PROD###')."' required><br> <i>Certificat fourni par payzen pour le mode production</i></td>
                     </tr>
                     <tr>
                         <td valign='top'>Mode</td>
                         <td><select name='__vads_ctx_mode' size='1' required> 
                                 <Option>Test</Option>
                                 <Option>Production</Option> 
                             </select><br><i>Mode de fonctionnement du module</i>
                         </td>
                     </tr>
                     <tr>
                         <td valign='top'>URL de la page de paiement </td>
                         <td><input type='text' name='__vads_platform_url' value='". (get_option( '__vads_platform_url' ) ? get_option( '__vads_platform_url' ) :  '###GATEWAY###')."' required style='width:400px'><br> <i>Certificat fourni par payzen pour le mode production</i></td>
                     </tr>
                     <tr>
                         <td valign='top'>URL de notification à copier <br> dans le back office PayZen </td>
                         <td valign='top'><i>http://.....</i> </td>
                     </tr>
                      
                    <tr>
                        <td colspan=2> 
                            <p>PAGE DE PAIEMENT</p> 
                        </td>
                    </tr>
                    
                    <tr>
                        <td valign='top'>Langue par défaut</td>
                        <td> 
                            <select name='data[payment][payment_params][__vads_language]' class='inputbox' id='__vads_language' style='width: 122px;' >
                            <?php
                            foreach (__VadsApi::getSupportedLanguages() as $code => $label) {
                                $selected = (@$params->__vads_language == $code) ? ' selected=".selected."' : '';
                                echo '<option' . $selected . ' value=". $code . ">' . JText::_('__VADS_LANGUAGE_' . strtoupper($label)) . '</option>';
                            }
                            ?>
                        </select> <br> <i>Sélectionner la langue par défaut à utiliser sur la page de paiement</i>
                        </td>
                    </tr>
                    
                    <tr >
                        <td valign='top'>Langue disponibles</td>
                                
                        <td height=100px> 
                           <select name='data[payment][payment_params][__vads_language]' class='inputbox' id='__vads_language' style='width: 122px;' >
                            <?php
                            foreach (__VadsApi::getSupportedLanguages() as $code => $label) {
                                $selected = (@$params->__vads_language == $code) ? ' selected=".selected."' : '';
                                echo '<option' . $selected . ' value=". $code . ">' . JText::_('__VADS_LANGUAGE_' . strtoupper($label)) . '</option>';
                            }
                            ?>
                        </select> <br> <i>Sélectionner les langues à proposer sur la page de paiement </i>
                        </td>
                    </tr>
                    
                    <tr> 
                        <td valign='top'>Délai avant remise en banque</td> 
                        <td> <input type='txt' name='__vads_capture_delay'> <br> <i>Le nombre de jours avant la remise en banque(paramétrable sur votre BAckOffice PayZen</i></td>
                    </tr>
                    
                    <tr>
                        <td valign='top'>Mode de validation</td> 
                                <td> 
                                    <select size='1' name='__vads_validation_mode'>
                                        <option> Configuration BackOffice </option>
                                        <option> Configuration ... </option>
                                    </select> <br> <i>En mode manuel, vous devrez confirmer les paiements dans le BackOffice PayZen</i>
                                </td>
                    </tr>
                    
                    <tr>
                        <td colspan=2>
                            <p> 3DS SELECTIF </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <td valign='top'>Monter minimal pour lequel activer3-DS</td>
                        <td> <input type='text' name='__vads_3ds_min_amount'> <br> <i>Nécessite la souscription à l'option 3-D Secure sélectif</i></td>
                    </tr>
                    
                    <tr>
                        <td colspan=2> 
                            <p>RETOUR A LA BOUTIQUE </p> 
                        </td>
                    </tr>
                    
                    <tr>
                        <td valign='top'> Redirection automatique </td>
                        <td> <input type='checkbox' name='__vads_redirect_enabled'>Activer/Désactiver   <br> <i> Si activée, l'acheteur sera redirigé automatiquement vers notre site lorsque le paiement a réussi.</i> </td>
                    </tr>
                    
                    <tr>
                        <td valign='top'>Temps avant redirection (succés)</td>
                        <td> <input type='text' name='__vads_redirect_success_timeout'  autofocus required pattern='[0-9]' value='5'> <br> <i>Temps en secondes (0-300) avant que l'acheteur ne soit automatiquement redirigé vers votre site lorsque le paiement réussi</i></td>
                    </tr>
                    
                    <tr>
                        <td valign='top'>Message avant redirection (succés)</td>
                        <td> <input type='text' name='__vads_redirect_success_message' value='". (get_option( '__vads_redirect_success_message' ) ? get_option( '__vads_redirect_success_message' ) :  '###SUCCESS_MSG###')."' style='width:400px;' > 
                             <select name='data[payment][payment_params][__vads_language]' class='inputbox' id='__vads_language' style='width: 122px;' >
                            <?php
                            foreach (__VadsApi::getSupportedLanguages() as $code => $label) {
                                $selected = (@$params->__vads_language == $code) ? ' selected=".selected."' : '';
                                echo '<option' . $selected . ' value=". $code . ">' . JText::_('__VADS_LANGUAGE_' . strtoupper($label)) . '</option>';
                            }
                            ?>
                        </select> <br> <i>Message affiché sur la page de paiement avant rediretion lorsque le paiement a réussi.</i></td>
                    </tr>
                    
                    <tr>
                        <td valign='top'>Temps avant redirection (échec)</td>
                        <td> <input type='text' name='__vads_redirect_error_timeout' autofocus required pattern='[0-9]' value='5'> <br> <i>Temps en secondes (0-300) avant que l'acheteur ne soit automatiquement redirigé vers votre site lorsque le paiement réussi</i></td>
                    </tr>
                    
                    <tr>
                        <td valign='top'>Message avant redirection (échec)</td>
                        <td> <input type='text' name='__vads_redirect_error_message' value='". (get_option( '__vads_redirect_error_message' ) ? get_option( '__vads_redirect_error_message' ) :  '###ERROR_MSG###')."' style='width:400px;'> 
                           <select name='data[payment][payment_params][__vads_language]' class='inputbox' id='__vads_language' style='width: 122px;' >
                            <?php
                            foreach (__VadsApi::getSupportedLanguages() as $code => $label) {
                                $selected = (@$params->__vads_language == $code) ? ' selected=".selected."' : '';
                                echo '<option' . $selected . ' value=". $code . ">' . JText::_('__VADS_LANGUAGE_' . strtoupper($label)) . '</option>';
                            }
                            ?>
                        </select><br /><br> <i>Message affiché sur la page de paiement avant rediretion lorsque le paiement a échoué.</i></td>
                    </tr>
                    
                <tr>
                        <td valign='top'>Mode de retour</td> 
                                <td> 
                                    <select NAME='__vads_return_mode' size='1'>
                                        <option> GET </option>
                                        <option> POST </option>
                                    </select> <br> <i>Façon dont l'acheteur transmettra le résultat du paiement lors de son retour à la boutique.</i>
                                </td>
		</tr>";
	return $output;
}

function _wpsc_filter_notest_merchant_customer_notification_raw_message( $message, $notification ) {
	$purchase_log = $notification->get_purchase_log();
	
	if ( $purchase_log->get( 'gateway' ) == 'wpsc_merchant___vads' )
		$message = get_option( 'payment_instructions', '' ) . "\r\n" . $message;
		
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