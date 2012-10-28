<?php

/**
 * @property string $verb HTTP request verb (e.g. GET, POST, etc.)
 * @property string $method API method called
 * @property int $limit Maximum number of (primary) objects to return in {@field results}
 * @property int $offset Zero-based offset from which (primary) objects are to be returned
 * @property bool|int $error True on error, see {@field messages} for explaination
 * @property mixed $messages API documentation and/or error messages
 * @property int $nResults Number of (primary) objects in {@field results}
 * @property string $serverTime Elapsed time process request on server
 * @property mixed $schema Formal spec. of data structure and types in {@field results}
 * @property mixed $results Query results, structured as given in {@field schema}
 */
abstract class TApiQuery extends CModel
{
    public $verb;
    public $method;
    public $limit;
    public $error = false;
    public $messages = array();
    public $nResults;
    public $serverTime;
    public $schema;
    public $results;

    protected $_summary;

    public function attributeLabels()
    {
        return array(
            'verb' => 'HTTP request method',
            'method' => 'API method',
            'limit' => 'Result size limit',
            'offset' => 'Result size limit',
            'error' => 'Error status',
            'messages' => 'Messages',
            'nResults' => 'Num results',
            'serverTime' => 'Server time',
            'schema' => 'Result schema',
            'results' => 'Query results',
        );
    }

    public function attributeNames()
    {
        return array_keys($this->attributeLabels());
    }

    public function rules()
    {
        return array(
            array(''),
        );
    }

    public function doInputs()
    {
        if (!$this->validate(array('verb'), false)) {
            return false;
        }
        $this->scenario = $this->verb;
        $input = array();
        if ($this->scenario === 'post' || $this->scenario === 'put') {
            $input = trim(file_get_contents('php://input'));
            if ($input) {
                $input = json_decode($input);
                if (json_last_error()) {
                    $this->addError('body', 'Error decoding JSON input');
                    return false;
                } elseif (!is_array($input)) {
                    $this->addError('body', 'Body should be a JSON Hash');
                    return false;
                }
            }
        }
        $this->setAttributes($_GET + $input);
        return $this->validate(null, false);
    }

    /**
     * Run the requested API query.
     *
     * @return mixed
     */
    abstract public function run();

    /**
     * Return a strung summarizing the API request, e.g. for logging.
     *
     * @return string
     */
    public function getSummary()
    {
        if (!$this->_summary && $this->safeAttributeNames) {
            $params = array();
            foreach ($this->safeAttributeNames as $attr) {
                $val = isset($_GET[$attr]) ? $_GET[$attr] : null;
                if ($val !== null && is_scalar($val)) {
                    $params[] = $attr . '=' . var_export($val, true);
                }
            }
            $params = implode(',', $params);
            $this->_summary = "$this->method($params)";
        }
        return $this->_summary;
    }

    /**
     * Write documentation for the requested method into $this->messages.
     */
    public function selfDocument()
    {

        // Get the class docblock lines from $this's class.
        $r = new ReflectionClass($this);
        $lines = preg_split('{\R}', trim(substr($r->getDocComment(), 3, -2)));

        // Append the class docblock lines from this class.
        $r = new ReflectionClass(get_class());
        $lines = array_merge(
            $lines,
            preg_split('{\R}', trim(substr($r->getDocComment(), 3, -2)))
        );

        if (!$lines) {
            return;
        }

        $safeAttrs = $this->getSafeAttributeNames();
        foreach ($lines as &$line) {

            // Trim stars and horiz. whitespace from left of string and spaces from right.
            $line = rtrim(ltrim($line, " *\t"));

            // Process each @property line from the docblocs.
            if (preg_match('{@property \h+ (\S+) \h+ \$(\S+) (?:\h+ (.+))? $}x', $line, $matches)) {
                $type = $matches[1];
                $attr = $matches[2];
                $desc = isset($matches[3]) ? $matches[3] : '';

                // A property that is a safe attribute is a query param. Others are responses.
                if (in_array($attr, $safeAttrs)) {
                    // A query param is either required or optional.
                    $req = $this->isAttributeRequired($attr) ? 'req' : 'opt';
                    $line = "@param $attr $type ($req) $desc";
                } else {
                    // Response fields are marked as outputs.
                    $line = "@field $attr $type (out) $desc";
                }
            }

            // Process each @see line from the docblocs. If it's not an external then it's
            // assumed to be just a method name at the same location as the current request.
            if (preg_match('{@link \h+ (\S+) (?:\h*)(\S.*)? $}x', $line, $matches)) {
                $line = $this->seeLink(
                    $matches[1],
                    null,
                    isset($matches[2]) ? $matches[2] : null
                );
            }
        }
        unset($line);

        // Next, copy the lines into $this->messages, concatenating each multi-line
        // paragraph into one message string, and removing blank lines.
        $message = '';
        foreach ($lines as $line) {
            if ($message && (!$line || $line[0] === '@')) {

                // Flush message buffer to messages array.
                $this->messages[] = $message;

                // Reset message buffer.
                $message = $line;
            } elseif ($line) {

                // Append line to message buffer.
                $message .= $message ? ' ' . $line : $line;
            }
        }
        if ($message) {
            // Flush last message buffer to messages array.
            $this->messages[] = $message;
        }
    }

    /**
     * @param string $url
     * @param array|null $params
     * @param string $text
     * @return string
     */
    public function seeLink($url = null, $params = array(), $text = '')
    {
        if (!preg_match('{^(?:https?)?//}', $url)) {
            $app = Yii::app();
            if ($url === null) {
                $url = 'api/' . $this->method;
            } elseif (strpos($url, '/') === false) {
                $url = 'api/' . $url;
            }
            if ($url[0] === '/') {
                $url = $app->baseUrl . $url;
            } else {
                $url = $app->createUrl($url, (array) $params);
            }
            $url = $app->getRequest()->hostInfo . rtrim($url, '/');
        }
        return trim("@see $url $text");
    }
}
