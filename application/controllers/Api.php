<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {
	public $pre = 'ecs_';
    public $size = 10;
    public $page = 1;
	function __construct ()
	{
		parent::__construct();
        define('IS_DEBUG', 'development' == ENVIRONMENT );
        if (IS_DEBUG) {
        	define('IS_AJAX', true);
        } else {
	        define('IS_AJAX', (isset($_SERVER ['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER ['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'));
        }
        if (!IS_AJAX) $this->output->set_content_type('application/json')->set_output(json_encode(array('ret'=>null,'msg'=>'method is not ajax')));
		$this->view_override = false;
	}
	public function index()
	{
		$data = array('a'=>'av', 'b' => 'bv');
        $json = "'" . json_encode($data) . "'";
        echo $json;
		// $this->output->set_content_type('application/json')->set_output(json_encode( $data ));
	}
	public function goods()
	{
		if (IS_AJAX) {
			$type = !empty($this->input->get('type')) ? $this->input->get('type') : 'best';
			$start = !empty($this->input->post('last')) ? $this->input->post('last') : 0;
			$limit = !empty($this->input->post('amount')) ? $this->input->post('amount') : 10;

			if ($type == 'new') {
				$type = 'g.is_new = 1';
			} else if ($type == 'hot') {
				$type = 'g.is_hot = 1';
			} else {
				$type = 'g.is_best = 1';
			}
			// 取出所有符合条件的商品数据，并将结果存入对应的推荐类型数组中
			// $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' . "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, " . "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, RAND() AS rnd " . 'FROM ' . $this->pre . 'goods AS g ' . "LEFT JOIN " . $this->pre . "member_price AS mp " . "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ";
			$sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.shop_price AS org_price, g.promote_price, ' . "IFNULL(mp.user_price, g.shop_price) AS shop_price, " . "promote_start_date, promote_end_date, g.goods_brief, g.goods_thumb, g.goods_img, RAND() AS rnd " . 'FROM ' . $this->pre . 'goods AS g ' . "LEFT JOIN " . $this->pre . "member_price AS mp " . "ON mp.goods_id = g.goods_id";
			$sql .= ' WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND ' . $type;
			$sql .= ' ORDER BY g.sort_order, g.last_update DESC limit ' . $start . ', ' . $limit;
			$data = $this->db->query($sql)->result_array();
			$this->output->set_content_type('application/json')->set_output(json_encode( $data ));
		}
	}
	function get_categories_tree($cat_id = 0) {
        header("Access-Control-Allow-Origin: *");
        if ($cat_id > 0) {
            $sql = 'SELECT parent_id FROM ' . $this->pre . "category WHERE cat_id = '$cat_id'";
            $result = $this->row($sql);
            $parent_id = $result['parent_id'];
        } else {
            $parent_id = 0;
        }

        /*
          判断当前分类中全是是否是底级分类，
          如果是取出底级分类上级分类，
          如果不是取当前分类及其下的子分类
         */
        $sql = 'SELECT count(*) FROM ' . $this->pre . "category WHERE parent_id = '$parent_id' AND is_show = 1 ";
        if ($this->row($sql) || $parent_id == 0) {
            /* 获取当前分类及其子分类 */
            $sql = 'SELECT c.cat_id,c.cat_name,c.parent_id,c.is_show,t.cat_image ' .
                    'FROM ' . $this->pre . 'category as c ' .
                    'left join ' . $this->pre . 'touch_category as t on t.cat_id = c.cat_id ' .
                    "WHERE c.parent_id = '$parent_id' AND c.is_show = 1 ORDER BY c.sort_order ASC, c.cat_id ASC";

            $res = $this->db->query($sql)->result_array();
            $key = 0;
            foreach ($res AS $row) {
                if ($row['is_show']) {
                    $cat_arr[$key]['id'] = $row['cat_id'];
                    $cat_arr[$key]['name'] = $row['cat_name'];
                    $cat_arr[$key]['cat_image'] = $row['cat_image'];
                    // $cat_arr[$row['cat_id']]['cat_image'] = get_image_path(0, $row['cat_image'],false);
                    $cat_arr[$key]['url'] = $row['cat_id'];
                    // $cat_arr[$row['cat_id']]['url'] = url('category/index', array('id' => $row['cat_id']));

                    if (isset($row['cat_id']) != NULL) {
                        $cat_arr[$key]['cat_id'] = $this->get_child_tree($row['cat_id']);
                    }
                }
                $key++;
            }
        }
        if (isset($cat_arr)) {
			$this->output->set_content_type('application/json')->set_output(json_encode($cat_arr,true));
        }
    }
    function get_child_tree($tree_id = 0) {
        $three_arr = array();
        $sql = 'SELECT count(*) FROM ' . $this->pre . "category WHERE parent_id = '$tree_id' AND is_show = 1 ";
        if ($this->row($sql) || $tree_id == 0) {
            $child_sql = 'SELECT c.cat_id, c.cat_name, c.parent_id, c.is_show, t.cat_image ' .
                    'FROM ' . $this->pre . 'category as c ' .
                    'left join ' . $this->pre . 'touch_category as t on t.cat_id = c.cat_id ' .
                    "WHERE c.parent_id = '$tree_id' AND c.is_show = 1 ORDER BY c.sort_order ASC, c.cat_id ASC";
            $res = $this->db->query($child_sql)->result_array();
            $key = 0;
            foreach ($res AS $row) {
                if ($row['is_show'])
                    $three_arr[$key]['id'] = $row['cat_id'];
                $three_arr[$key]['name'] = $row['cat_name'];
                $three_arr[$key]['cat_image'] = $row['cat_image'];
                $three_arr[$key]['url'] = $row['cat_id'];
                // $three_arr[$row['cat_id']]['cat_image'] = get_image_path(0,$row['cat_image'],false);
                // $three_arr[$row['cat_id']]['url'] = url('category/index', array('id' => $row['cat_id']));

                if (isset($row['cat_id']) != NULL) {
                    $three_arr[$key]['cat_id'] = $this->get_child_tree($row['cat_id']);
                }
                $key++;
            }
        }
        return $three_arr;
    }
    
    function get_brands($app = 'brand') {
        $start = ($this->page - 1) * $this->size;
        $sql = "SELECT b.brand_id, b.brand_name, b.brand_logo, b.brand_desc, t.brand_banner FROM " . $this->pre . "brand b LEFT JOIN  " . $this->pre . "touch_brand t ON t.brand_id = b.brand_id " . "WHERE is_show = 1 " . "GROUP BY b.brand_id , b.sort_order order by b.sort_order ASC LIMIT $start , $this->size";
        $res = $this->db->query($sql)->result_array();
        $arr = array();
        $key = 0;
        foreach ($res as $row) {
            $arr[$key]['brand_name'] = $row['brand_name'];
            $arr[$key]['url'] = $row['brand_id'];
            $arr[$key]['brand_logo'] = $row['brand_logo'];
            $arr[$key]['brand_banner'] = $row['brand_banner'];
            $arr[$key]['goods_num'] = $row['brand_id'];
            // $arr[$row['brand_id']]['url'] = url('brand/goods_list', array('id' => $row['brand_id']));
            // $arr[$row['brand_id']]['brand_logo'] = get_banner_path($row['brand_logo']);
            // $arr[$row['brand_id']]['brand_banner'] = get_banner_path($row['brand_banner']);
            // $arr[$row['brand_id']]['goods_num'] = model('Brand')->goods_count_by_brand($row['brand_id']);
            $arr[$key]['brand_desc'] = htmlspecialchars($row['brand_desc'], ENT_QUOTES);
            $key++;
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($arr,true));
    }
    public function row($sql) {
        $data = $this->db->query($sql)->result_array();
        return isset($data[0]) ? $data[0] : false;
    }
}
