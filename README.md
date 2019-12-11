# wxpay4ecshop
ecshop微信扫码支付插件


0、直接上传 includes、languages 目录，wxpay_native_notify.php文件 到根目录

1、user.php $not_login_arr 数组增加 wxpay_native_query 值

$not_login_arr[] = 'wxpay_native_query';

2、 else if ($action == 'order_query') 前面增加 user.txt文件内容

3,注意,如果你的商城没有手机 版,即mobile这个目录中没有启动手机版,那么在手机上也可以直接用扫码支付.只需要长按二维码,即可正常支付. 更新记录 20160526 增加账户充值跳转 20160717 增加详细的错误提示

Blog

https://www.9suan.net
