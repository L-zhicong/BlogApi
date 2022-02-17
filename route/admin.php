<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use app\common\middleware\AllowOriginMiddleware;
use app\common\middleware\AdminTokenMiddleware;
use think\facade\Route;


Route::group('adminapi',function (){
    /*public*/
    Route::group(function () {
        //用户名密码登录
        Route::post('login', 'Login/login')->name('AdminLogin');
        Route::post('logout','Login/logout');
//        //后台登录页面数据
//        Route::get('login/info', 'Login/info')->option(['real_name' => '登录信息']);
        //验证码
        Route::get('captcha_pro', 'Login/captcha')->name('')->option(['real_name' => '获取验证码']);
        Route::get('index', 'Test/index')->option(['real_name' => '测试地址']);
    })->middleware(AllowOriginMiddleware::class);

    /*private*/
    Route::group(function(){
        Route::group("user",function (){
            Route::Post('info','v1.User/getInfo');
        });

        //菜单管理
        Route::group('menu',function(){
            Route::get('getTree','v1.Menu/getList')->alias('menuLst');
            Route::get('lst','v1.Menu/menus');
            Route::post('create','v1.Menu/create')->alias('menuCreate');
            Route::get('create/form','v1.Menu/createForm')->alias('menuCreateForm');
            Route::get('update/:id','v1.Menu/update')->alias('menuUpdateForm');
            Route::post('update/:id','v1.Menu/update')->alias('menuUpdate');
            Route::delete('delete/:id','v1.Menu/delete')->alias('menuDelete');
        });

        //身份管理
        Route::group('role',function(){
            Route::get('lst','v1.Role/getList')->alias('roleLst');
            Route::post('create','v1.Role/create')->alias('roleCreate');
            Route::get('create/form','v1.Role/createForm')->alias('roleCreateForm');
            Route::get('update/:id','v1.Role/update')->alias('roleUpdateForm');
            Route::post('update/:id','v1.Role/update')->alias('roleUpdate');
            Route::delete('delete/:id','v1.Role/delete')->alias('roleDelete');
            Route::post('status/:id','v1.Role/updateStatus')->alias('roleStatus');
        });

        //用户管理
        Route::group('user',function(){
            Route::post('lst','/User/list')->alias('userLst');
            Route::post('status/:id','/User/statusUpdate')->alias('userStatus');
            Route::post('details/:id','/User/details')->alias('userDetails');
            Route::post('info','/User/info');
            Route::get('recruiterLst','/User/getRecruiterList')->alias('userRecruiterList');
            Route::get('getListByName','/User/getIdListByName')->alias('userListByName');
            Route::get('userExtractRecord','/User/userExtractRecord')->alias('userExtractRecord');
            Route::post('extractStatus/:id','/User/extractStatus')->alias('userExtractStatus');
        });

        Route::group('article', function () {
            Route::get('lst', 'v1.article.Article/getList')->name('systemArticlArticleLst');
            Route::post('create', '/article.Article/ArticleCreate')->name('systemArticleArticleCreate');
            Route::post('update/:id', '/article.Article/ArticleUpdate')->name('systemArticArticleleUpdate');
            Route::post('delete/:id', '/article.Article/delete')->name('systemArticArticleleDelete');
            Route::get('updateForm/:id', '/article.Article/ArticleUpdate')->name('systemArticArticleleDetail');
            //文章类别管理
            Route::group('category',function(){
                Route::get('lst', '/article.ArticleCategory/list')->name('systemArticleCategoryLst');
                Route::post('create', '/article.ArticleCategory/create')->name('systemArticleCategoryCreate');
                Route::get('updateForm/:id', '/article.ArticleCategory/update')->name('systemArticleCategoryUpdateForm');
                Route::post('update/:id', '/article.ArticleCategory/update')->name('systemArticleCategoryUpdate');
                Route::post('status/:id', '/article.ArticleCategory/switchStatus')->name('systemArticleCategoryStatus');
                Route::post('delete/:id', '/article.ArticleCategory/delete')->name('systemArticleCategoryDelete');
            });
        });

    })->middleware(AdminTokenMiddleware::class)->middleware(AllowOriginMiddleware::class);


})->prefix('admin.');