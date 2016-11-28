<?php
/**
 * @copyright Copyright(c) 2011 aircheng.com
 * @file site.php
 * @brief
 * @author webning
 * @date 2011-03-22
 * @version 0.6
 * @note
 */
/**
 * @brief Site
 * @class Site
 * @note
 */
class Site extends IController
{
    public $layout='site';

	function init()
	{
		//必须微信客户端
// 		$isWechat 				= IClient::isWechat();
// 		if($isWechat == false) exit('请使用微信访问我们的页面：）');
//        $action = IFilter::act(IReq::get('action'),'string');
//        if ($action!='article_detail' || $action='index'){ISession::clear('visit_num');}
	}

	function index()
	{
	    if ($this->user['user_id']){
	        $user_own_shop_data = $this->get_user_own_shop_data();
            if (!empty($user_own_shop_data)){
                ISession::set('shop_name',$user_own_shop_data['name']);
                ISession::set('shop_identify_id',$user_own_shop_data['identify_id']);
            } else {
                $user_rel_shop_data = $this->get_user_rel_shop_data();
                if ($user_rel_shop_data){
                    ISession::set('shop_name',$user_rel_shop_data['name']);
                    ISession::set('shop_identify_id',$user_rel_shop_data['identify_id']);
                } else {
                    $identify_id = IFilter::act(IReq::get('iid'),'int');
                    if ($identify_id){
                        $shop_data = $this->get_shop_data_by_identify_id($identify_id);
                        ISession::set('shop_name',$shop_data['name']);
                        ISession::set('shop_identify_id',$shop_data['identify_id']);
                        $if_shop_register = $this->if_shop_register($identify_id);
                        if ($if_shop_register){

                        } else {
                            $shop_model = new IModel('shop');
                            $shop_model->setData(['own_id'=>$this->user['user_id']]);
                            $ret = $shop_model->update('identify_id='.$identify_id);
                            if ($ret){
                                $user_model = new IModel('user');
                                $user_model->setData(['shop_identify_id' => $identify_id]);
                                $user_model->update('id = ' . $this->user['user_id']);
                            }
                        }
                    }
                }
            }
        }
        //用户登陆


        if (empty($_SERVER['REDIRECT_PATH_INFO'])){
            ISession::set('is_first',true);
        }
//		$this->index_slide = Api::run('getBannerList');
		$this->redirect('index');
	}

	private function get_user_own_shop_data(){
        $user_query = new IQuery('user as a');
        $user_query->join = 'right join shop as b on a.id = b.own_id';
        $user_query->where = 'a.id = ' . $this->user['user_id'];
        $user_shop_data = !empty($user_query->find()) ? $user_query->find()[0] : null;
        return $user_shop_data;
    }
    private function get_user_rel_shop_data(){
        $user_query = new IQuery('user as a');
        $user_query->join = 'inner join shop as b on a.shop_identify_id = b.identify_id';
        $user_query->where = 'a.id = ' . $this->user['user_id'];
        $user_rel_shop_data = !empty($user_query->find()) ? $user_query->find()[0] : null;
        return $user_rel_shop_data;
    }
    private function if_shop_register($identify_id){
        $shop_query = new IQuery('shop as a');
        $shop_query->join = 'inner join user as b on a.own_id = b.id';
        $shop_query->where = 'identify_id = ' . $identify_id;
        if (!empty($shop_query->find())){
            return true;
        } else {
            return false;
        }
    }
    private function get_shop_data_by_identify_id($identify_id){
        $shop_query = new IQuery('shop');
        $shop_query->where = 'identify_id = ' . $identify_id;
        $shop_data = !empty($shop_query->find()) ? $shop_query->find()[0] : null;
        return $shop_data;
    }

