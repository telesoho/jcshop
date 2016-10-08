<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>商家管理中心</title>
    <link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.0/css/bootstrap-theme.min.css">
    <script src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
    <script src="http://cdn.bootcss.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/artDialog.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artdialog/plugins/iframeTools.js"></script><link rel="stylesheet" type="text/css" href="/runtime/_systemjs/artdialog/skins/aero.css" />
    <link rel="stylesheet" href="<?php echo $this->getWebSkinPath()."css/login.css";?>" />
</head>

<body>
    <header class="clearfix">
        <div class="col-md-12 column">
            <ul class="breadcrumb">
                <li><a href="<?php echo IUrl::creatUrl("");?>">网站首页</a> <span class="divider"></span></li>
                <li class="active">商家中心</li>
            </ul>
        </div>
    </header>

    <div class="container">
        <div class="row clearfix">
            <div class="col-md-12 column">
                <div class="row clearfix">
                    <div class="col-md-12 column">
                        <h3 class="text-center w700" style="color:#333">商家管理中心</h3>
                        <p class="text-center" style="color:#333">IWEBSHOP</p>
                        <form method="post" name="login" action="<?php echo IUrl::creatUrl("/systemseller/login");?>" autoComplete="off" class="form-horizontal" role="form">
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <input class="form-control" placeholder="用户名" name="username" type="text" />
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <input name="password" class="form-control" placeholder="密码" type="password" />
                                </div>
                            </div>

                            <div class="form-group">
                                <div class=" col-sm-12">
                                    <button type="submit"  class="btn btn-block btn-success">登录</button>
                                </div>
                            </div>
                        </form>

                        <div style="margin-top:20px;margin-bottom:20px">
                            <a  target="_blank" href="http://www.aircheng.com"><p style="font-size:12px">联系IWEBSHOP</p></a>
                            <a target="_blank" href="<?php echo IUrl::creatUrl("/site/help_list");?>"><p style="font-size:12px">服务中心</p></a>
                        </div>
                        <a href="<?php echo IUrl::creatUrl("/simple/seller");?>" type="button" class="btn btn-block btn-info">商家入驻</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<footer class="text-center">PowerBy IWEBSHOP</footer>
</body>
</html>