<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Htmldom{
    public function __construct(){
        require_once APPPATH.'third_party/simple_html_dom.php';
    }
}
