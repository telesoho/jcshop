<?php
$testJson = '
{
  "success": true,
  "validate": "00000",
  "message": "成功",
  "result": {
    "Orders": [
      {
        "OrderTime": "2016-11-22 14:01:46",
        "ConsigneeName": "卢雅倩",
        "PostalTotal": 0,
        "OrderStatus": "TRADE_STATUS_FINISHED",
        "DiscountTotal": 0,
        "SendTime": "2016-12-01 14:18:01",
        "IdCard": "330727199108202241",
        "PostId": null,
        "WmsName": "香港仓",
        "Province": "浙江省",
        "ConsigneeNumber": "15268612207",
        "City": "杭州市",
        "SettleTotal": 550,
        "DetailedAddres": "浙江省杭州市下城区绍兴路161号野风现代中心南楼2216室",
        "District": "下城区",
        "OrderTotal": 550,
        "LogisticName": "圆通快递",
        "OrderItems": [
          {
            "BuyPrice": 550,
            "SupGoodsNo": "zzb079",
            "BuyQty": 1
          }
        ],
        "TaxFee": 0,
        "Remark": ""
      },
      {
        "OrderTime": "2016-11-23 13:42:42",
        "ConsigneeName": "李墨",
        "PostalTotal": 0,
        "OrderStatus": "TRADE_STATUS_FINISHED",
        "DiscountTotal": 0,
        "SendTime": "2016-12-01 14:17:31",
        "IdCard": "362430198808230061",
        "PostId": null,
        "WmsName": "香港仓",
        "Province": "湖南省",
        "ConsigneeNumber": "18676789952",
        "City": "长沙市",
        "SettleTotal": 265,
        "DetailedAddres": "湖南省长沙市岳麓区梅溪湖梅溪新秀一期3栋502",
        "District": "岳麓区",
        "OrderTotal": 265,
        "LogisticName": "圆通快递",
        "OrderItems": [
          {
            "BuyPrice": 265,
            "SupGoodsNo": "zzb099",
            "BuyQty": 1
          }
        ],
        "TaxFee": 0,
        "Remark": ""
      }
    ],
    "TotalResults": 2
  }
}
';

$a = json_decode($testJson, true);
var_dump($a);
echo json_encode($a);