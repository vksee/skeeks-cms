<?php
/**
 * RequestHelper
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 05.11.2014
 * @since 1.0.0
 */
namespace skeeks\cms\helpers;

use skeeks\cms\App;
use skeeks\cms\modules\admin\components\UrlRule;
use skeeks\sx\traits\Entity;
use skeeks\sx\traits\InstanceObject;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseUrl;
use yii\helpers\Url;
use yii\web\Application;

/**
 * Class RequestOptions
 * @package skeeks\cms\helpers
 */
class UrlHelper
    extends BaseUrl
{
    use Entity;

    const SYSTEM_CMS_NAME = "_sx";

    protected $_route = "";

    protected $_absolute = false;

    /**
     * @var null|UrlHelper
     */
    static public $currentUrl = null;

    /**
     * @return static
     */
    static public function getCurrent()
    {
        if (static::$currentUrl === null)
        {
            //TODO: доработать вычисление текущего роута
            static::$currentUrl = static::constructCurrent();
        }

        return static::$currentUrl;
    }

    /**
     * @return static
     */
    static public function constructCurrent()
    {

        $route = [];
        if (!\Yii::$app->controller->module instanceof Application)
        {
            $route[] = \Yii::$app->controller->module->id;
        }

        $route[] = \Yii::$app->controller->id;
        $route[] = \Yii::$app->controller->action->id;

        $url = new static("/" . implode('/', $route), \Yii::$app->request->getQueryParams());
        if (\Yii::$app->cms->moduleAdmin()->requestIsAdmin())
        {
            $url->enableAdmin();
        }


        return $url;
    }


    /**
     * @return $this
     */
    public function normalizeCurrentRoute()
    {
        $this->_route = self::normalizeRoute($this->_route);
        return $this;
    }
    /**
     * @param $route
     * @return $this
     */
    public function setRoute($route)
    {
        $this->_route = (string) $route;
        return $this;
    }
    /**
     * @param $route
     * @param array $data
     * @return static
     */
    static public function construct($route = "", $data = [])
    {
        return new static($route, $data);
    }

    /**
     * @param $route
     * @param array $data
     */
    public function __construct($route = "", $data = [])
    {
        $this->_route   = (string) $route;
        $this->_data    = (array) $data;
    }

    /**
     * Получить системный параметр
     * @param null|string $key
     * @param null $default
     * @return array|mixed
     */
    public function getSystem($key = null, $default = null)
    {
        $systemData = (array) $this->get(self::SYSTEM_CMS_NAME, []);
        if ($key)
        {
            return ArrayHelper::getValue($systemData, $key, $default);
        } else
        {
            return $systemData;
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function issetSystemParam($key)
    {
        $systemData = (array) $this->get(self::SYSTEM_CMS_NAME, []);
        return (bool) isset($systemData[$key]);
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setSystemParam($key, $value = '')
    {
        $systemData = $this->getSystem();
        $systemData[$key] = $value;

        return $this->set(self::SYSTEM_CMS_NAME, $systemData);
    }

    /**
     * @param $systemData
     * @return $this
     */
    public function setSystem($systemData = [])
    {
        return $this->set(self::SYSTEM_CMS_NAME, (array) $systemData);
    }

    /**
     * @return $this
     */
    public function setCurrentRef()
    {
        if (!$this->getRef())
        {
            return $this->setRef(\Yii::$app->request->getUrl());
        }

        return $this;
    }

    /**
     * @param $ref
     * @return $this
     */
    public function setRef($ref)
    {
        return $this->setSystemParam("ref", (string) $ref);
    }

    /**
     * @return null|string
     */
    public function getRef()
    {
        return $this->getSystem("ref", "");
    }


    /**
     * @return bool
     */
    public function isAdmin()
    {
        return (bool) $this->get(UrlRule::ADMIN_PARAM_NAME);
    }

    /**
     * Добавить параметры, указывающие что запрос на валидацию данных формы.
     * @return $this
     */
    public function enableAjaxValidateForm()
    {
        return $this->setSystemParam(\skeeks\cms\helpers\RequestResponse::VALIDATION_AJAX_FORM_SYSTEM_NAME);
    }

    /**
     * Это урл админки.
     * @return $this
     */
    public function enableAdmin()
    {
        return $this->set(UrlRule::ADMIN_PARAM_NAME, UrlRule::ADMIN_PARAM_VALUE);
    }

    /**
     * @return $this
     */
    public function disableAdmin()
    {
        return $this->offsetUnset(UrlRule::ADMIN_PARAM_NAME);
    }

    /**
     * @return string
     */
    public function createUrl()
    {
        return \Yii::$app->urlManager->createUrl($this->toArray());
    }
    /**
     * @return string
     */
    public function createAbsoluteUrl()
    {
        return \Yii::$app->urlManager->createAbsoluteUrl($this->toArray());
    }


    /**
     * Включить абсолютный адрес
     * @return $this
     */
    public function enableAbsolute()
    {
        $this->_absolute = true;
        return $this;
    }

    public function disableAbsolute()
    {
        $this->_absolute = false;
        return $this;
    }

    public function toString()
    {
        if ($this->_absolute)
        {
            return $this->createAbsoluteUrl();
        } else
        {
            return $this->createUrl();
        }

    }
    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge([$this->_route], $this->_data);
    }
}