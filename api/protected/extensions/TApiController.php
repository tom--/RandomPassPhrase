<?php

class TApiController extends CController {
    /**
     * @var TApiQuery
     */
    public $query;

    public $defaultAction = 'Posts';

    public static function humanHttpStatus($statusCode)
    {
        switch ($statusCode) {
            case 200: return 'OK';
            case 404: return 'Not Found';
            case 403: return 'Forbidden';
            case 400: return 'Bad Request';
            case 500: return 'Internal Server Error';
            case 501: return 'Not Implemented';
            case 503: return 'Service Unavailable';
            default: return 'Error';
        }
    }

    /**
     * Instantiate and initialize an API query model.
     *
     * @param string $class
     */
    protected function constructQuery($class = null)
    {
        $action = $this->action->getId();
        if ($class === null) {
            $class = ucfirst($action) . 'Query';
        }
        $this->query = new $class;
        $this->query->method = $action;
    }

    /**
     * Run the current HTTP request as an API query
     */
    protected function doRequest()
    {
        $beginTime = microtime(true);
        $this->query->verb = strtoupper($_SERVER['REQUEST_METHOD']);

        if ($this->query->verb === 'GET' && isset($_GET['docs']) && $_GET['docs'] === '1') {
            $this->query->selfDocument();
        } else {
            $valid = $this->query->doInputs();
            if (!$valid) {
                $this->query->error = 400;
                $this->query->messages = $this->query->getErrors();
                array_unshift($this->query->messages, 'Request validation failed');
            } else {
                $this->query->run();
                if ($this->query->nResults === null) {
                    if (is_scalar($this->query->results)) {
                        $this->query->nResults = 1;
                    } else {
                        $this->query->nResults = count($this->query->results);
                    }
                }
            }

            if ($this->query->error) {
                $status = $this->query->error . ' ' . self::humanHttpStatus($this->query->error);
                header('HTTP/1.1 ' . $status);
                array_unshift($this->query->messages, $status);
                if (substr($this->query->error, 0, 1) === '4') {
                    $this->query->messages[] = $this->query->seeLink(
                        null,
                        array('docs' => 1),
                        'for this method\'s documentation.'
                    );
                }
                $this->query->error = true;
            } else {
                $this->setCacheing();
            }
        }

        $log[] = $this->query->getSummary();
        if ($this->query->nResults) {
            $log[] = 'nR=' . $this->query->nResults;
        }
        $log[] = 'St=' . sprintf('%4.2fms', 1000 * (microtime(true) - $beginTime));
        Yii::log(implode(' ', $log), 'trace', 'api');

        $this->query->serverTime = sprintf('%4.2fms', 1000 * (microtime(true) - $beginTime));

        header('Content-type: application/json');
        echo json_encode($this->query);

        Yii::app()->end();
    }

    /**
     * Sets cache control headers in the case of sucessful query. Inheriting classes may
     * override this method.
     */
    protected function setCacheing()
    {
    }
}