	//[首页]商品搜索
	function search_list()
	{
//		$this->word = IFilter::act(IReq::get('word'),'text');
//		$cat_id     = IFilter::act(IReq::get('cat'),'int');
//
//		if(preg_match("|^[\w\x7f\s*-\xff*]+$|",$this->word))
//		{
//			//搜索关键字
//			$tb_sear     = new IModel('search');
//			$search_info = $tb_sear->getObj('keyword = "'.$this->word.'"','id');
//
//			//如果是第一页，相应关键词的被搜索数量才加1
//			if($search_info && intval(IReq::get('page')) < 2 )
//			{
//				//禁止刷新+1
//				$allow_sep = "30";
//				$flag = false;
//				$time = ICookie::get('step');
//				if(isset($time))
//				{
//					if (time() - $time > $allow_sep)
//					{
//						ICookie::set('step',time());
//						$flag = true;
//					}
//				}
//				else
//				{
//					ICookie::set('step',time());
//					$flag = true;
//				}
//				if($flag)
//				{
//					$tb_sear->setData(array('num'=>'num + 1'));
//					$tb_sear->update('id='.$search_info['id'],'num');
//				}
//			}
//			elseif( !$search_info )
//			{
//				//如果数据库中没有这个词的信息，则新添
//				$tb_sear->setData(array('keyword'=>$this->word,'num'=>1));
//				$tb_sear->add();
//			}
//		}
//		else
//		{
//			IError::show(403,'请输入正确的查询关键词');
//		}
//		$this->cat_id = $cat_id;
		$this->redirect('search_list');
	}

	//[site,ucenter头部分]自动完成
	function autoComplete()
	{
		$word = IFilter::act(IReq::get('word'));
		$isError = true;
		$data    = array();

		if($word != '' && $word != '%' && $word != '_')
		{
			$wordObj  = new IModel('keyword');
			$wordList = $wordObj->query('word like "'.$word.'%" and word != "'.$word.'"','word, goods_nums','',10);

			if(!empty($wordList))
			{
				$isError = false;
				$data = $wordList;
			}
		}

		//json数据
		$result = array(
			'isError' => $isError,
			'data'    => $data,
		);

		echo JSON::encode($result);
	}

	//[首页]邮箱订阅
	function email_registry()
	{
		$email  = IReq::get('email');
		$result = array('isError' => true);

		if(!IValidate::email($email))
		{
			$result['message'] = '请填写正确的email地址';
		}
		else
		{
			$emailRegObj = new IModel('email_registry');
			$emailRow    = $emailRegObj->getObj('email = "'.$email.'"');

			if(!empty($emailRow))
			{
				$result['message'] = '此email已经订阅过了';
			}
			else
			{
				$dataArray = array(
					'email' => $email,
				);
				$emailRegObj->setData($dataArray);
				$status = $emailRegObj->add();
				if($status == true)
				{
					$result = array(
						'isError' => false,
						'message' => '订阅成功',
					);
				}
				else
				{
					$result['message'] = '订阅失败';
				}
			}
		}
		echo JSON::encode($result);
	}

	//[列表页]商品
	function pro_list()
	{
		$this->catId = IFilter::act(IReq::get('cat'),'int');//分类id

		switch($this->catId){
			case 126:
				$this->name 	= '药妆'; //个性美妆
				$this->ac_id 	= 15;
				$this->pic 		= 'gou';
				$this->title 	= '狗子推荐';
				break;
			case 134:
				$this->name 	= '个护'; //基础护肤
				$this->ac_id 	= 18;
				$this->pic 		= 'nai';
				$this->title 	= '奶瓶推荐';
				break;
			case 6:
				$this->name 	= '宠物'; //宠物用品
				$this->ac_id 	= 17;
				$this->pic 		= 'tui';
				$this->title 	= '腿毛推荐';
				break;
			case 2:
				$this->name 	= '健康'; //居家药品
				$this->ac_id 	= 16;
				$this->pic 		= 'xi';
				$this->title 	= '昔君推荐';
				break;
			case 7:
				$this->name 	= '零食'; //日式美食
				$this->ac_id 	= 19;
				$this->pic 		= 'yi';
				$this->title 	= '一哥推荐';
				break;
			default:
				IError::show(403,'分类不存在');
		}
		/* 专辑 */
		$db_article 					= new IQuery('article as m');
		$db_article->join 				= 'left join article_category as c on c.id=m.category_id';
		$db_article->where 				= 'm.visibility=1 and m.category_id='.$this->ac_id;
		$db_article->fields 			= 'm.id,m.title,m.visit_num,m.favorite,m.image,c.name';
		$db_article->order 				= 'm.top desc,m.sort desc';
		$db_article->limit 				= 2;
		$data_article 					= $db_article->find();
		if(!empty($data_article)){
			$db_goods 					= new IQuery('goods as m');
			$db_goods->join 			= 'left join relation as r on r.goods_id=m.id';
			$db_goods->fields 			= 'm.id,m.name,m.sell_price,m.img';
			$db_goods->order 			= 'm.sort desc';
			$db_goods->limit 			= 1000;
			$db_favorite 				= new IQuery('favorite_article');
			$db_favorite->field 		= 'count(id)';
			foreach($data_article as $k => $v){
				$data_article[$k]['image'] 		= IWeb::$app->config['image_host'] .'/'. $v['image'];
				//是否已收藏
				$data_article[$k]['is_favorite'] 	= 0;
				if(!empty($this->user['user_id'])){
					$db_favorite->where 		= 'aid='.$v['id'].' and user_id='.$this->user['user_id'];
					$data_favorite 				= $db_favorite->find();
					if(!empty($data_favorite)) $data_article[$k]['is_favorite'] = 1;
				}
				//相关商品
				$db_goods->where 				= 'm.is_del=0 and r.article_id='.$v['id'];
				$goods_list 					= $db_goods->find();
				if(!empty($goods_list)){
					foreach($goods_list as $k1 => $v1){
						$goods_list[$k1]['img'] 		= IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v1['img']."/w/240/h/240");
					}
				}
				$data_article[$k]['goods_list'] = $goods_list;
			}
		}
		//商品分类
		$this->childId 					= goods_class::catChild($this->catId);
		
