<?php
use CRM_Bisnode_ExtensionUtil as E;

/**
 * BinodeFile.Import API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_bisnode_file_Import_spec(&$spec) {
#  $spec['file_id']['description'] = 'Bisnode file ID';
#  $spec['file_id']['api.required'] = 0;
  $spec['file_name']['description'] = 'Bisnode filename';
  $spec['file_name']['api.required'] = 1;
}

/**
 * BinodeFile.Import API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_bisnode_file_Import($params) {
  if (array_key_exists('file_name', $params)) {
    $filename = $params['file_name'];

    // Download file
    $loader = new CRM_Bisnode_Loader_CSV();
    $records = $loader->run($filename)['records'];
    $logs_loader = $loader->getLog();

    // Import file
    $importer = new CRM_Bisnode_Importer_CNCD(false);
    $errors = $importer->run($records);
    $logs_import = $importer->getLog();
    $logs_summary = $importer->getSummary();


    // Notify manager by email
    send_mail2contact(2, "admcrm@cncd.be",
      "[CiviBisnode] Résultat d'importation ($filename)",
      "<h1>Rapport d'importation des modifications d'adresses</h1>".
      "<h2>".ts('Summary')."</h2>".
      $logs_summary.
      "<h2>".ts('Reading')."</h2>".
      implode($logs_loader, '<br>').
      "<h2>".ts('Details')."</h2>".
      implode($logs_import, '<br>'));

    $returnValues = array("File $filename are imported succefuly.");

    return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
  }
  else {
    throw new API_Exception('Bad parameters', 1);
  }
}
