<?php
use CRM_Stripepaymentreport_ExtensionUtil as E;

/**
 * Job.Stripepaymentschedule API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_Stripepaymentschedule_spec(&$spec) {
  // $spec['magicword']['api.required'] = 1;
}

/**
 * Job.Stripepaymentschedule API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_job_Stripepaymentschedule($params) {
  $contributions=[];
  $resultsActivity=[];
  
  try {
    
    //get contact id, payment process type, email address, total amount, month, site name
    
    $contributions = \Civi\Api4\Contribution::get()
      ->addSelect('contact_id', 'payment_processor.payment_processor_type_id', 'email.email', 'SUM(total_amount)', 'EXTRACT(YEAR_MONTH FROM receive_date)', 'financial_type.name')
      ->addJoin('FinancialTrxn AS financial_trxn', 'LEFT', ['financial_trxn.trxn_id', '=', 'trxn_id'])
      ->addJoin('PaymentProcessor AS payment_processor', 'LEFT', ['payment_processor.id', '=', 'financial_trxn.payment_processor_id'])
      ->addJoin('PaymentProcessorType AS payment_processor_type', 'LEFT', ['payment_processor_type.id', '=', 'payment_processor.payment_processor_type_id'])
      ->addJoin('Email AS email', 'LEFT', ['contact_id', '=', 'email.contact_id'])
      ->addJoin('FinancialType AS financial_type', 'LEFT', ['financial_type.id', '=', 'financial_type_id'])
      ->addGroupBy('contact_id')
      ->addGroupBy('financial_type.name')
      ->addGroupBy('EXTRACT(YEAR_MONTH FROM receive_date)')
      ->addGroupBy('email.email')
      ->addGroupBy('payment_processor.payment_processor_type_id')
      ->addWhere('trxn_id', 'IS NOT NULL')
      ->addWhere('invoice_id', 'IS NOT NULL')
      ->addWhere('contribution_status_id', '=', 1)
      ->addWhere('payment_processor_type.name', '=', 'Stripe')
      ->addWhere('financial_trxn.is_payment', '=', TRUE)
      ->execute();

    Civi::log()->debug("contribution result is: ".$contributions);  
    // return $contributions;
    // CRM_Core_Error::debug('contribution information', $contributions);
    
    foreach ($contributions as $contribution) {
      // create activities 
      $resultsActivity = \Civi\Api4\Activity::create()
        ->addValue('source_contact_id', $contribution['contact_id'])
        ->addValue('subject', 'contact id: '.$contribution['contact_id'].' contact email: '.$contribution['email.email'].' and month is '.$contribution['EXTRACT:receive_date'].' and financial type: '.$contribution['financial_type.name'].' total amount: '.$contribution['SUM:total_amount'])
        ->addValue('Stripe_Monthly_Total_Amount.Stripe_Monthly_Total_Amount',$contribution['SUM:total_amount'])
        ->addValue('activity_date_time', date('Y-m-d H:i:s'))
        ->addValue('status_id', 1)
        ->addValue('activity_type_id', 56) //56 is activity type id=56 and type name is Stripe Payments
        ->addValue('priority_id', 2)
        ->execute();
      Civi::log()->debug("activity result is: ".$resultsActivity);  
    }
    // exit();
  }
  //catch exception
  catch(Exception $e) {
    Civi::log()->debug("result is: ".$e->getMessage());
    echo 'Message: ' .$e->getMessage();
  }
  // ALTERNATIVE: $returnValues = []; // OK, success
  // ALTERNATIVE: $returnValues = ["Some value"]; // OK, return a single value

  // Spec: civicrm_api3_create_success($values = 1, $params = [], $entity = NULL, $action = NULL)
  return civicrm_api3_create_success($contributions, $params, 'Job', 'Stripepaymentschedule');
  
}
