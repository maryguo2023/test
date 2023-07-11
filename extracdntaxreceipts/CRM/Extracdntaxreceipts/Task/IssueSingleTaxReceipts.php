<?php

require_once('CRM/Contribute/Form/Task.php');

/**
 * This class provides the common functionality for issuing CDN Tax Receipts for
 * one or a group of contact ids.
 */
class CRM_Extracdntaxreceipts_Task_IssueSingleTaxReceipts extends CRM_Contribute_Form_Task {
  private $_years;
  
  function extracdntaxreceipts_civicrm_buildForm($formName, $form) {
    ///CRM/Cdntaxreceipts/Task/IssueSingleTaxReceipts.php    function buildQuickForm()
    if ($formName == "CRM_Cdntaxreceipts_Task_IssueSingleTaxReceipts") {
      //CRM-918: Add Custom Stylesheet to pages as well
      CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.cdntaxreceipts', 'css/receipt_module.css');
      $this->assign('receiptList', $this->_receiptList);
      $this->assign('receipt_type', 'single');
      // add radio buttons
      $this->add('checkbox', 'receipt_option', ts('Also re-issue duplicates', array('domain' => 'org.civicrm.cdntaxreceipts')));
      //Add ColumnHeaders for Table of Users Section
      $columnHeaders = ['Received',
        'Name',
        'Amount',
        'Fund',
        'Campaign',
        'Source',
        'Method',
        'Status',
        'Eligibility',
      ];
      $this->assign('columnHeaders', $columnHeaders);
  
      // Add Receipt Types
      $receiptTypes = ['original', 'duplicate', 'ineligibles'];
      $this->assign('receiptTypes', $receiptTypes);
      // Add tax year as select box
      krsort($this->_years);
      foreach( $this->_years as $year ) {
        $tax_year['issue_'.$year] = $year;
      }
      if($this->_years) {
        $this->assign('defaultYear', array_values($this->_years)[0]);
      }
  
      $this->add('select', 'receipt_year',
        ts('Tax Year'),
        $tax_year,
        FALSE,
        array('class' => 'crm-select')
      );
  
      //CRM-921: Add delivery Method to form
      $delivery_method = CRM_Core_BAO_Setting::getItem(CDNTAX_SETTINGS, 'delivery_method');
      $delivery_placeholder = null;
      $delivery_options = [];
  
      $delivery_options[CDNTAX_DELIVERY_PRINT_ONLY] = 'Print';
      $delivery_options[CDNTAX_DELIVERY_PRINT_EMAIL] = 'Email';
      $this->add('select',
        'delivery_method',
        ts('Method'),
        $delivery_options,
        FALSE,
        ['class' => 'crm-select2']
      );
  
      // Add Thank-you Setting block
      $this->add('checkbox', 'thankyou_date', ts('Mark Contribution as thanked', array('domain' => 'org.civicrm.cdntaxreceipts')));
      $this->add('checkbox', 'thankyou_email', ts('Send a custom Thank You Message', array('domain' => 'org.civicrm.cdntaxreceipts')));
      $buttons = array(
        array(
          'type' => 'cancel',
          'name' => ts('Back', array('domain' => 'org.civicrm.cdntaxreceipts')),
        ),
        array(
          'type' => 'submit',
          'name' => ts('Preview', array('domain' => 'org.civicrm.cdntaxreceipts')),
          'isDefault' => FALSE,
        ),
        array(
          'type' => 'next',
          'name' => 'Issue Tax Receipts',
          'isDefault' => TRUE,
          'submitOnce' => FALSE,
        ),
      );
  
      //CRM-921: Integrate WYSWIG Editor on the form
      CRM_Contact_Form_Task_PDFLetterCommon::buildQuickForm($this);
      if($this->elementExists('from_email_address')) {
        $this->removeElement('from_email_address');
      }
      $from_email_address = current(CRM_Core_BAO_Domain::getNameAndEmail(FALSE, TRUE));
      //CRM-1596 "From Email Address" value being passed as attributes
      $this->add('text', 'from_email_address', ts('From Email Address'), array('value' => $from_email_address), TRUE);
      $this->add('text', 'email_options', ts('Print and Email Options'), array('value' => 'email'), FALSE);
      $this->add('text', 'group_by_separator', ts('Group By Seperator'), array('value' => 'comma'), FALSE);
      $defaults = [
        'margin_left' => 0.75,
        'margin_right' => 0.75,
        'margin_top' => 0.75,
        'margin_bottom' => 0.75,
        'email_options' => 'email',
        'from_email_address' => $from_email_address,
        'group_by_separator' => 'comma',
        'thankyou_date' => 1,
        'receipt_option' => 0,
      ];
      $this->setDefaults($defaults);
      $this->addButtons($buttons);
  
      //Add Tokens
      $tokens = CRM_Cdntaxreceipts_Task_PDFLetterCommon::listTokens();
      $this->assign('tokens', CRM_Utils_Token::formatTokensForDisplay($tokens));
  
      $templates = CRM_Core_BAO_MessageTemplate::getMessageTemplates(FALSE);
      if($this->elementExists('template')) {
        $this->removeElement('template');
        $this->assign('templates', TRUE);
        $this->add('select', "template", ts('Use Template'),
          ['default' => 'Default Message'] + $templates + ['0' => ts('Other Custom')], FALSE,
          ['onChange' => "selectValue( this.value, '');"]
        );
      }
    }
  }
  function extracdntaxreceipts_cdntaxreceipts_preProcess($formName, &$form) {
    //CRM/Cdntaxreceipts/Task/IssueSingleTaxReceipts.php    function preProcess()
    if ($formName == "CRM_Cdntaxreceipts_Task_IssueSingleTaxReceipts") {
      $this->_years = array();
      $receiptList = [];
      $eligible_contact_ids = [];
      foreach ( $this->_contributionIds as $id ) {
        if (!cdntaxreceipts_eligibleForReceipt($id) ) {
          $key = 'ineligibles';
        }
        $result = civicrm_api3('Contribution', 'get', [
          'sequential' => 1,
          'return' => ["contribution_source", "contribution_status_id", "payment_instrument_id", "total_amount", "financial_type_id", "contribution_page_id", "receive_date"],
          'id' => $id,
        ]);
        if($result['values'][0]['receive_date']) {
          $result['values'][0]['receive_time'] = date("h:i A", strtotime($result['values'][0]['receive_date']));
          $result['values'][0]['receive_year'] = date("Y", strtotime($result['values'][0]['receive_date']));
          $this->_years[$result['values'][0]['receive_year']] = $result['values'][0]['receive_year'];
          $result['values'][0]['receive_date'] = date("F jS, Y", strtotime($result['values'][0]['receive_date']));
        }
        if($result['values'][0]['financial_type_id']) {
          $fund = civicrm_api3('FinancialType', 'get', [
            'sequential' => 1,
            'id' => $result['values'][0]['financial_type_id'],
          ]);
          if($fund['values']) {
            $result['values'][0]['fund'] = $fund['values'][0]['name'];
          }
        }
        if($result['values'][0]['contribution_page_id']) {
          $campaign = civicrm_api3('ContributionPage', 'get', [
            'sequential' => 1,
            'id' => $result['values'][0]['contribution_page_id'],
          ]);
          if($campaign['values']) {
            $result['values'][0]['campaign'] = $campaign['values'][0]['title'];
          }
        }
        $result['values'][0]['eligible'] = true;
        if($key == 'ineligibles') {
          $result['values'][0]['eligible'] = false;
        }
        if($result['values'][0]['contact_id']) {
          $contact_details = civicrm_api3('Contact', 'getsingle', [
            'sequential' => 1,
            'id' => $result['values'][0]['contact_id'],
            'return' => ["display_name"],
          ]);
        }
        if($contact_details) {
          $receiptList[$key][$result['values'][0]['receive_year']]['contact_ids'][$result['values'][0]['contact_id']]['display_name'] = $contact_details['display_name'];
          $receiptList[$key][$result['values'][0]['receive_year']]['contact_ids'][$result['values'][0]['contact_id']]['contributions'][$result['values'][0]['id']] = $result['values'][0];
        }
        //Count the totals
        $receiptList[$key][$result['values'][0]['receive_year']]['total_contrib']++;
        $receiptList[$key][$result['values'][0]['receive_year']]['total_amount'] += $result['values'][0]['total_amount'];
        $receiptList[$key][$result['values'][0]['receive_year']]['total_amount'] = round($receiptList[$key][$result['values'][0]['receive_year']]['total_amount'], 2);
        $receiptList[$key][$result['values'][0]['receive_year']]['total_contacts'] = count($receiptList[$key][$result['values'][0]['receive_year']]['contact_ids']);
        if($key !== 'ineligibles') {
          $eligible_contact_ids[$result['values'][0]['receive_year']][] = $result['values'][0]['contact_id'];
        }
      }
      $receiptTypes = ['original', 'duplicate', 'ineligibles'];
      foreach($receiptTypes as $rtype) {
        foreach($this->_years as $year) {
          if(empty($receiptList[$rtype][$year])) {
            $receiptList[$rtype][$year]['total_contacts'] = 0;
            $receiptList[$rtype][$year]['total_contrib'] = 0;
            $receiptList[$rtype][$year]['total_amount'] = 0;
          }
        }
      }
  
      //Count Total Eligible Contacts
      if(isset($eligible_contact_ids)) {
        foreach($this->_years as $year) {
          if(!empty($eligible_contact_ids[$year])) {
            $receiptList['totals'][$year]['total_eligible_contacts'] = count(array_unique($eligible_contact_ids[$year]));
          } else {
            $receiptList['totals'][$year]['total_eligible_contacts'] = 0;
          }
        }
      }
      $this->_receiptList = $receiptList;
      $this->_receipts = $receipts;
    }
  }  
}

