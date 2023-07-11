<?php

require_once 'extracdntaxreceipts.civix.php';
// phpcs:disable
use CRM_Extracdntaxreceipts_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function extracdntaxreceipts_civicrm_config(&$config) {
  _extracdntaxreceipts_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function extracdntaxreceipts_civicrm_install() {
  _extracdntaxreceipts_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function extracdntaxreceipts_civicrm_postInstall() {
  _extracdntaxreceipts_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function extracdntaxreceipts_civicrm_uninstall() {
  _extracdntaxreceipts_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function extracdntaxreceipts_civicrm_enable() {
  _extracdntaxreceipts_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function extracdntaxreceipts_civicrm_disable() {
  _extracdntaxreceipts_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function extracdntaxreceipts_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _extracdntaxreceipts_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function extracdntaxreceipts_civicrm_entityTypes(&$entityTypes) {
  _extracdntaxreceipts_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function extracdntaxreceipts_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function extracdntaxreceipts_civicrm_navigationMenu(&$menu) {
//  _extracdntaxreceipts_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _extracdntaxreceipts_civix_navigationMenu($menu);
//}

/**
 * Implements hook_civicrm_buildForm().
 *
 * Set a default value for an event price set field.
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function extracdntaxreceipts_civicrm_buildForm($formName, $form) {
  ///CRM/Cdntaxreceipts/Task/IssueSingleTaxReceipts.php    function buildQuickForm()
  if ($formName == "CRM_Cdntaxreceipts_Task_IssueSingleTaxReceipts") {
    $form->addRadio('receipt_layout', E::ts('Receipt Layout'), ['single' => 'Single Receipt', 'three_receipt' => 'Original Receipt + 2 duplicate copies'], [], "</br>");
    $form->setDefaults(['receipt_layout' => 'three_receipt']);
    CRM_Core_Region::instance("page-body")->add([
      'template' => 'CRM/Extracdntaxreceipts/Layout.tpl',
    ]);
  }
  if ($formName == "CRM_Cdntaxreceipts_Form_Settings") {

  }
  //CRM/Cdntaxreceipts/Form/ViewTaxReceipt.php  function buildQuickForm()
  if ($formName == "CRM_Cdntaxreceipts_Form_ViewTaxReceipt") {
    //CRM-917: Add Custom Stylesheet to pages as well
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.cdntaxreceipts', 'css/receipt_module.css');
    $buttonLabel = ts('Complete', array('domain' => 'org.civicrm.cdntaxreceipts'));
    $buttons[] = array(
      'type' => 'cancel',
      'name' => ts('Back', array('domain' => 'org.civicrm.cdntaxreceipts')),
    );
    if (CRM_Core_Permission::check( 'issue cdn tax receipts' ) ) {
      if ($this->_reissue && !$this->_isCancelled) {
        $buttons[] = array(
          'type' => 'submit',
          'name' => ts('Void Receipt', array('domain' => 'org.civicrm.cdntaxreceipts')),
          'isDefault' => FALSE,
          'class' => 'void-receipt',
        );
      } else {
        $buttons[] = array(
          'type' => 'submit',
          'name' => ts('Preview', array('domain' => 'org.civicrm.cdntaxreceipts')),
          'isDefault' => FALSE,
        );
      }
      $buttons[] = array(
        'type' => 'next',
        'name' => $buttonLabel,
        'isDefault' => TRUE,
      );
    }
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
      'thankyou_date' => 1
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
  //CRM/Cdntaxreceipts/Task/IssueAnnualTaxReceipts.php  function buildQuickForm()
  if ($formName == "CRM_Cdntaxreceipts_Task_IssueAnnualTaxReceipts") {
    //CRM-919: Add Custom Stylesheet to pages as well
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.cdntaxreceipts', 'css/receipt_module.css');
    $receipt_years = $this->_years;
    $this->assign('receiptYears', $receipt_years);
    // Add tax year as select box
    if($receipt_years) {
      foreach( $receipt_years as $year ) {
        $tax_year['issue_'.$year] = $year;
      }
    } else {
      $tax_year['issues'. date('Y')] = date('Y');
      $receipt_years[] = date('Y');
    }
    if($tax_year) {
      $this->assign('defaultYear', $receipt_years[0]);
    }
    $this->add('select', 'receipt_year',
      ts('Tax Year'),
      $tax_year,
      FALSE,
      array('class' => 'crm-select')
    );
    //CRM-919: Add delivery Method to form
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
    //CRM-919: Integrate WYSWIG Editor on the form
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
      'receipt_year' => 'issue_'.$receipt_years[0],
    ];
    $this->setDefaults($defaults);
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

/**
 * Implements hook_cdntaxreceipts_writeReceipt().
 *
 * Override the default function to switch between writing a single page VS three receipt per page.
 *
 * @param object $f
 * @param array $pdf_variables
 * @param array $receipt
 */
function extracdntaxreceipts_cdntaxreceipts_writeReceipt(&$f, $pdf_variables, $receipt) {
  if ($pdf_variables['is_duplicate']) {
    // print duplicate copy
    _cdntaxreceipts_writeReceipt($f, $pdf_variables);
  }
  else {
    // print original AND duplicate copy
    $mymargin_top = $pdf_variables['mymargin_top'];
    _cdntaxreceipts_writeReceipt($f, $pdf_variables);
    if (!empty($_POST['receipt_layout']) && $_POST['receipt_layout'] == 'three_receipt') {
      $pdf_variables["mymargin_top"] = $mymargin_top + 90;
      $pdf_variables["is_duplicate"] = TRUE;
      _cdntaxreceipts_writeReceipt($f, $pdf_variables);
      $pdf_variables["mymargin_top"] = $mymargin_top + 90 * 2;
      $pdf_variables["is_duplicate"] = TRUE;
      _cdntaxreceipts_writeReceipt($f, $pdf_variables);
    }
  }

  return [TRUE];
}
//postProcess function
function extracdntaxreceipts_cdntaxreceipts_postProcess($formName, $form) {
  //CRM/Cdntaxreceipts/Form/ViewTaxReceipt.php  function postProcess()
  if ($formName == "CRM_Cdntaxreceipts_Form_ViewTaxReceipt") {
    $params = $this->controller->exportValues($this->_name);
    // If we are cancelling the tax receipt
    if ($buttonName == '_qf_ViewTaxReceipt_submit') {
      if(!$this->_reissue) {
        $receiptsForPrinting = cdntaxreceipts_openCollectedPDF();
        $previewMode = TRUE;
        list($result, $method, $pdf) = cdntaxreceipts_issueTaxReceipt( $contribution,  $receiptsForPrinting, $previewMode  );
        if($result == TRUE) {
          cdntaxreceipts_sendCollectedPDF($receiptsForPrinting, 'Receipt-To-Print-' . (int) $_SERVER['REQUEST_TIME'] . '.pdf');
        } else {
          $statusMsg = ts('Encountered an error. Tax receipt has not been issued.', array('domain' => 'org.civicrm.cdntaxreceipts'));
          CRM_Core_Session::setStatus($statusMsg, '', 'error');
        }
      }
    }
    // issue tax receipt, or report error if ineligible
    if (cdntaxreceipts_eligibleForReceipt($contribution->id) ) {
      if($this->getElement('thankyou_email')->getValue()) {
        if($this->getElement('html_message')->getValue()) {
          if(isset($params['template'])) {
            if($params['template'] !== 'default') {
              $this->_contributionIds = [$contribution->id];
              $from_email_address = current(CRM_Core_BAO_Domain::getNameAndEmail(FALSE, TRUE));
              if($from_email_address) {
                $data = &$this->controller->container();
                $data['values']['ViewTaxReceipt']['from_email_address'] = $from_email_address;
                $data['values']['ViewTaxReceipt']['subject'] = $this->getElement('subject')->getValue();
                $data['values']['ViewTaxReceipt']['html_message'] = $this->getElement('html_message')->getValue();
                $thankyou_html = CRM_Cdntaxreceipts_Task_PDFLetterCommon::postProcessForm($this, $params);
                if($thankyou_html) {
                  if(is_array($thankyou_html)) {
                    $contribution->thankyou_html = array_values($thankyou_html)[0];
                  } else {
                    $contribution->thankyou_html = $thankyou_html;
                  }
                }
              }
            }
          }
        }
      }
      if ($result == TRUE) {
        //CRM-921: Mark Contribution as thanked if checked
        if($this->getElement('thankyou_date')->getValue()) {
          $contribution->thankyou_date = date('Y-m-d H:i:s', CRM_Utils_Time::time());
          $contribution->save();
        }
      }
    }
  }
  //CRM/Cdntaxreceipts/Task/IssueAnnualTaxReceipts.php  function postProcess()
  if ($formName == "CRM_Cdntaxreceipts_Task_IssueAnnualTaxReceipts") {
    $buttonName = $this->controller->getButtonName();
    if($buttonName == '_qf_IssueAnnualTaxReceipts_submit') {
      $previewMode = TRUE;
    }
    foreach ($this->_contactIds as $contactId ) {
      if ( empty($issuedOn) && count($contributions) > 0 ) {
        //CRM-919: Thank-you Email Tool
        if($this->getElement('thankyou_email')->getValue()) {
          if($this->getElement('html_message')->getValue()) {
            if(isset($params['template'])) {
              if($params['template'] !== 'default') {
                $this->_contributionIds = array_column($contributions, 'contribution_id');
                $from_email_address = current(CRM_Core_BAO_Domain::getNameAndEmail(FALSE, TRUE));
                if($from_email_address) {
                  $data = &$this->controller->container();
                  $data['values']['ViewTaxReceipt']['from_email_address'] = $from_email_address;
                  $data['values']['ViewTaxReceipt']['subject'] = $this->getElement('subject')->getValue();
                  $data['values']['ViewTaxReceipt']['html_message'] = $this->getElement('html_message')->getValue();
                  //CRM-1792 Adding 'group_by' parameter for token processor to process grouped contributions
                  if(count($contributions) > 1)
                  {
                    $params['group_by'] = 'contact_id';
                  }
                  $thankyou_html = CRM_Cdntaxreceipts_Task_PDFLetterCommon::postProcessForm($this, $params);
                  if($thankyou_html) {
                    if(is_array($thankyou_html)) {
                      $thankyou_html = array_values($thankyou_html)[0];
                    } else {
                      $thankyou_html = $thankyou_html;
                    }
                  }
                }
              }
            }
          }
        }
        list( $ret, $method ) = cdntaxreceipts_issueAnnualTaxReceipt($contactId, $year, $receiptsForPrinting, $previewMode, $thankyou_html);

        if( $ret !== 0 ) {
          //CRM-919: Mark Contribution as thanked if checked
          if($this->getElement('thankyou_date')->getValue()) {
            foreach($contributions as $contributionIds) {
              $contribution = new CRM_Contribute_DAO_Contribution();
              $contribution->id = $contributionIds['contribution_id'];
              if ( ! $contribution->find( TRUE ) ) {
                CRM_Core_Error::fatal( "CDNTaxReceipts: Could not find corresponding contribution id." );
              }
              $contribution->thankyou_date = date('Y-m-d H:i:s', CRM_Utils_Time::time());
              $contribution->save();
            }
          }
        }
      }
    }
  }
  ///CRM/Cdntaxreceipts/Task/IssueSingleTaxReceipts.php    function buildQuickForm()
  if ($formName == "CRM_Cdntaxreceipts_Task_IssueSingleTaxReceipts") {
    $originalOnly = TRUE;
    if ($params['receipt_option']) {
      $originalOnly = FALSE;
    }
    $buttonName = $this->controller->getButtonName();
    if($buttonName == '_qf_IssueSingleTaxReceipts_submit') {
      $previewMode = TRUE;
    }
    foreach ($this->_contributionIds as $item => $contributionId) {
      // Only process Contributions of selected Year
      if($contribution->receive_date) {
        $receive_year = 'issue_'.date("Y", strtotime($contribution->receive_date));
        if($receive_year !== $params['receipt_year']) {
          continue;
        }
      }

      // 2. If Contribution is eligible for receipting, issue the tax receipt.  Otherwise ignore.
      if ( cdntaxreceipts_eligibleForReceipt($contribution->id) ) {

        list($issued_on, $receipt_id) = cdntaxreceipts_issued_on($contribution->id);
        if ( empty($issued_on) || ! $originalOnly ) {
          //CRM-918: Thank-you Email Tool
          if($this->getElement('thankyou_email')->getValue()) {
            if($this->getElement('html_message')->getValue()) {
              if(isset($params['template'])) {
                if($params['template'] !== 'default') {
                  $this->_contributionIds = [$contribution->id];
                  $from_email_address = current(CRM_Core_BAO_Domain::getNameAndEmail(FALSE, TRUE));
                  if($from_email_address) {
                    $data = &$this->controller->container();
                    $data['values']['ViewTaxReceipt']['from_email_address'] = $from_email_address;
                    $data['values']['ViewTaxReceipt']['subject'] = $this->getElement('subject')->getValue();
                    $data['values']['ViewTaxReceipt']['html_message'] = $this->getElement('html_message')->getValue();
                    $thankyou_html = CRM_Cdntaxreceipts_Task_PDFLetterCommon::postProcessForm($this, $params);
                    if($thankyou_html) {
                      if(is_array($thankyou_html)) {
                        $contribution->thankyou_html = array_values($thankyou_html)[0];
                      } else {
                        $contribution->thankyou_html = $thankyou_html;
                      }
                    }
                  }
                }
              }
            }
          }
          list( $ret, $method ) = cdntaxreceipts_issueTaxReceipt( $contribution, $receiptsForPrinting, $previewMode );
          if( $ret !== 0 ) {
            //CRM-918: Mark Contribution as thanked if checked
            if($this->getElement('thankyou_date')->getValue()) {
              $contribution->thankyou_date = date('Y-m-d H:i:s', CRM_Utils_Time::time());
              $contribution->save();
            }
          }
        }
      }
    }
    // 3. Set session status
    if(!$previewMode) {
      if ($emailCount > 0) {
        $status = ts('%1 tax receipt(s) were sent by email.', array(1=>$emailCount, 'domain' => 'org.civicrm.cdntaxreceipts'));
        CRM_Core_Session::setStatus($status, '', 'success');
      }
      if ($printCount > 0) {
        $status = ts('%1 tax receipt(s) need to be printed.', array(1=>$printCount, 'domain' => 'org.civicrm.cdntaxreceipts'));
        CRM_Core_Session::setStatus($status, '', 'success');
      }
      if ($dataCount > 0) {
        $status = ts('Data for %1 tax receipt(s) is available in the Tax Receipts Issued report.', array(1=>$dataCount, 'domain' => 'org.civicrm.cdntaxreceipts'));
        CRM_Core_Session::setStatus($status, '', 'success');
      }
    }
  }
}




