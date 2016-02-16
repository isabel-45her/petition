<?php
namespace Slim\Middleware;
include "Strong/Strong.php";
// include "Strong/Provider.php";
// foreach (glob("Provider/*.php") as $filename)
// {
//     include $filename;
// }

class StrongAuth extends \Slim\Middleware
{
    /**
     * @var array
     */
    protected $settings = array(
        'login.url' => '/',
        'auth.type' => 'http',
        'realm' => 'Protected Area',
    );

    /**
     * Constructor
     *
     * @param   array  $config   Configuration for Strong and Login Details
     * @param   \Strong\Strong $strong
     * @return  void
     */
    public function __construct(array $config = array(), \Strong\Strong $strong = null)
    {
        $this->config = array_merge($this->settings, $config);
        $this->auth = (!empty($strong)) ? $strong : \Strong\Strong::factory($this->config);
    }

    /**
     * Call
     *
     * @return void
     */
    public function call()
    {
        $req = $this->app->request();

        // Authentication Initialised
        switch ($this->config['auth.type']) {
            case 'form':
                $this->formAuth($this->auth, $req);
                break;
            default:
                $this->httpAuth($this->auth, $req);
                break;
        }
    }

    /**
     * Form based authentication
     *
     * @param \Strong\Strong $auth
     * @param object $req
     */
    private function formAuth($auth, $req)
    {
        $app = $this->app;
        $config = $this->config;
        $this->app->hook('slim.before.router', function () use ($app, $auth, $req, $config) {
            $secured_urls = isset($config['security.urls']) && is_array($config['security.urls']) ? $config['security.urls'] : array();
            foreach ($secured_urls as $surl) {
                $patternAsRegex = $surl['path'];
                if (substr($surl['path'], -1) === '/') {
                    $patternAsRegex = $patternAsRegex . '?';
                }
                $patternAsRegex = '@^' . $patternAsRegex . '$@';

                if (preg_match($patternAsRegex, $req->getPathInfo())) {
                    if (!$auth->loggedIn()) {
                        if ($req->getPath() !== $config['login.url']) {
                            $app->redirect($config['login.url']);
                        }
                    }
                }
            }
        });

        $this->next->call();
    }

    /**
     * HTTPAuth based authentication
     *
     * This method will check the HTTP request headers for previous authentication. If
     * the request has already authenticated, the next middleware is called. Otherwise,
     * a 401 Authentication Required response is returned to the client.
     *
     * @param \Strong\Strong $auth
     * @param object $req
     */
    private function httpAuth($auth, $req)
    {
        $res = $this->app->response();
        $authUser = $req->headers('PHP_AUTH_USER');
        $authPass = $req->headers('PHP_AUTH_PW');

        if ($authUser && $authPass && $auth->login($authUser, $authPass)) {
            $this->next->call();
        } else {
            $res->status(401);
            $res->header('WWW-Authenticate', sprintf('Basic realm="%s"', $this->config['realm']));
        }
    }
}
