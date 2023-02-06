<?php

namespace App\Entity\UserManagement;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tracking
 *
 * @ORM\Entity
 * @ORM\Table(name="tracking")
 * @ORM\Entity(repositoryClass="App\Repository\UserManagement\TrackingRepository")
 */
class Tracking
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="controller", type="string", length=255, nullable=true)
     */
    private $controller;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255, nullable=true)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="http_method", type="string", length=255)
     */
    private $httpMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="lang", type="string", length=255, nullable=true)
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="id_request", type="string", length=255, nullable=true)
     */
    private $idRequest;

    /**
     * @var string
     *
     * @ORM\Column(name="page_request", type="string", length=255, nullable=true)
     */
    private $pageRequest;

    /**
     * @var string
     *
     * @ORM\Column(name="keyword_request", type="string", length=255, nullable=true)
     */
    private $keywordRequest;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_request", type="string", length=255)
     */
    private $ipRequest;

    /**
     * @var string
     *
     * @ORM\Column(name="uri_request", type="text", nullable=true)
     */
    private $uriRequest;

    /**
     * @var string
     *
     * @ORM\Column(name="query_request", type="text", nullable=true)
     */
    private $queryRequest;

    /**
     * @var string
     *
     * @ORM\Column(name="path_info", type="text", nullable=true)
     */
    private $pathInfo;

    /**
     * @var \Time
     *
     * @ORM\Column(name="duration", type="time", nullable=true)
     */
    private $duration;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * Unidirectional - Many-To-One
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User",inversedBy="trackings")
     * @ORM\JoinColumn(name="idUser", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set controller
     *
     * @param string $controller
     * @return Tracking
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Get controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Tracking
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set pathInfo
     *
     * @param string $pathInfo
     * @return Tracking
     */
    public function setPathInfo($pathInfo)
    {
        $this->pathInfo = $pathInfo;

        return $this;
    }

    /**
     * Get pathInfo
     *
     * @return string
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * Set httpMethod
     *
     * @param string $httpMethod
     * @return Tracking
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;

        return $this;
    }

    /**
     * Get httpMethod
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Set lang
     *
     * @param string $lang
     * @return Tracking
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set $ipRequest
     *
     * @param string $ipRequest
     * @return Tracking
     */
    public function setIpRequest($ipRequest)
    {
        $this->ipRequest = $ipRequest;

        return $this;
    }

    /**
     * Set idRequest
     *
     * @param string $idRequest
     * @return Tracking
     */
    public function setIdRequest($idRequest)
    {
        $this->idRequest = $idRequest;

        return $this;
    }

    /**
     * Get idRequest
     *
     * @return string
     */
    public function getIdRequest()
    {
        return $this->idRequest;
    }

    /**
     * Set pageRequest
     *
     * @param string $pageRequest
     * @return Tracking
     */
    public function setPageRequest($pageRequest)
    {
        $this->pageRequest = $pageRequest;

        return $this;
    }

    /**
     * Get pageRequest
     *
     * @return string
     */
    public function getPageRequest()
    {
        return $this->pageRequest;
    }

    /**
     * Get ipRequest
     *
     * @return string
     */
    public function getIpRequest()
    {
        return $this->ipRequest;
    }

    /**
     * Set keywordRequest
     *
     * @param string $keywordRequest
     * @return Tracking
     */
    public function setKeywordRequest($keywordRequest)
    {
        $this->keywordRequest = $keywordRequest;

        return $this;
    }

    /**
     * Get keywordRequest
     *
     * @return string
     */
    public function getKeywordRequest()
    {
        return $this->keywordRequest;
    }

    /**
     * Set $uriRequest
     *
     * @param string $uriRequest
     * @return Tracking
     */
    public function setUriRequest($uriRequest)
    {
        $this->uriRequest = $uriRequest;

        return $this;
    }

    /**
     * Get uriRequest
     *
     * @return string
     */
    public function getUriRequest()
    {
        return $this->uriRequest;
    }

    /**
     * Set queryRequest
     *
     * @param string queryRequest
     * @return Tracking
     */
    public function setQueryRequest($queryRequest)
    {
        $this->queryRequest = $queryRequest;

        return $this;
    }

    /**
     * Get queryRequest
     *
     * @return string
     */
    public function getQueryRequest()
    {
        return $this->queryRequest;
    }

    /**
     * Set duration
     *
     * @param \Time $duration
     * @return Tracking
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return \Time
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Tracking
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function setUser(User $u)
    {
        $this->user = $u;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        if (isset($this->user)) {
            return $this->user;
        } else {
            return false;
        }
    }
}
