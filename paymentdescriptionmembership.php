<?php

require_once 'paymentdescriptionmembership.civix.php';
// phpcs:disable
use CRM_Paymentdescriptionmembership_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function paymentdescriptionmembership_civicrm_config(&$config): void {
  _paymentdescriptionmembership_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function paymentdescriptionmembership_civicrm_install(): void {
  _paymentdescriptionmembership_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function paymentdescriptionmembership_civicrm_enable(): void {
  _paymentdescriptionmembership_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function paymentdescriptionmembership_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function paymentdescriptionmembership_civicrm_navigationMenu(&$menu): void {
//  _paymentdescriptionmembership_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _paymentdescriptionmembership_civix_navigationMenu($menu);
//}

// --- Actual code for extension below here. ---

/**
 * Hook for altering payment parameters before talking to a payment processor back end.
 *
 * Definition will look like this:
 *
 *   function hook_civicrm_alterPaymentProcessorParams(
 *     $paymentObj,
 *     &$rawParams,
 *     &$cookedParams
 *   );
 *
 * @param CRM_Core_Payment $paymentObj
 *   Instance of payment class of the payment processor invoked (e.g., 'CRM_Core_Payment_Dummy')
 *   See discussion in CRM-16224 as to whether $paymentObj should be passed by reference.
 * @param array|\Civi\Payment\PropertyBag &$rawParams
 *    array of params as passed to to the processor
 * @param array|\Civi\Payment\PropertyBag &$cookedParams
 *     params after the processor code has translated them into its own key/value pairs
 *
 * @return mixed
 *   This return is not really intended to be used.
 */
function paymentdescriptionmembership_civicrm_alterPaymentProcessorParams($paymentObj, &$rawParams, &$cookedParams) {
  /* testing
  \Civi::log()->debug(' rawParams: {raw}\ncookedParams: {cooked}', [
    'raw' => $rawParams,
    'cooked' => $cookedParams,
    //'paymentObj' => $paymentObj
  ]);
  */

  $expected_title = 'Member Signup and Renewal';

  $modify_description = FALSE;
  $cid = NULL;
  $selectMembership = NULL;
  $renewsignup = 'Signup';

  // grab contact ID and membership type selected from rawParams
  if ($paymentObj instanceof CRM_Core_Payment_PayPalImpl) {
    // CiviCRM Core PayPal -- only tested with PayPal Web Standard
    //    Uses array for rawParams and cookedParams
    $description = $rawParams['description'];
    if (str_contains($description, $expected_title)) {
      $cid = $rawParams['contactID'];
      $selectMembership = $rawParams['selectMembership'];
    }
  }
  elseif ($paymentObj instanceof CRM_Core_Payment_Stripe) {
    // com.drastikbydesign.stripe CiviCRM extension version 6.8.2.
    //    Uses propertyBag for $rawParams and ignores $cookedParams as of 22 May 2023, 6.8.2.
    // FIXME: hard-coded for membership signup/renewal form name
    if ($rawParams->getDescription() == $expected_title) {
      $cid = $rawParams->getContactID();
      $selectMembership = $rawParams->getCustomProperty('selectMembership');
      /*
      \Civi::log()->debug('    {description}', [
        'description' => $rawParams->getDescription()
      ]);
      */
    }    
  }

  // only modify description if expected form and params needed are set
  $modify_description = ($cid and $selectMembership);
  if ($modify_description) {
    if ($cid) {
      // guess if renewal based on membership start_date existing for this contact ID
      $membership = new CRM_Member_DAO_Membership();
      $membership->contact_id = $cid;
      $membership->find(TRUE);
      if ($membership->start_date) {
        $renewsignup = 'Renewal';
      }
      /* testing
      \Civi::log()->debug('    {cid} {renewsignup} {membership}', [
        'cid' => $cid,
        'renewsignup' => $renewsignup,
        'membership' => $membership
      ]);
      */
    }
    $membershipType = CRM_Member_BAO_MembershipType::getMembershipType($selectMembership);
    if (! $membershipType) {
      \Civi::log()->debug('    membershipType not found? {selectMembership} {membershipType}', [
        'selectMembership' => $selectMembership,
        'membershipType' => $membershipType
      ]);
    }

    $newDescription = 'Member ' . $renewsignup . ': ' . $membershipType['name'];

    if ($paymentObj instanceof CRM_Core_Payment_Stripe) {
      // Stripe extension modifies more after calling alterPaymentParams hook, adding the cidXmembershipID
      $rawParams->setDescription($newDescription);
      // Stripe extension version 6.9 (30 Jun 2023) uses cookedParams arg for Stripe Checkout payment processor
      if ( !empty($cookedParams) ) {
        if ( !empty($cookedParams['payment_intent_data']) ) {
          $oldDescription = $cookedPArams['payment_intent_data']['description'];
          $cookedParams['payment_intent_data']['description'] = str_replace($expected_title, $newDescription, $oldDescription);
        }
      }
      /* testing
      \Civi::log()->debug(' AFTER Stripe rawParams: {raw}', [
        'raw' => $rawParams
      ]);
      */
    } 
    elseif ($paymentObj instanceof CRM_Core_Payment_PayPalImpl) {
      $old_item_name = $cookedParams['item_name'];
      $cookedParams['item_name'] = str_replace($expected_title, $newDescription, $old_item_name);
      /*
      \Civi::log()->debug('    AFTER PayPal cookedParams: {cooked}', [
        'cooked' => $cookedParams
      ]);
      */
    }
  }
}