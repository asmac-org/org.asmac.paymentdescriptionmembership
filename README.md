# ASMAC Payment Description Show Renewal and Membership Type (org.asmac.paymentdescriptionmembership)

Modifies the description sent to PayPal Web Standard and Stripe payment processors for `Member Signup and Renewal` to show `Member Renewal: Full Membership`. So, when looking at PayPal and Stripe transactions, one can see what the transaction was for instead of that vague forum title of `Member Signup and Renewal`. 

On the PayPal side, future membership signups should have a description like one of these:

	527-1322-Member Renewal: Full Membership
	3019-1068-Member Signup: Student Membership

On the Stripe side, the descriptions currently look like:

	Member Renewal: Student Membership 783X1261 #48abe900de…

In an ideal world, the other spots that reference `Member Signup and Renewal` as text for that contribution page would also indicate the signup or renewal with the selected membership type. If that happens—where the `description` ends up with that information—then this extension will no longer be needed for this feature and it would work across all payment processors.

This has only been tested with the [Stripe extension](https://lab.civicrm.org/extensions/stripe) 6.8.2 and CiviCRM 5.61 with the Core [PayPal Web Standard](https://docs.civicrm.org/sysadmin/en/latest/setup/payment-processors/paypal-standard/) payment processors. Expect that it'll probably work with the other Core PayPal payment processors. Will need modifications to work with other payment processors.

The extension is licensed under [BSD-2-Clause](LICENSE.txt).

## Requirements

* PHP v7.4+ (but only tested on PHP 8.0.28)
* CiviCRM 5.61+
* Only works with the Contribution page with a title of `Member Signup and Renewal`.

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl org.asmac.paymentdescriptionmembership@https://github.com/asmac-org/org.asmac.paymentdescriptionmembership/archive/master.zip
```


## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/asmac-org/org.asmac.paymentdescriptionmembership.git
cv en paymentdescriptionmembership
```

## Getting Started

Install and enable CiviCRM extension. There are no settings. This is just a hook.

With Stripe or PayPal Web Standard enabled for the `Member Signup and Renewal` contribution page, try a test link membership signup or renewal. Ideally, you should see one of the examples above.

## Known Issues

Only works with the [Stripe extension](https://lab.civicrm.org/extensions/stripe) 6.8.2 and CiviCRM with the Core [PayPal Web Standard](https://docs.civicrm.org/sysadmin/en/latest/setup/payment-processors/paypal-standard/) payment processors. Hardcoded to the processors and the Contribution page named `Member Signup and Renewal`.

This will not work with the **PayPal Checkout** in [Omnipay Multi Processor Payment Processor For CiviCRM](https://github.com/eileenmcnaughton/nz.co.fuzion.omnipaymultiprocessor) extension. The info about `contactID` and `selectedMembership` is not provided in the `rawParams` passed to the [`alterPaymentProcessorParams` hook](https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterPaymentProcessorParams/).