		/* 品牌 */
		//关联的品牌分类
		$db_brand_category 				= new IQuery('brand_category');
		$db_brand_category->where 		= 'goods_category_id in ('.$this->childId.')';
		$db_brand_category->fields 		= 'id';
		$db_brand_category->limit 		= 1000;
		$data_brand_category 			= $db_brand_category->find();
		$data_brand 					= array();
		if(!empty($data_brand_category)){
			//关联的品牌
			$db_brand 					= new IQuery('brand');
			$where 						= '';
			foreach($data_brand_category as $k => $v){
				$where 		.= 'category_ids like "%,'.$v['id'].',%"';
				if(count($data_brand_category)-1 != $k) $where .= ' OR ';
			}
			$db_brand->where 			= $where;
			$db_brand->fields 			= 'id,name,logo,url';
			$db_brand->order 			= 'sort desc';
			$db_brand->limit 			= 20;
			$data_brand 				= $db_brand->find();
			if(!empty($data_brand)){
				foreach ($data_brand as $k => $v){
					$data_brand[$k]['logo'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$v['logo']."/w/200/h/120");
					if(empty($v['logo'])) unset($data_brand[$k]);
				}
			}
		}

		/* 模板赋值 */
		$this->article_list 		= $data_article; 	//文章列表
		$this->data_brand 			= $data_brand; 		//品牌
		$this->redirect('pro_list');
	}
	//咨询
	function consult()
	{
		$this->goods_id = IFilter::act(IReq::get('id'),'int');
		if($this->goods_id == 0)
		{
			IError::show(403,'缺少商品ID参数');
		}

		$goodsObj   = new IModel('goods');
		$goodsRow   = $goodsObj->getObj('id = '.$this->goods_id);
		if(!$goodsRow)
		{
			IError::show(403,'商品数据不存在');
		}

		//获取次商品的评论数和平均分
		$goodsRow['apoint'] = $goodsRow['comments'] ? round($goodsRow['grade']/$goodsRow['comments']) : 0;

		$this->goodsRow = $goodsRow;
		$this->redirect('consult');
	}

	//咨询动作
	function consult_act()
	{
		$goods_id   = IFilter::act(IReq::get('goods_id','post'),'int');
		$captcha    = IFilter::act(IReq::get('captcha','post'));
		$question   = IFilter::act(IReq::get('question','post'));
		$_captcha   = ISafe::get('captcha');
		$message    = '';

    	if(!$captcha || !$_captcha || $captcha != $_captcha)
    	{
    		$message = '验证码输入不正确';
    	}
    	else if(!$question)
    	{
    		$message = '咨询内容不能为空';
    	}
    	else if(!$goods_id)
    	{
    		$message = '商品ID不能为空';
    	}
    	else
    	{
    		$goodsObj = new IModel('goods');
    		$goodsRow = $goodsObj->getObj('id = '.$goods_id);
    		if(!$goodsRow)
    		{
    			$message = '不存在此商品';
    		}
    	}

		//有错误情况
    	if($message)
    	{
    		IError::show(403,$message);
    	}
    	else
    	{
			$dataArray = array(
				'question' => $question,
				'goods_id' => $goods_id,
				'user_id'  => isset($this->user['user_id']) ? $this->user['user_id'] : 0,
				'time'     => ITime::getDateTime(),
			);
			$referObj = new IModel('refer');
			$referObj->setData($dataArray);
			$referObj->add();
			plugin::trigger('setCallback','/site/products/id/'.$goods_id);
			$this->redirect('/site/success');
    	}
	}

	//公告详情页面
	function notice_detail()
	{
		$this->notice_id = IFilter::act(IReq::get('id'),'int');
		if($this->notice_id == '')
		{
			IError::show(403,'缺少公告ID参数');
		}
		else
		{
			$noObj           = new IModel('announcement');
			$this->noticeRow = $noObj->getObj('id = '.$this->notice_id);
			if(empty($this->noticeRow))
			{
				IError::show(403,'公告信息不存在');
			}
			$this->redirect('notice_detail');
		}
	}

	//文章列表页面
	function article()
	{
		$catId  = IFilter::act(IReq::get('id'),'int');
		$catRow = Api::run('getArticleCategoryInfo',$catId);
		$queryArticle = $catRow ? Api::run('getArticleListByCatid',$catRow['id']) : Api::run('getArticleList');
		$this->setRenderData(array("catRow" => $catRow,'queryArticle' => $queryArticle));
		$this->redirect('article');
	}

	//文章详情页面
	function article_detail()
	{
        if(IClient::isWechat() == true){
            require_once __DIR__ . '/../plugins/wechat/wechat.php';
            $this->wechat = new wechat();
        }

        $this->action = IFilter::act(IReq::get('action'),'string');
        if ($this->action == 'article_detail'){
            ISession::set('is_first',false);
        }
		$this->article_id = IFilter::act(IReq::get('id'),'int');
		$this->vn = IFilter::act(IReq::get('vn'),'int');
        ISession::clear('visit_article_id');
        ISession::set('visit_article_id', $this->article_id.','.$this->vn.','.$this->xb);
		if($this->article_id == '')
		{
			IError::show(403,'缺少咨询ID参数');
		}
		else
		{
			$articleObj       = new IModel('article');
			$this->articleRow = $articleObj->getObj('id = '.$this->article_id);
//            var_dump($this->articleRow);
			if(empty($this->articleRow))
			{
				IError::show(403,'资讯文章不存在');
				exit;
			}
            $articleObj->setData(array("visit_num"=>$this->articleRow['visit_num']+1));
			$articleObj->update('id = '.$this->article_id);
//            $this->articleRow['link'] = IWeb::$app->config['image_host'] . '/site/article_detail/id/' . $this->article_id;
//            $this->articleRow['share_img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$this->articleRow['image']."/w/200/h/200");
			//关联商品
			$this->relationList = Api::run('getArticleGoods',array("#article_id#",$this->article_id));
            if (in_array($this->articleRow['category_id'],['10','11','12','13','14'])){
                ISession::set('tbtj_visited',true);
            } else {
                ISession::set('tbtj_visited',false);
            }
			$this->redirect('article_detail');
		}
	}

	//商品展示
	function products()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');

		if(!$goods_id)
		{
			IError::show(403,"传递的参数不正确");
			exit;
		}

		//使用商品id获得商品信息
		$tb_goods = new IModel('goods');
		$goods_info = $tb_goods->getObj('id='.$goods_id." AND is_del=0");
		if(!$goods_info)
		{
			IError::show(403,"这件商品不存在");
			exit;
		}

		//品牌名称
		if($goods_info['brand_id'])
		{
			$tb_brand = new IModel('brand');
			$brand_info = $tb_brand->getObj('id='.$goods_info['brand_id']);
			if($brand_info)
			{
				$goods_info['brand'] = $brand_info['name'];
			}
		}

		//获取商品分类
		$categoryObj = new IModel('category_extend as ca,category as c');
		$categoryList= $categoryObj->query('ca.goods_id = '.$goods_id.' and ca.category_id = c.id','c.id,c.name','ca.id desc',1);
		$categoryRow = null;
		if($categoryList)
		{
			$categoryRow = current($categoryList);
		}
		$goods_info['category'] = $categoryRow ? $categoryRow['id'] : 0;

		//商品图片
		$tb_goods_photo = new IQuery('goods_photo_relation as g');
		$tb_goods_photo->fields = 'p.id AS photo_id,p.img ';
		$tb_goods_photo->join = 'left join goods_photo as p on p.id=g.photo_id ';
		$tb_goods_photo->where =' g.goods_id='.$goods_id;
		$goods_info['photo'] = $tb_goods_photo->find();

		//商品是否参加促销活动(团购，抢购)
		$goods_info['promo']     = IReq::get('promo')     ? IReq::get('promo') : '';
		$goods_info['active_id'] = IReq::get('active_id') ? IFilter::act(IReq::get('active_id'),'int') : 0;
		if($goods_info['promo'])
		{
			$activeObj    = new Active($goods_info['promo'],$goods_info['active_id'],$this->user['user_id'],$goods_id);
			$activeResult = $activeObj->data();
			if(is_string($activeResult))
			{
				IError::show(403,$activeResult);
			}
			else
			{
				$goods_info[$goods_info['promo']] = $activeResult;
			}
		}

		//获得扩展属性
		$tb_attribute_goods = new IQuery('goods_attribute as g');
		$tb_attribute_goods->join  = 'left join attribute as a on a.id=g.attribute_id ';
		$tb_attribute_goods->fields=' a.name,g.attribute_value ';
		$tb_attribute_goods->where = "goods_id='".$goods_id."' and attribute_id!=''";
		$goods_info['attribute'] = $tb_attribute_goods->find();

		//购买记录
		$tb_shop = new IQuery('order_goods as og');
		$tb_shop->join = 'left join order as o on o.id=og.order_id';
		$tb_shop->fields = 'count(*) as totalNum';
		$tb_shop->where = 'og.goods_id='.$goods_id.' and o.status = 5';
		$shop_info = $tb_shop->find();
		$goods_info['buy_num'] = 0;
		if($shop_info)
		{
			$goods_info['buy_num'] = $shop_info[0]['totalNum'];
		}

		//购买前咨询
		$tb_refer    = new IModel('refer');
		$refeer_info = $tb_refer->getObj('goods_id='.$goods_id,'count(*) as totalNum');
		$goods_info['refer'] = 0;
		if($refeer_info)
		{
			$goods_info['refer'] = $refeer_info['totalNum'];
		}

		//网友讨论
		$tb_discussion = new IModel('discussion');
		$discussion_info = $tb_discussion->getObj('goods_id='.$goods_id,'count(*) as totalNum');
		$goods_info['discussion'] = 0;
		if($discussion_info)
		{
			$goods_info['discussion'] = $discussion_info['totalNum'];
		}

		//获得商品的价格区间
		$tb_product = new IModel('products');
		$product_info = $tb_product->getObj('goods_id='.$goods_id,'max(sell_price) as maxSellPrice ,max(market_price) as maxMarketPrice');
		if(isset($product_info['maxSellPrice']) && $product_info['maxSellPrice'])
		{
			$goods_info['sell_price']   .= "-".$product_info['maxSellPrice'];
			$goods_info['market_price'] .= "-".$product_info['maxMarketPrice'];
		}

		//获得会员价
		$countsumInstance = new countsum();
		$goods_info['group_price'] = $countsumInstance->getGroupPrice($goods_id,'goods');

		//获取商家信息
		if($goods_info['seller_id'])
		{
			$sellerDB = new IModel('seller');
			$goods_info['seller'] = $sellerDB->getObj('id = '.$goods_info['seller_id']);
		}
		//商品封面
		$goods_info['img'] = IWeb::$app->config['image_host'] . IUrl::creatUrl("/pic/thumb/img/".$goods_info['img']."/w/240/h/240");

		//增加浏览次数
		$visit    = ISafe::get('visit');
		$checkStr = "#".$goods_id."#";
		if($visit && strpos($visit,$checkStr) !== false)
		{
		}
		else
		{
			$tb_goods->setData(array('visit' => 'visit + 1'));
			$tb_goods->update('id = '.$goods_id,'visit');
			$visit = $visit === null ? $checkStr : $visit.$checkStr;
			ISafe::set('visit',$visit);
		}

		$this->setRenderData($goods_info);
		$this->redirect('products');
	}
	//商品讨论更新
	function discussUpdate()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');
		$content  = IFilter::act(IReq::get('content'),'text');
		$captcha  = IReq::get('captcha');
		$_captcha = ISafe::get('captcha');
		$return   = array('isError' => true , 'message' => '');

		if(!$this->user['user_id'])
		{
			$return['message'] = '请先登录系统';
		}
    	else if(!$captcha || !$_captcha || $captcha != $_captcha)
    	{
    		$return['message'] = '验证码输入不正确';
    	}
    	else if(trim($content) == '')
    	{
    		$return['message'] = '内容不能为空';
    	}
    	else
    	{
    		$return['isError'] = false;

			//插入讨论表
			$tb_discussion = new IModel('discussion');
			$dataArray     = array(
				'goods_id' => $goods_id,
				'user_id'  => $this->user['user_id'],
				'time'     => ITime::getDateTime(),
				'contents' => $content,
			);
			$tb_discussion->setData($dataArray);
			$tb_discussion->add();

			$return['time']     = $dataArray['time'];
			$return['contents'] = $content;
			$return['username'] = $this->user['username'];
    	}
    	echo JSON::encode($return);
	}

	//获取货品数据
	function getProduct()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$specJSON = IFilter::act(IReq::get('specJSON'));
		if(!$specJSON || !is_array($specJSON))
		{
			echo JSON::encode(array('flag' => 'fail','message' => '规格值不符合标准'));
			exit;
		}

		//获取货品数据
		$tb_products = new IModel('products');
		$procducts_info = $tb_products->getObj("goods_id = ".$goods_id." and spec_array = '".JSON::encode($specJSON)."'");

		//匹配到货品数据
		if(!$procducts_info)
		{
			echo JSON::encode(array('flag' => 'fail','message' => '没有找到相关货品'));
			exit;
		}

		//获得会员价
		$countsumInstance = new countsum();
		$group_price = $countsumInstance->getGroupPrice($procducts_info['id'],'product');

		//会员价格
		if($group_price !== null)
		{
			$procducts_info['group_price'] = $group_price;
		}

		echo JSON::encode(array('flag' => 'success','data' => $procducts_info));
	}

	//顾客评论ajax获取
	function comment_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$commentDB = new IQuery('comment as c');
		$commentDB->join   = 'left join goods as go on c.goods_id = go.id AND go.is_del = 0 left join user as u on u.id = c.user_id';
		$commentDB->fields = 'u.head_ico,u.username,c.*';
		$commentDB->where  = 'c.goods_id = '.$goods_id.' and c.status = 1';
		$commentDB->order  = 'c.id desc';
		$commentDB->page   = $page;
		$data     = $commentDB->find();
		$pageHtml = $commentDB->getPageBar("javascript:void(0);",'onclick="comment_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//购买记录ajax获取
	function history_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$orderGoodsDB = new IQuery('order_goods as og');
		$orderGoodsDB->join   = 'left join order as o on og.order_id = o.id left join user as u on o.user_id = u.id';
		$orderGoodsDB->fields = 'o.user_id,og.goods_price,og.goods_nums,o.create_time as completion_time,u.username';
		$orderGoodsDB->where  = 'og.goods_id = '.$goods_id.' and o.status = 5';
		$orderGoodsDB->order  = 'o.create_time desc';
		$orderGoodsDB->page   = $page;

		$data = $orderGoodsDB->find();
		$pageHtml = $orderGoodsDB->getPageBar("javascript:void(0);",'onclick="history_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//讨论数据ajax获取
	function discuss_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$discussDB = new IQuery('discussion as d');
		$discussDB->join = 'left join user as u on d.user_id = u.id';
		$discussDB->where = 'd.goods_id = '.$goods_id;
		$discussDB->order = 'd.id desc';
		$discussDB->fields = 'u.username,d.time,d.contents';
		$discussDB->page = $page;

		$data = $discussDB->find();
		$pageHtml = $discussDB->getPageBar("javascript:void(0);",'onclick="discuss_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//买前咨询数据ajax获取
	function refer_ajax()
	{
		$goods_id = IFilter::act(IReq::get('goods_id'),'int');
		$page     = IFilter::act(IReq::get('page'),'int') ? IReq::get('page') : 1;

		$referDB = new IQuery('refer as r');
		$referDB->join = 'left join user as u on r.user_id = u.id';
		$referDB->where = 'r.goods_id = '.$goods_id;
		$referDB->order = 'r.id desc';
		$referDB->fields = 'u.username,u.head_ico,r.time,r.question,r.reply_time,r.answer';
		$referDB->page = $page;

		$data = $referDB->find();
		$pageHtml = $referDB->getPageBar("javascript:void(0);",'onclick="refer_ajax([page])"');

		echo JSON::encode(array('data' => $data,'pageHtml' => $pageHtml));
	}

	//评论列表页
	function comments_list()
	{
		$id   = IFilter::act(IReq::get("id"),'int');
		$type = IFilter::act(IReq::get("type"));
		$data = array();

		//评分级别
		$type_config = array('bad'=>'1','middle'=>'2,3,4','good'=>'5');
		$point       = isset($type_config[$type]) ? $type_config[$type] : "";

		//查询评价数据
		$this->commentQuery = Api::run('getListByGoods',$id,$point);
		$this->commentCount = Comment_Class::get_comment_info($id);
		$this->goods        = Api::run('getGoodsInfo',array("#id#",$id));

		$this->redirect('comments_list');
	}

	//提交评论页
	function comments()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if(!$id)
		{
			IError::show(403,"传递的参数不完整");
		}

		if(!isset($this->user['user_id']) || $this->user['user_id']==null )
		{
			IError::show(403,"登录后才允许评论");
		}

		$result = Comment_Class::can_comment($id,$this->user['user_id']);
		if(is_string($result))
		{
			IError::show(403,$result);
		}

		$this->comment      = $result;
		$this->commentCount = Comment_Class::get_comment_info($result['goods_id']);
		$this->goods        = Api::run('getGoodsInfo',array("#id#",$result['goods_id']));
		$this->redirect("comments");
	}

	/**
	 * @brief 进行商品评论 ajax操作
	 */
	public function comment_add()
	{
		$id      = IFilter::act(IReq::get('id'),'int');
		$content = IFilter::act(IReq::get("contents"));
		if(!$id || !$content)
		{
			IError::show(403,"填写完整的评论内容");
		}

		if(!isset($this->user['user_id']) || !$this->user['user_id'])
		{
			IError::show(403,"未登录用户不能评论");
		}

		$data = array(
			'point'        => IFilter::act(IReq::get('point'),'float'),
			'contents'     => $content,
			'status'       => 1,
			'comment_time' => ITime::getNow("Y-m-d"),
		);

		if($data['point']==0)
		{
			IError::show(403,"请选择分数");
		}

		$result = Comment_Class::can_comment($id,$this->user['user_id']);
		if(is_string($result))
		{
			IError::show(403,$result);
		}

		$tb_comment = new IModel("comment");
		$tb_comment->setData($data);
		$re         = $tb_comment->update("id={$id}");

		if($re)
		{
			$commentRow = $tb_comment->getObj('id = '.$id);

			//同步更新goods表,comments,grade
			$goodsDB = new IModel('goods');
			$goodsDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$goodsDB->update('id = '.$commentRow['goods_id'],array('grade','comments'));

			//同步更新seller表,comments,grade
			$sellerDB = new IModel('seller');
			$sellerDB->setData(array(
				'comments' => 'comments + 1',
				'grade'    => 'grade + '.$commentRow['point'],
			));
			$sellerDB->update('id = '.$commentRow['seller_id'],array('grade','comments'));
			$this->redirect("/site/comments_list/id/".$commentRow['goods_id']);
		}
		else
		{
			IError::show(403,"评论失败");
		}
	}

	function pic_show()
	{
		$this->layout="";

		$id   = IFilter::act(IReq::get('id'),'int');
		$item = Api::run('getGoodsInfo',array('#id#',$id));
		if(!$item)
		{
			IError::show(403,'商品信息不存在');
		}
		$photo = Api::run('getGoodsPhotoRelationList',array('#id#',$id));
		$this->setRenderData(array("id" => $id,"item" => $item,"photo" => $photo));
		$this->redirect("pic_show");
	}

	function help()
	{
		$id       = IFilter::act(IReq::get("id"),'int');
		$tb_help  = new IModel("help");
		$help_row = $tb_help->getObj("id={$id}");
		if(!$help_row)
		{
			IError::show(404,"您查找的页面已经不存在了");
		}
		$tb_help_cat    = new IModel("help_category");
		$this->cat_row  = $tb_help_cat->getObj("id={$help_row['cat_id']}");
		$this->help_row = $help_row;
		$this->redirect("help");
	}

	function help_list()
	{
		$id          = IFilter::act(IReq::get("id"),'int');
		$tb_help_cat = new IModel("help_category");
		$cat_row     = $tb_help_cat->getObj("id={$id}");

		//帮助分类数据存在
		if($cat_row)
		{
			$this->helpQuery = Api::run('getHelpListByCatId',$id);
			$this->cat_row   = $cat_row;
		}
		else
		{
			$this->helpQuery = Api::run('getHelpList');
			$this->cat_row   = array('id' => 0,'name' => '站点帮助');
		}
		$this->redirect("help_list");
	}

	//团购页面
	function groupon()
	{
		$id = IFilter::act(IReq::get("id"),'int');

		//指定某个团购
		if($id)
		{
			$this->regiment_list = Api::run('getRegimentRowById',array('#id#',$id));
			$this->regiment_list = $this->regiment_list ? array($this->regiment_list) : array();
		}
		else
		{
			$this->regiment_list = Api::run('getRegimentList');
		}

		if(!$this->regiment_list)
		{
			IError::show('当前没有可以参加的团购活动');
		}

		//往期团购
		$this->ever_list = Api::run('getEverRegimentList');
		$this->redirect("groupon");
	}

	//品牌列表页面
	function brand()
	{
		$id   = IFilter::act(IReq::get('id'),'int');
		$name = IFilter::act(IReq::get('name'));
		$this->setRenderData(array('id' => $id,'name' => $name));
		$this->redirect('brand');
	}

	//品牌专区页面
	function brand_zone()
	{
		$brandId  = IFilter::act(IReq::get('id'),'int');
		$brandRow = Api::run('getBrandInfo',$brandId);
		if(!$brandRow)
		{
			IError::show(403,'品牌信息不存在');
		}
		$this->setRenderData(array('brandId' => $brandId,'brandRow' => $brandRow));
		$this->redirect('brand_zone');
	}

	//商家主页
	function home()
	{
		$seller_id = IFilter::act(IReq::get('id'),'int');
		$sellerRow = Api::run('getSellerInfo',$seller_id);
		if(!$sellerRow)
		{
			IError::show(403,'商户信息不存在');
		}
		$this->setRenderData(array('sellerRow' => $sellerRow,'seller_id' => $seller_id));
		$this->redirect('home');
	}
	function category_third(){
        $this->redirect('category_third');
    }
    function favorite(){
        $this->redirect('favorite');
    }
    function show(){
        $this->redirect('show');
    }
    function goods_more(){
    	//获取商品
    	$category_id 				= IFilter::act(IReq::get('category_id'));
    	$commend_id 				= IFilter::act(IReq::get('commend_id'));
    	$db_goods 					= new IQuery('goods as m');
    	$db_goods->join 			= 'left join commend_goods as d on d.goods_id=m.id left join category_extend as e on e.goods_id=m.id';
    	$db_goods->where 			= 'm.is_del=0 and e.category_id in ('.$category_id.') and d.commend_id='.$commend_id;
    	$db_goods->fields 			= 'm.id,m.name,m.sell_price,m.img,m.market_price,m.jp_price';
    	$db_goods->limit 			= 1000;
    	$db_goods->order 			= 'm.id desc';
    	$db_goods->group 			= 'm.id';
    	$data_goods 				= $db_goods->find();
    	//处理为偶数数量
    	if(count($data_goods)%2 == 1){
    		$data_goods[] 			= $data_goods[0];
    	}
    	
    	$this->data_goods 			= $data_goods;
        $this->redirect('goods_more');
    }
    //一键翻译
    function translate(){
    	$text 						= IReq::get('text');
    	$text 						= strip_tags($text);
    	if(!empty($text)){
    		$num 					= ceil(mb_strlen($text,'utf-8')/1000);
    		$data 					= '';
    		for($i=0; $i<$num; $i++){
    			$content 			= mb_substr($text,$i*1000,$i*1000+1000,'utf-8');
    			$data 				.= translate::exec($content,'jp','zh');
    			if($data == false) exit( '翻译失败了，再重新试试吧' );
    		}
	    	exit( json_encode($data) );
    	}
    	exit( '' );
    }
    
    
}
