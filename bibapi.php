<?php
  include('key.php');

  $mms_id = $_GET['bibid'];

  if(!($mms_id)) {
    echo 'need bibid parameter';
    }

  else {

    //validate for Alma ids, and
    //convert any Voyager IDs to Alma
    
    if (strpos($mms_id, '99') !== 0 || (substr($mms_id, -7) != '3604103')) {
      $len = strlen($mms_id);
      if ($len > 4 || $len < 9) {
        //this is a voyager id we need to change
        $mms_id = '99' . $mms_id . '3604103';
        }
      }

    $holdings_url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/bibs/' . $mms_id . '/holdings?apikey=' . $key;
    $holdings_xml = simplexml_load_file($holdings_url);
    $holdings_xpath = $holdings_xml->xpath("/holdings/holding/@link");

    $items = array();

    foreach ($holdings_xpath as $holding) {
      $item_url = $holding . '/items?apikey=' . $key;
      $items[] = $item_url;
      }

    $loans = array();

    foreach($items as $item) {
      $items_xml = simplexml_load_file($item);
      $items_xpath = $items_xml->xpath("/items/item/@link");
      foreach($items_xpath as $loan) {
        $loan_url = $loan . '/loans?apikey=' . $key;
        $loans[] = $loan_url;
        }
      }

    $loan_status = array();

    foreach($loans as $loan) {
      $loans_xml = simplexml_load_file($loan);
      $loans_xpath = $loans_xml->xpath("/item_loans/@total_record_count");
      foreach($loans_xpath as $borrow) {
        $loan_status[] = $borrow;
        }
      }

    if(in_array(0,$loan_status)) {
      echo "available";
      }
    else {
      echo "not available";
      }
    }

?>
