> 实现思路：在编辑文章和单页render之前，通过替换html内容改写puload的FilesAdded方法，在FilesAdded方法内使用CompressorJS进行预压缩

#### 使用方法：

- 下载并解压，解压文件夹改名为CompressImage，复制到/usr/plugins目录下
- 后台插件管理页面启用并设置

#### 注意事项：

- 图片强制转为webp，因为webp足够优雅
- 上传webp格式不进行压缩
- 默认CompressorJS的CDN地址如果加载慢，自行存本地后插件中设置CDN为本地路径
