<?php
$tokenStr = 'b306f5829b6045f8a10efacebcd5b5c12017-02-10searchOrder{"IfReturnTotal":true,"PageNo":1,"EndModified":"2016-11-23 23:59:59","StartModified":"2016-11-23 00:00:00","PageNum":10}';
$token = md5($tokenStr);
print $token;

