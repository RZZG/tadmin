<?php

namespace tadmin\support\controller;

use think\App;
use think\Container;
use tadmin\model\Menu;
use tadmin\service\auth\facade\Auth;
use tadmin\support\controller\traits\Jump;
use tadmin\support\view\View;

abstract class Controller
{
    use Jump;

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
     * 应用实例
     * @var \tadmin\support\view\View
     */
    protected $view;

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
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    protected $viewPath = '';

    public function __construct(App $app = null)
    {
        $this->app     = $app ?: Container::getInstance()->get("app");
        $this->request = $this->app->request;

        $this->view    = new View($this->app);
        // 控制器初始化
        $this->initialize();

        $this->initConfig();

        $this->initView();

        $this->assignCommon();
    }


    // 初始化
    protected function initialize()
    {
    }

    public function initConfig()
    {
        if (is_file(admin_config_path('paginate.php'))) {
            $paginateAdmin = include admin_config_path('paginate.php');
            $config = $this->app->get('config');
            $paginate = $config->get('paginate');
            $config->set(array_merge(
                \is_array($paginate) ? $paginate : [],
                $paginateAdmin
            ), 'paginate');
        }
    }

    public function initView()
    {
        $assets = ltrim($this->app->config->get('tadmin.view.tpl_replace_string.__TADMIN_ASSETS__'), '/');
        $publicName = trim($this->app->config->get('tadmin.view.public_name'), '/');

        $documentPath = $this->app->getRootPath();

        if (!empty($publicName)) {
            $documentPath .= $publicName . '/';
        }

        if (!file_exists($documentPath . $assets)) {
            throw new \Exception('Resource not published,Please initialize tadmin.');
            // Console::call('tadmin:init');
        }
    }

    public function assignCommon()
    {
        $menus = app(Menu::class)->toTree();
        $adminer = Auth::user();
        $this->view->assign(compact('menus', 'adminer'));
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $config);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @param  array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [])
    {
        return $this->view->display($content, $vars);
    }

    protected function redirect(string $route = '', int $code = 302)
    {
        return redirect_route($route, $code);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);

        return $this;
    }

    /**
     * 视图过滤
     * @access protected
     * @param  Callable $filter 过滤方法或闭包
     * @return $this
     */
    protected function filter($filter)
    {
        $this->view->filter($filter);

        return $this;
    }

    /**
     * 初始化模板引擎
     * @access protected
     * @param  array|string $engine 引擎参数
     * @return $this
     */
    protected function engine($engine)
    {
        $this->view->engine($engine);

        return $this;
    }
}
