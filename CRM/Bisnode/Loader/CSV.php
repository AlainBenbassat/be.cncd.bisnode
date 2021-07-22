<?php
/*-------------------------------------------------------+
| CiviBisnode                                            |
| Copyright (C) 2019 CNCD-11.11.11                       |
| Author: Philippe Sampont                               |
| http://www.cncd.be/                                    |
+--------------------------------------------------------*/

class CRM_Bisnode_Loader_CSV {

  protected $timestamp_start;
  protected $timestamp_end;
  protected $logs;
  protected $csv_delimiter, $csv_enclosure;

  protected $filename;
  protected $header;

  function __construct() {
    $this->logs = array();
    $this->csv_delimiter= ';';
    $this->csv_enclosure= '"';
  }

  /**
   * Get logs of the last run
   */
  function getLog() {
    return $this->logs;
  }

  /**
   * Get summary of the last run
   */
  function getSummary() {
    $summary = '';

    $summary.= "<p>".ts("Start Date")." : ".$this->timestamp_start."</p>";
    $summary.= "<p>".ts("End Date")." : ".$this->timestamp_end."</p>";

    return $summary;
  }

  /**
    Manage CSV files
  */
  function read_file($filename) {
    $result = [];

    if (($import_file = fopen($filename, "r")) == FALSE) throw new exception("CVS file $filename not found !\n");

    $header = fgetcsv($import_file, 1000, $this->csv_delimiter);
    $result['header'] = $header;

    $record = [];
    while (($data = fgetcsv($import_file, 1000, $this->csv_delimiter)) !== FALSE) {
      $data = array_map("utf8_encode", $data);
      $num = count($data);
      for ($c=0; $c < $num; $c++) {
        $record[$header[$c]] = $data[$c];
      }

      $records[] = $record;
    }
    fclose($import_file);

    $result['records'] = $records;

    return $result;
  }

  function write_file($filename, $header, $errors) {

    if (($error_file = fopen($filename, "a")) == FALSE) throw new exception("Can't create file $filename !\n");

    $header['ERROR']= "Messages d'erreur";
    fputcsv($error_file, $header, $this->csv_delimiter, $this->csv_enclosure);
    foreach ($errors as $error) {
      fputcsv($error_file, $error, $this->csv_delimiter, $this->csv_enclosure);
    }

    fclose($error_file);
  }

  /***
   * Load data from file
   */
  function run($filename) {
    $this->timestamp_start= date('d-m-Y H:i:s');

    $result = $this->read_file($filename);
    $this->logs[] = count($result['records'])." records readed in $filename.";
    $this->timestamp_end= date('d-m-Y H:i:s');

    return $result;
  }

}
