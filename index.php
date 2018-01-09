<?php

include "area.php";
//淘宝收货地址页面
$js_url='https://g.alicdn.com//vip/address/6.0.14/index-min.js';

$c=new area();
$c->setUrl($js_url);
$c->setIsCountry(true);
$c->setMakeCsv(true);
$c->process();

echo "make SUCCESS";