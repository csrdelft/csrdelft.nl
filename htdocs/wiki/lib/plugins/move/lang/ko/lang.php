<?php
/**
 * korean language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gary Owen <>
 * @author     Myeongjin <aranet100@gmail.com>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = '문서/이름공간 옮기기/이름 바꾸기...';
$lang['desc'] = '문서/이름공간 옮기기/이름 바꾸기 플러그인';

$lang['notexist']    = '%s 문서가 존재하지 않습니다';
$lang['medianotexist']    = '%s 미디어 파일이 존재하지 않습니다';
$lang['notwrite']    = '이 문서를 수정할 충분한 권한이 없습니다';
$lang['badns']       = '이름공간에 잘못된 글자가 있습니다.';
$lang['badname']     = '문서 이름에 잘못된 글자가 있습니다.';
$lang['nochange']    = '문서 이름과 이름공간이 바뀌지 않습니다.';
$lang['nomediachange']    = '미디어 파일 이름과 이름공간이 바뀌지 않습니다.';
$lang['existing']    = '%s인 문서는 이미 %s에 존재합니다';
$lang['mediaexisting']    = '%s인 미디어 파일은 이미 %s에 존재합니다';
$lang['root']        = '[루트 이름공간]';
$lang['current']     = '(현재)';
$lang['renamed']     = '문서 이름이 %s에서 %s(으)로 바뀌었습니다';
$lang['moved']       = '문서가 %s에서 %s(으)로 옮겨졌습니다';
$lang['move_rename'] = '문서가 %s에서 %s(으)로 옮겨지고 이름이 바뀌었습니다';
$lang['delete']		= '옮기기 플러그인에 의해 삭제됨';
$lang['norights']    = '%s(을)를 편집할 충분하지 않은 권한이 있습니다.';
$lang['nomediarights']    = '%s(을)를 삭제할 충분하지 않은 권한이 있습니다.';
$lang['notargetperms'] = '%s 문서를 만들 권한이 없습니다.';
$lang['nomediatargetperms'] = '%s 미디어 파일을 만들 권한이 없습니다.';
$lang['filelocked']  = '%s 문서가 잠겨 있습니다. 나중에 다시 시도하세요.';
$lang['linkchange']  = '링크가 옮기기 작업 때문에 적응했습니다';

$lang['ns_move_in_progress'] = '여기에 현재 문서 %s개와 미디어 파일 %s개가 %s 이름공간에서 %s 이름공간으로 옮겨지고 있습니다.';
$lang['ns_move_continue'] = '이름공간 옮기기 계속';
$lang['ns_move_abort'] = '이름공간 옮기기 중단';
$lang['ns_move_continued'] = '이름공간 옮기기가 %s 이름공간에서 %s 이름공간으로 계속되었으며, 항목 %s개가 여전히 남아 있습니다.';
$lang['ns_move_started'] = '이름공간 옮기기가 %s 이름공간에서 %s 이름공간으로 시작되었으며, 문서 %s개와 미디어 파일 %s개가 옮겨집니다.';
$lang['ns_move_error'] = '이름공간 옮기기를 %s에서 %s(으)로 계속하는 동안 오류가 발생했습니다.';
$lang['ns_move_tryagain'] = '다시 시도';
$lang['ns_move_skip'] = '현재 항목을 건너뛰기';
// Form labels
$lang['newname']     = '새 문서 이름:';
$lang['newnsname']   = '새 이름공간 이름:';
$lang['targetns']    = '새 이름공간 선택:';
$lang['newtargetns'] = '새 이름공간 만들기:';
$lang['movepage']	= '문서 옮기기';
$lang['movens']		= '이름공간 옮기기';
$lang['submit']      = '제출';
$lang['content_to_move'] = '옮길 내용';
$lang['move_pages']  = '문서';
$lang['move_media']  = '미디어 파일';
$lang['move_media_and_pages'] = '문서와 미디어 파일';
// JavaScript preview
$lang['js']['previewpage'] = 'OLDPAGE(은)는 NEWPAGE(으)로 옮겨집니다';
$lang['js']['previewns'] = 'OLDNS 이름공간 안에 모든 문서와 이름공간은 NEWNS 이름공간 안으로 옮겨집니다';
