<?php
class CurlBrowser {

  var $content;
  var $cookies = array();
  var $options = array(
    'header'         => false,
    'followlocation' => true,
    'returntransfer' => true,
    'ssl_verifyhost' => false,
    'ssl_verifypeer' => false
  );

  var $cookieStorage;

  function get($url) {
    unset($this->options['post']);
    $this->options['httpget'] = true;
    return $this->fetch($url);
  }

  function post($url, $data) {
    unset($this->options['httpget']);
    $this->options['post'] = true;
    if(!empty($data)) $this->options['postfields'] = $data;
    return $this->fetch($url);
  }

  function download($url, $filePath) {
    $this->options['binarytransfer'] = true;
    $this->fetch($url);
    if(file_exists($filePath)) unlink($filePath);
    $fp = fopen($filePath, 'x');
    $status = fwrite($fp, $this->content);
    fclose($fp);
    return $status;
  }

  function fetch($url) {
    $ch = curl_init($url);
    $this->configCookies();
    $this->configCurl($ch);
    $this->content = curl_exec($ch);
    curl_close($ch);
    $this->readCookies();
    return $this->content;
  }

  function configCookies() {
    if(empty($this->cookieStorage)) {
      $this->cookieStorage = dirname(__FILE__)."/cookies.txt";
      exec("touch $this->cookieStorage");
    }
    //$this->options['cookiejar']  = $this->cookieStorage;
    $this->options['cookiefile'] = $this->cookieStorage;
  }

  function configCurl($ch) {
    foreach($this->options as $option => $value)
      curl_setopt($ch, constant('CURLOPT_'.strtoupper($option)), $value);
  }

  function readCookies() {
    $content = file_get_contents($this->cookieStorage);
    foreach(explode("\n", $content) as $line) {
      if(isset($line[0]) and substr_count($line, "\t") == 6) {
        $cookieData = explode("\t", $line);
        $cookieData = array_map('trim', $cookieData);
        $this->cookies[$cookieData[5]] = $cookieData[6];
      }
    }
  }

  function __destruct() { //unlink($this->cookieStorage); 
  }
}
?>