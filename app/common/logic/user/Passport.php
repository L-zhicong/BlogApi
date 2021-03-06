<?php
/**
 * 用户登陆身份验证类
 */
namespace app\common\logic\user;
use app\common\utils\Captcha;
use app\common\utils\JwtAuth;
use app\lib\exception\AuthException;
use think\facade\Cache;
use app\lib\exception\PlatException;
class Passport{

	private static Passport|null $instance = null;
	private AbsUser $passportObject;
	private int $plat;  //访问的平台应用 1 管理后台
    public string $type;

	private function __construct(int $plat)
	{
        switch ($plat){
            case 1:
                $this->type = "admin";
                $this->plat = $plat;
                $this->passportObject = new AdminUser();
                break;
            case 3:
                $this->type = "app";
                $this->plat = $plat;
                $this->passportObject = new AppUser();
                break;
            default:
                throw new PlatException('平台不存在');
        }
	}

	public static function getInstance($plat = 0)
	{
         if (self::$instance == null) {
             self::$instance = new self($plat);
         }
        return new self($plat);
	}

    public function login($param) 
    {
        $loginInfo = $this->passportObject->login($param);
//        $this->loginInfo = $loginInfo;
        return $loginInfo;
    }

    public function register($param)
    {
        return $this->passportObject->register($param);
    }

    public function getInfo($uid){
       return $this->passportObject->getInfo($uid);
    }

    /**
     * 创建登录验证码key
     */
    public function createLoginKey($code)
    {
        $key = uniqid(microtime(true), true);
        cache('am_captcha' . $key, $code, config('system.captcha_exp', 5) * 60);
        return $key;
    }

    /**
     * 检查验证码
     */
    public function checkCode($key, $code)
    {

        if (!app()->make(Captcha::class)->check($code)) {
            E('验证码错误，请重新输入');
//            return $this->fail('验证码错误，请重新输入');
        }
        $_code = cache('am_captcha' . $key);
        if (!$_code) {
            E('验证码过期');
        }

        if (strtolower($_code) != strtolower($code)) {
            E('验证码错误');
        }

        //删除code
        cache('am_captcha' . $key, NULL);
    }

    public function createToken($userId)
    {
        $service = new JwtAuth();
        $exp = intval(config('system.token_exp'));
        $lastTime = date('Y-m-d H:i:s');
        $platLastTime = Cache::store('redis')->set('login_last_time_'.$this->plat.'_'.$userId,$lastTime);
        $token = $service->createToken($userId, $this->type, $this->plat, $exp, ['login_time' => $lastTime]);
//        $this->cacheToken($token['token'], $token['out']);
        return $token;
    }

    public function cacheToken(string $token, string $exp)
    {
        Cache::set($this->platList[$this->plat].'_' . $token, time() + $exp, $exp);
    }

    public function checkToken(string $token)
    {
        $JwtObj = new JwtAuth();
        $JwtObj->verifyToken();
        $has = Cache::has($this->platList[$this->plat].'_' . $token);
        if (!$has)
            throw new AuthException('无效的token');
        $lastTime = Cache::get($this->platList[$this->plat].'_' . $token);
        if (($lastTime + (intval(config('system.token_valid_exp'))) * 60) < time())
            throw new AuthException('token 已过期');
    }

    public function updateToken(string $token)
    {
        $exp = intval(config('system.token_valid_exp')) * 60;
        Cache::set($this->platList[$this->plat].'_' . $token, time() + $exp, $exp);
    }

    public function clearToken(string $token)
    {
        Cache::rm($this->platList[$this->plat].'_' . $token);
    }

    public function updateLastTime($userInfo)
    {
        $userInfo->last_time = time();
        $userInfo->save();
    }
}