<?php
return array(
	'exchange_rate_jp'     => 15.26, //日元人民币兑换比例
	'score_rate'           => 100, //积分人民币兑换比例
	'score_goods_rate'     => 0.07, //购买商品获得积分比例
	'activity_id'          => 1, //当前活动ID
	'goods_ratio'          => 1, //全场折扣率，不包含活动商品，注意：勿随便更改！！
	'goods_ratio_original' => 1, //原价显示的折扣率
	'goods_ratio_delivery' => 1.5, //商品价格重量比率计算是否包邮
	'goods_weight_delivery' => 300, //商品重量大于此数值时不包邮，-1为关闭
	'condition_price'      => 288, //包邮金额，[0=全场无条件包邮,-1=关闭包邮]
	
)

?>