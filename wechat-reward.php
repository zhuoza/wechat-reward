<?php
/**
 * @package Wechat-reward 微信打赏
 * @version 1.0
 */
/*
Plugin Name: 微信打赏
Plugin URI: https://github.com/wordpress-plugins-tanteng/wechat-reward
Description: 在文章末尾添加微信打赏功能，如果读者觉得这篇文章对他有用，可以用微信给你打赏赞助。
Author: tán téng
Version: 1.1
Author URI: http://www.tantengvip.com
*/

define('WRPATH',dirname(__FILE__));

class WechatReward
{
    public function __Construct()
    {
        $this->run();
    }

    /**
     * 加载js和css
     */
    public function setting()
    {
        //在jqeury之后加载js文件
        wp_register_script('wechat-reward', plugins_url( '/assets/wechat-reward.js', __FILE__ ), array('jquery'));
        wp_enqueue_script('wechat-reward');

        wp_register_style('wechat-reward', plugins_url( '/assets/wechat-reward.css', __FILE__ ));

        //确保在底部加载css样式，覆盖主题的样式
        add_action('wp_footer',array($this,'add_css'));
    }

    public function add_css()
    {
        wp_enqueue_style( 'wechat-reward');
    }

    //在文章末尾添加打赏图标
    public function add_pay($content)
    {
        $QRpic = get_option('wechat-reward-QR-pic');
        $QRpic = $QRpic ? $QRpic : 'http://www.tantengvip.com/wp-content/uploads/2015/11/626761280052462332.jpg';
        $pay = <<<PAY
        <div class="gave" >
            <a href="javascript:;" id="gave">打赏</a>
            <div class="code" id="wechatCode" style="display: none">
                <img src="[wechat-qrpic]" alt="微信扫一扫支付">
                <div><img src="[wechat-ico]" alt="微信logo" class="ico-wechat">微信扫一扫，打赏作者吧～</i></div>
            </div>
        </div>
PAY;
        $pay = strtr(
            $pay,
            array(
                '[wechat-qrpic]' => $QRpic,
                '[wechat-ico]' => plugins_url( '/assets/ico-wechat.jpg', __FILE__ )
            )
        );
        //本插件只在文章页和非手机访问有效
        if(is_single() && !wp_is_mobile()){
            $this->setting();
            $content .= $pay;
        }
        return $content;
    }

    //前台入口
    public function run()
    {
        add_filter( 'the_content', array($this,'add_pay'));
    }

    //设置link
    public function wechat_reward_plugin_setting( $links, $file )
    {
        if($file == 'wechat-reward/wechat-reward.php'){
            $settings_link = '<a href="' . admin_url( 'options-general.php?page=upload_wechat_QR' ) . '">' . __('Settings') . '</a>';
            array_unshift( $links, $settings_link ); // before other links
        }
        return $links;
    }

    //微信打赏设置菜单
    function WR_add_pages() {
        add_options_page( '微信打赏', '微信打赏', 'manage_options', 'upload_wechat_QR', array($this,'upload_wechat_QR'));
    }

    //调用钩子设置link
    public function settings()
    {
        add_filter( 'plugin_action_links', array($this,'wechat_reward_plugin_setting'), 10, 2 );
        add_action('admin_menu', array($this,'WR_add_pages'));
    }

    //管理页面
    public function upload_wechat_QR()
    {
        if(isset($_POST['submit']) && $_SERVER['REQUEST_METHOD']=='POST'){
            update_option('wechat-reward-QR-pic',$_POST['wechatQR'] ? $_POST['wechatQR'] : '');
            $this->upload_success();
        }
        $QRpic = get_option('wechat-reward-QR-pic');
?>
    <div class="wrap">
        <h2>设置微信打赏二维码</h2>
        <p>
            请先通过手机微信获取付款二维码，操作步骤：
        </p>
        <p>
            1.打开微信，点击右上角“+”号，点击“收钱”，即可进入微信收钱页面<br>
            2.长按二维码，点击“保存图片”，即可保存图片到手机<br>
            3.将图片上传到电脑，可以通过微信传输助手传到电脑，或者其他方式将图片传到电脑<br>
            4.将二维码图片传到WordPress站点，在WordPress后台“多媒体”-"添加"，上传二维码，<span style="color: red; ">然后复制上传到服务器的二维码图片的url</span><br>
            提示：建议把微信生成的二维码图片先进行裁剪再上传。有任何疑问请发邮件到tanteng@gmail.com，将第一时间回复，谢谢！
        </p>
        <form action="<?= admin_url( 'options-general.php?page=upload_wechat_QR' ) ?>" name="settings-WR" method="post">
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label for="QR">微信支付二维码URL</label></th>
                    <td><input type="text" class="regular-text code" value="<?= $QRpic ?>" id="QR" name="wechatQR"></td>
                </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" value="保存更改" class="button button-primary" id="submit" name="submit"></p>
        </form>
        <hr>
        <p>如果你觉得这个插件不错，给我打赏吧！！微信扫一扫</p>
        <p><img src="http://www.tantengvip.com/wp-content/uploads/2015/11/626761280052462332.jpg" alt="微信打赏二维码"> </p>
    </div>
<?php
    }

    //保存成功提示
    public function upload_success()
    {
        echo '<div class="updated"><p>更新成功！打开一篇文章页看看效果吧~~</p></div>';
    }
}

$instance = new WechatReward();

if(is_admin()){
    //插件设置页面
    $instance->settings();
}