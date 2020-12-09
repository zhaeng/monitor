<?php
namespace api\components;

use OAuth2\RequestInterface;
use OAuth2\ResponseInterface;
use OAuth2\ResponseType\AccessTokenInterface;
use OAuth2\Storage\UserCredentialsInterface;

/**
 *
 * @author Brent Shaffer <bshafs at gmail dot com>
 */
class UserCredentials implements \OAuth2\GrantType\GrantTypeInterface
{
    private $userInfo;

    protected $storage;

    /**
     * @param OAuth2\Storage\UserCredentialsInterface $storage REQUIRED Storage class for retrieving user credentials information
     */
    public function __construct(UserCredentialsInterface $storage)
    {
        $this->storage = $storage;
    }

    public function getQuerystringIdentifier()
    {
        return 'password';
    }

    public function validateRequest(RequestInterface $request, ResponseInterface $response)
    {
        if (!$request->request("password") || (!$request->request("email") && !$request->request("mobile") && !$request->request("username"))) {
            $response->setError(400, 'invalid_request', '账号和密码不能为空');
            return null;
        }

        //邮箱登陆
        if ($request->request("email")) {
            if (!$this->storage->emailCheckUserCredentials($request->request("email"), $request->request("password"))) {
                $response->setError(401, 'invalid_grant', '邮箱和密码不匹配');
                return null;
            }
            $userInfo = $this->storage->getUserDetailsByEmail($request->request("email"));
        } elseif ($request->request("mobile")) {//手机登陆
            if (!$this->storage->mobileCheckUserCredentials($request->request("mobile"), $request->request("password"))) {
                $response->setError(401, 'invalid_grant', '手机号和密码不匹配');
                return null;
            }
            $userInfo = $this->storage->getUserDetailsByMobile($request->request("mobile"));
        }elseif($request->request("username")){
            if (!$this->storage->checkUserCredentials($request->request("username"), $request->request("password"))) {
                $response->setError(401, 'invalid_grant', '用户名和密码不匹配');
                return null;
            }
            $userInfo = $this->storage->getUserDetails($request->request("username"));
        }

        if (empty($userInfo)) {
            $response->setError(400, 'invalid_grant', '获取用户信息失败');
            return null;
        }

        if (!isset($userInfo['user_id'])) {
            throw new \LogicException("用户Id不存在");
        }

        $this->userInfo = $userInfo;
        return true;
    }

    public function getClientId()
    {
        return null;
    }

    public function getUserId()
    {
        return $this->userInfo['user_id'];
    }

    public function getScope()
    {
        return isset($this->userInfo['scope']) ? $this->userInfo['scope'] : null;
    }

    public function createAccessToken(AccessTokenInterface $accessToken, $client_id, $user_id, $scope)
    {
        $token = $accessToken->createAccessToken($client_id, $user_id, $scope);
        if (!empty($token)) {
            $token['uid'] = $user_id;
            $result = [
                'message' => '登录成功',
                'data'    => $token,
            ];
            return $result;
        }
    }
}
