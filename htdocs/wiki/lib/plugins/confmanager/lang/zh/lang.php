<?php


// These settings must be present and set appropriately for the language.
// Do not change, if you don't know what it does!
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';


// For admin plugins, the menu prompt to be displayed in the admin menu.
// If set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = '配置文件管理器';


// Page header
$lang['welcomehead'] = '配置文件管理器';
$lang['welcome']     = '配置文件管理器让你能编辑 DokuWiki 及其插件的配置文件。';


// Page controls (buttons, labels, etc.)
$lang['save'] = '保存';
$lang['select_config'] = '选择一个配置文件';
$lang['please_select'] = '--- 请选择一个条目 ---';
$lang['edit'] = '编辑';
$lang['cannot change default file icon'] = '不能改变默认图标';
$lang['delete_action'] = '删除';
$lang['edit_key_action'] = '编辑';
$lang['edit_key_action_tooltip'] = '点击重命名这一条目';
$lang['delete_action_tooltip'] = '点击删除这一条目';
$lang['delete_action_tooltip_disabled'] = '不能删除默认值';
$lang['edit_key_action_tooltip_disabled'] = '不能编辑默认值';
$lang['default_value_tooltip'] = '这是一个默认值，不能被改变';
$lang['edit_icon_action'] = '编辑图标';
$lang['edit_icon_action_disabled'] = '不能编辑图标';
$lang['edit_icon_action_tooltip'] = '点击选择另外的图标';
$lang['edit_icon_action_tooltip_disabled'] = '不能改变默认图标';
$lang['toggle_description'] = '切换显示和隐藏描述';
$lang['toggle_defaults'] = '切换显示和隐藏默认值';
$lang['defaults_description'] = '请注意：默认值定义了 Dokuwiki 的基础行为，不能被改变。';
$lang['add_action'] = '添加';
$lang['add_action_tooltip'] = '点击添加新的条目到这个列表';
$lang['no_script_title'] = 'JavaScript 被禁用了！';
$lang['no_script_message'] = '由于 JavaScript 被禁用了，confmanager 只提供一些基础功能。为了方便地使用像折叠，条目上的快捷操作等等。请打开你的 JavaScript 支持。我们保证不会伤害你的，亲。';
$lang['file_upload_prompt'] = '请选择要上传的图片文件';
$lang['upload'] = '上传';
$lang['cancel'] = '取消';
$lang['uploading'] = '上传文件中...';
$lang['upload_success'] = '上传文件成功';
$lang['upload_error'] = '上传失败了';
$lang['continue'] = '继续';
$lang['delete_icon_action'] = '删除图标';
$lang['delete_icon_action_tooltip'] = '点击删除图标';
$lang['delete_icon_action_disabled'] = '不能删除图标';
$lang['delete_icon_action_tooltip_disabled'] = '不能删除图标';

// Table headers
$lang['key'] = '键';
$lang['value'] = '值';
$lang['actions'] = '操作';
$lang['user_defined_values'] = '用户定义值';
$lang['default_values'] = '默认值';


// Names of DokuWiki's default config files
$lang['URL Schemes'] = 'URL 模式';
$lang['Blacklisting'] = '黑名单';
$lang['Acronyms'] = '缩写词和缩略词';
$lang['Entity replacements'] = '实体替换';
$lang['MIME configuration'] = 'MIME 文件类型';
$lang['InterWiki Links'] = '维基间链接';


// Error Messages
$lang['invalid request csrf'] = '警告： 检测到跨站脚本尝试';
$lang['invalid save arguments'] = '保存配置文件时碰到了错误';
$lang['changes applied'] = '改动被成功应用';
$lang['cannot apply changes'] = '不能应用改动';
$lang['no local file given'] = '载入配置文件时错误：未指定路径';

// Fehler beim upload
$lang['upload_errNoAdmin'] = '这个操作需要管理员权限';
$lang['upload_errNoConfig'] = '没有指定配置文件';
$lang['upload_errNoFileSend'] = '没有提交文件';
$lang['upload_errNoConfigKeySend'] = '缺少键参数';
$lang['upload_errCannotOverwriteDefaultKey'] = '不能覆盖默认图标';
$lang['upload_errUploadError'] = '上传时出错';
$lang['upload_errNoFileExtension'] = '缺少文件扩展名';
$lang['upload_errWrongFileExtension'] = '指定了错误的文件扩展名';
$lang['upload_errCannotMoveUploadedFileToFolder'] = '上传的文件不能被移动到图片文件夹。这可能是由于缺少权限。';
$lang['iconDelete_error'] = '删除图标时出错。这可能是由于缺少权限';
