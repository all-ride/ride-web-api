<?php

namespace ride\web\api\controller;

use ride\library\api\ApiBrowser;
use ride\library\http\Response;

use ride\web\base\controller\AbstractController;

use \Exception;

/**
 * Controller of the API pages
 */
class ApiController extends AbstractController {

    /**
     * Url to the namespace detail
     * @var string
     */
    protected $urlNamespace;

    /**
     * URL to the class detail
     * @var string
     */
    protected $urlClass;

    /**
     * Constructs a new API controller
     * @param \ride\library\api\ApiBrowser $apiBrowser
     * @return null
     */
    public function __construct(ApiBrowser $apiBrowser) {
        $this->apiBrowser = $apiBrowser;
    }

    /**
     * Initialize the URLs to the actions
     * @return boolean
     */
    public function preAction() {
        $this->urlNamespace = $this->getUrl('api.namespace');
        $this->urlClass = $this->getUrl('api.class');

        return true;
    }

    /**
     * Action to show the main API browser view
     * @return null
     */
    public function indexAction() {
        $this->setView('api/namespace', array(
        	'namespaces' => $this->apiBrowser->getNamespaces(),
        ));
    }

    /**
     * Action to show the detail of a namespace
     * @return null
     */
    public function namespaceAction() {
        $namespace = implode(ApiBrowser::NAMESPACE_SEPARATOR, func_get_args());
        $variables = array(
            'namespaces' => $this->apiBrowser->getNamespaces($namespace),
            'namespace' => $namespace,
            'classes' => $this->apiBrowser->getClassesForNamespace($namespace),
        );

        $this->setView('api/namespace', $variables, $namespace);
    }

    /**
     * Action to show the API of a class
     * @return null
     */
    public function classAction() {
        $args = func_get_args();
        $class = array_pop($args);
        $namespace = implode(ApiBrowser::NAMESPACE_SEPARATOR, $args);

        try {
            $namespaces = $this->apiBrowser->getNamespaces($namespace);
            $classes = $this->apiBrowser->getClassesForNamespace($namespace);
            $class = $this->apiBrowser->getClass($namespace, $class);

            $type = $class->getTypeString();
            $name = $class->getName();
            $shortName = $class->getShortName();
            $inheritance = $class->getInheritance();
            $interfaces = $class->getInterfaceNames();
            $properties = $class->getProperties();
            $constants = $class->getConstants();

            $variables = array(
                'namespaces' => $namespaces,
                'namespace' => $namespace,
                'classes' => $classes,
                'class' => $class,
                'type' => $type,
                'shortName' => $shortName,
                'name' => $name,
                'inheritance' => $inheritance,
                'interfaces' => $interfaces,
                'properties' => $properties,
                'constants' => $constants,
            );

            $this->setView('api/class', $variables, $namespace, $shortName);
        } catch (Exception $exception) {
            $this->response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);

            $namespace = implode('\\', $args);
            $this->addError('error.class.not.found', array('class' => $namespace . '\\' . $class));

            $this->setView('api/namespace', array(
            	'namespaces' => $this->apiBrowser->getNamespaces(),
            ));
        }
    }

    /**
     * Action to perform a class search
     * @return null
     */
    public function searchAction() {
        if ($this->request->isPost()) {
            $this->response->setRedirect($this->getUrl('api.search') . '?query=' . urlencode($this->request->getBodyParameter('query')));

            return;
        }

        $namespaces = $this->apiBrowser->getNamespaces();
        $searchQuery = $this->request->getQueryParameter('query');
        $searchNamespaces = array();

        if ($searchQuery) {
            $searchClasses = $this->apiBrowser->getClassesForNamespace(null, true, $searchQuery);
            if (count($searchClasses) == 1) {
                $keysSearchClasses = array_keys($searchClasses);
                $searchClass = array_shift($keysSearchClasses);

                $this->response->setRedirect($this->urlClass . '/' . $searchClass);

                return;
            }

            foreach ($namespaces as $namespace => $null) {
                if (strpos($namespace, $searchQuery) !== false) {
                    $searchNamespaces[$namespace] = $namespace;
                }
            }
        } else {
            $searchClasses = array();
        }

        $variables = array(
        	'namespaces' => $namespaces,
            'searchQuery' => $searchQuery,
            'searchClasses' => $searchClasses,
            'searchNamespaces' => $searchNamespaces,
        );

        $this->setView('api/search', $variables);
    }

    /**
     * Sets the provided template as view of the response along with some
     * generic variables common to a API view
     * @param string $template
     * @param array $variables
     * @param string $namespace
     * @param string $class
     * @return \ride\library\mvc\view\View
     */
    protected function setView($template, array $variables = array(), $namespace = null, $class = null) {
        $translator = $this->getTranslator();

        if (!isset($variables['namespaces'])) {
            $variables['namespaces'] = array();
        }
        if (!isset($variables['classes'])) {
            $variables['classes'] = array();
        }
        if (!isset($variables['searchQuery'])) {
            $variables['searchQuery'] = null;
        }
        if (!isset($variables['shortName'])) {
            $variables['shortName'] = null;
        }

        $variables['urlNamespace'] = $this->urlNamespace;
        $variables['urlClass'] = $this->urlClass;
        $variables['breadcrumbs'] = array(
            (string) $this->getUrl('api') => $translator->translate('title.api'),
        );

        if ($namespace) {
            $tokens = explode(ApiBrowser::NAMESPACE_SEPARATOR, $namespace);

            $namespace = '/';
            foreach ($tokens as $token) {
                $namespace = $namespace . $token . '/';

                $variables['breadcrumbs'][substr($this->urlNamespace . $namespace, 0, -1)] = $token;
            }

            if ($class) {
                $variables['breadcrumbs'][$this->urlClass . $namespace . '/' . $class] = $class;
            }
        } elseif ($template == 'api/search') {
            $variables['breadcrumbs'][(string) $this->getUrl('api.search')] = $translator->translate('title.search');
        }

        $this->setTemplateView($template, $variables);
    }

}
