<?php
declare (strict_types = 1);

namespace app\common\Base;

use app\common\utils\Json;
use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class AdminBaseController extends Json
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     *  service 实例化的服务
     */
    protected $service;

    /**
     * repository  实例化的逻辑类
     */
    protected $repository;

    /**
     * 当前登陆管理员信息
     * @var
     */
    protected $userInfo;

    /**
     * 当前登陆管理员ID
     * @var
     */
    protected $adminId;

    /**
     * 平台id
     * @var int
     */
    protected $plat;

    /**
     * 当前管理员权限
     * @var array
     */
    protected $auth = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        $this->adminId = $this->request->adminId();
        $this->userInfo = $this->request->userInfo();
        $this->plat = $this->request->plat()??1;
        $this->auth = $this->request->adminInfo['rule'] ?? [];
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }


}
