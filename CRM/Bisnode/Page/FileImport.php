<?php
use CRM_Bisnode_ExtensionUtil as E;

class CRM_Bisnode_Page_FileImport extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('FileImport'));


    $file_info = isset($_FILES['uploadFile'])?$_FILES['uploadFile']:null;
    $filename = $file_info['tmp_name'];
    $errors_filename = '/tmp/'.$file_info['name'].'-errors.csv';
//  $filename = "/var/www/demo.csv";

    // Download file
    $loader = new CRM_Bisnode_Loader_CSV();
    $result = $loader->run($filename);
    $header = $result['header'];
    $records = $result['records'];

    $logs_download = $loader->getLog();


    // Import file
    $dry_run = isset($_REQUEST['dry_run'])?$_REQUEST['dry_run']:false;
    $importer = new CRM_Bisnode_Importer_CNCD($dry_run);
    $errors = $importer->run($records);
    if (count($errors) > 0 )
      $loader->write_file($errors_filename, $header, $errors);
    else
      $errors_filname = null;
    $logs_import = $importer->getLog();
    $logs_summary = $importer->getSummary();

    $this->assign('logs_download', $logs_download);
    $this->assign('logs_import', $logs_import);
    $this->assign('logs_summary', $logs_summary);
/*
    // Notify manager by email
    send_mail2contact(2, "admcrm@cncd.be",
      "[CiviBisnode] Résultat d'importation (".$file_info['name'].")",
      "<h1>Rapport d'importation des modifications d'adresses</h1>".
      "Mode test : $dry_run".
      "<h2>".ts('Summary')."</h2>".
      $logs_summary.
      "<h2>".ts('Reading')."</h2>".
      implode($logs_loader, '<br>').
      "<h2>".ts('Details')."</h2>".
      implode($logs_import, '<br>'),
      $errors_filename);
 */
    parent::run();
  }

}
