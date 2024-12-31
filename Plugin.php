<?php

namespace TypechoPlugin\CompressImage;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Widget\Options;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 上传图片JS预压缩
 *
 * @package CompressImage
 * @author nrtzhou
 * @version 1.0.0
 * @link https://zail.cn
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        \Typecho\Plugin::factory('admin/write-post.php')->bottom = __CLASS__ . '::script';
        \Typecho\Plugin::factory('admin/write-page.php')->bottom = __CLASS__ . '::script';
        \Typecho\Plugin::factory('admin/footer.php')->end = __CLASS__ . '::compress';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Form $form)
    {
        $cdn = new \Typecho\Widget\Helper\Form\Element\Text(
          'cdn',
          NULL,
          'https://gcore.jsdelivr.net/npm/compressorjs@1.2.1/dist/compressor.min.js',
          '压缩库CDN地址',
          _t('CompressorJS的CDN地址, 如果加载慢可保存本地并修改此地址')
        );
        $quality = new \Typecho\Widget\Helper\Form\Element\Text(
          'quality',
          NULL,
          '80',
          _t('图片质量'),
          _t('压缩后的图片质量, 1~100')
        );
        $maxWidth = new \Typecho\Widget\Helper\Form\Element\Text(
          'maxWidth',
          NULL,
          '1920',
          _t('图片最大宽度'),
          _t('压缩后的图片最大宽度, 单位 px')
        );
        $maxHeight = new \Typecho\Widget\Helper\Form\Element\Text(
          'maxHeight',
          NULL,
          '1920',
          _t('图片最大高度'),
          _t('压缩后的图片最大高度, 单位 px')
        );

        $form->addInput($cdn);
        $form->addInput($quality);
        $form->addInput($maxWidth);
        $form->addInput($maxHeight);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }

    /**
     * 压缩库CDN
     *
     * @access public
     * @return void
     */
    public static function script()
    {
      $cdn = \Typecho\Widget::widget('Widget_Options')->plugin('CompressImage')->cdn;
      echo '<script src="'.$cdn.'"></script>';
    }

    /**
     * 压缩图片
     *
     * @access public
     * @return void
     */
    public static function compress()
    {
      // 获取缓冲区内容
      $html = ob_get_clean();

      // 判断是否是文章页面
      if (strpos($html, 'fileUploadStart(files[i])') !== false) {

        $options = \Typecho\Widget::widget('Widget_Options')->plugin('CompressImage');

        $html = str_replace(
            'var uploader',
            'function compressImage(file){return new Promise((resolve,reject)=>{new Compressor(file.getNative(),{quality:'.$options->quality.',maxWidth:'.$options->maxWidth.',maxHeight:'.$options->maxHeight.',mimeType:"image/webp",success(result){const compressedFile=new File([result],result.name,{type:"image/webp",lastModified:new Date().getTime()});resolve(compressedFile)},error(err){reject(err)}})})};var uploader',
            $html
        );
        $html = str_replace(
            'function (up, files) {',
            'async function (up, files) {',
            $html
        );
        $html = str_replace(
            'fileUploadStart(files[i]);',
            'const file=files[i];if(file.type.indexOf("image/")===0&&file.type!=="image/webp"){const compressedFile=await compressImage(file);up.removeFile(file);up.addFile(compressedFile,compressedFile.name)}else{fileUploadStart(file)}',
            $html
        );
        
      }

      // 输出修改后的内容
      echo $html;
    }
}
