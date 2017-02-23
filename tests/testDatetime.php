<?php
function getOrderId($datetime) {
    $orderTime = date_create_from_format("Y-m-d H:i:s", $datetime);
    // $orderTime = date_parse_from_format("Y-m-d H:i:s", $datetime);
    // var_dump($orderTime);
    $mobile = '111111111111';
    $orderId = "NS" . $orderTime->format("YmdHis") . substr($mobile, -4);
    return $orderId;
}

$id = getOrderId("2012-12-12 23:12:22");
print $id;

