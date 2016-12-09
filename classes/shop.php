<?php
class Shop
{
    //关联
    static function associate($user_id){
        $user_model = new IModel('user');
        $data = $user_model->getObj('id = ' . $user_id);
        if (!empty($data['shop_identify_id'])){

        } else {
            $user_model->setData(['shop_identify_id' => ISession::get('shop_identify_id'), 'shop_relation_time'=> date('Y-m-d H:i:s',time())]);
            $ret = $user_model->update('id = ' . $user_id);
        }
    }
    static function update_order_seller_id($order_id){
        $shop_identify_id = ISession::get('shop_identify_id');
        $order_model = new IModel('order');
        $order_model->setData(['seller_id' => $shop_identify_id]);
        $order_model->update('id = ' . $order_id);
    }
    static function settlement_shop_orders($shop_id){
        $shop_query = new IQuery('shop');
        $shop_query->where = 'id = ' . $shop_id;
        $shop_data = $shop_query->find()[0];
        $seller_id = $shop_data['identify_id'];
        $amount_available = $shop_data['amount_available'];
        $category_id = $shop_data['category_id'];
        $temp = 'is_shop_checkout = 0 and seller_id = ' . $seller_id;
        $date_interval = ' and DATE_FORMAT( completion_time, \'%Y%m\' ) = DATE_FORMAT( CURDATE( ) , \'%Y%m\' )'; //本月
        $order_data = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 5 ' . $date_interval)->find(); // 已完成
        $shop_category_query = new IQuery('shop_category');
        $shop_category_query->where = ' id = ' . $category_id;
        $shop_category_data = $shop_category_query->find();
        foreach ($order_data as $k=>$v){
            $settlement_model = new IModel('settlement');
            $rebate = $shop_category_data[0]['rebate'];
            $rebate_amount = $v['real_amount']*$shop_category_data[0]['rebate'];
            $settlement_model->setData(['order_id'=>$v['id'], 'goods_amount'=>$v['real_amount'],'rebate'=> $rebate, 'rebate_amount' => $rebate_amount,'settlement_time'=>date('Y-m-d H:i:s', time()), 'seller_id'=>$seller_id ]);
            $ret = $settlement_model->add();
            if ($ret){
                $order_model = new IModel('order');
                $order_model->setData(['is_shop_checkout' => 1]);
                $ret = $order_model->update('id = ' . $v['id']);
                if ($ret){
                    $shop_model = new IModel('shop');
                    $shop_model->setData(['amount_available' =>$amount_available+$rebate_amount ]);
                    $shop_model->update('identify_id = ' . $seller_id);
                }
            }
        }
    }
    static function settlement_recommender_orders($recommender_id){
        $shop_query = new IQuery('shop');
        $shop_query->where = 'recommender = ' . $recommender_id;
        $shop_data = $shop_query->find();
        foreach ($shop_data as $key=>$value){

            $seller_id = $value['identify_id'];
            $amount_available = $value['amount_available'];
            $category_id = $value['category_id'];

            $temp = 'is_recommender_checkout = 0 and seller_id = ' . $seller_id;
            $date_interval = ' and DATE_FORMAT( completion_time, \'%Y%m\' ) = DATE_FORMAT( CURDATE( ) , \'%Y%m\' )'; //本月
            $order_data = Api::run('getOrderList', $temp, 'pay_type != 0 and status = 5 ' . $date_interval)->find(); // 已完成

//            var_dump($value);
//            var_dump($order_data);
//            exit();

            $user_query = new IQuery('user');
            $user_query->where = ' id = ' . $recommender_id;
            $user_data = $user_query->find();

            foreach ($order_data as $k=>$v){
                $settlement_model = new IModel('settlement_recommender');
                $rebate = $user_data[0]['rebate'];
                $rebate_amount = $v['real_amount']*$user_data[0]['rebate'];
                $settlement_model->setData(['order_id'=>$v['id'], 'goods_amount'=>$v['real_amount'],'rebate'=> $rebate, 'rebate_amount' => $rebate_amount,'settlement_time'=>date('Y-m-d H:i:s', time()), 'recommender_id'=>$recommender_id ]);
                $ret = $settlement_model->add();
                if ($ret){
                    $order_model = new IModel('order');
                    $order_model->setData(['is_recommender_checkout' => 1]);
                    $ret = $order_model->update('id = ' . $v['id']);
                    if ($ret){
                        $user_model = new IModel('user');
                        $user_model->setData(['amount_available' => $amount_available+$rebate_amount ]);
                        $user_model->update('id = ' . $recommender_id);
                    }
                }
            }


        }
    }
}