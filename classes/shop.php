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
}