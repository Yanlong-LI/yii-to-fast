<?php
/**
 *   Author: Yanlongli <ahlyl94@gmail.com>
 *   Date:   2019/8/3
 *   IDE:    PhpStorm
 *   Desc:
 */


namespace yanlongli\yii2\fast;


class Config
{
    protected static $config = null;
    protected static $init = false;

    protected static function init()
    {
        //加载公共配置
        $commonConfigPath = \Yii::$app->basePath . '/../common/config';
        if (is_dir($commonConfigPath)) {
            static::LoadConfigFile($commonConfigPath);
        }
        //加载模块配置
        $moduleConfigPath = \Yii::$app->basePath . '/config';
        static::LoadConfigFile($moduleConfigPath);
    }

    protected static function checkInit()
    {
        if (!static::$init)
            static::init();
    }

    /**
     * 获取配置参数
     * 兼容 key.key
     * key. 获取key下的所有数据 value
     *
     * @param      $name
     * @param null $default
     * @param null $config
     *
     * @return array|mixed|null
     */
    public static function get($name = '', $default = null, $config = null)
    {
        static::checkInit();
        if ($config == null) {
            if (is_null(static::$config)) {
                static::$config = \Yii::$app->params;
            }
            $config = static::$config;
        }
        $name = explode('.', $name);
        if (count($name) == 1) {
            if (trim($name[0]) == '') return $config;

            return isset($config[$name[0]]) ? $config[$name[0]] : $default;
        } else {
            if (isset($config[$name[0]])) {
                $newName = $name[0];
                unset($name[0]);
                $name = implode('.', $name);
            }

            return static::get($name, null, $config[$newName]);
        }
    }

    protected static function LoadConfigFile($path)
    {
        //1、首先先读取文件夹
        $temp = scandir($path);
        $config = [];
        //遍历文件夹
        foreach ($temp as $v) {
            $a = $path . '/' . $v;
            if (is_dir($a)) {
                //忽略子目录
//                if ($v == '.' || $v == '..') {//判断是否为系统隐藏的文件.和..  如果是则跳过否则就继续往下走，防止无限循环再这里。
//                    continue;
//                }
//                $this->list_file($a);//因为是文件夹所以再次调用自己这个函数，把这个文件夹下的文件遍历出来
            } else {
                if (substr($v, 0, 1) == '.') {
                    continue;
                } else {
                    if (substr($v, -10) === '-local.php') {
                        $k = substr($v, 0, -10);
                    } else {
                        $k = substr($v, 0, -4);
                    }
                    // 过滤 Yii 的主要配置文件
                    if (in_array($k, ['main', 'codeception', 'bootstrap', 'test'])) {
                        continue;
                    }

                    $_config = require $a;
                    if (isset(static::$config[$k])) {
                        static::$config[$k] = array_merge(static::$config[$k], $_config);
                    } else {
                        static::$config[$k] = $_config;
                    }
                }
            }

        }
    }
}