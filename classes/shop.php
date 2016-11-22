<?php
class Shop
{
    //关联
    function associate(){
        $user_model = new IModel('user');
        $user_model->setData(['shop_identify_id' => ISession::get('shop_identify_id'), 'shop_relation_time'=> date('Y-m-d H:i:s',time())]);
        $ret = $user_model->update('id = ' . $this->user['user_id']);
    }
}