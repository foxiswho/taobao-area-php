<?php

include "area.php";
include "area_ext.php";
//淘宝收货地址页面
$js_url='https://g.alicdn.com/vip/address/6.0.14/index-min.js';

//生成 SQL 和CSV
$c=new area();
$c->setUrl($js_url);
$c->setIsCountry(true);
$c->setMakeCsv(true);
$c->setExtData($ext);
$c->process();


// 生成 JS DATA

$c=new area();
$c->setUrl($js_url);
$c->setIsCountry(false);
$c->setMakeCsv(false);
$c->setMakeSql(false);
$c->setMakeJsData(true);
$c->setExtData($ext);
$c->process();