<?php

/**
 * Return an array of random pass-phrase.
 *
 * @property int $np Number of phrases to generate.
 * @property int $mn Minimum number of ASCII letter chars in phrase.
 * @property int $mx Maximum number of ASCII letter chars in phrase.
 * @property int $nw Number of words to use.
 * @property int $ns Number of special chars to insert into phrase.
 * @property int $nd Number of digits to append.
 */
class PhraseQuery extends TApiQuery
{
    public $np = 1;
    public $mn = 14;
    public $mx = 20;
    public $nw = 4;
    public $ns = 1;
    public $nd = 1;
    private $generator;

    public function __construct() {
        $this->generator = new PassPhrase;
    }

    public function attributeLabels()
    {
        return array(
            'np' => 'Number of phrases',
            'mn' => 'Min letters',
            'mx' => 'Max letters',
            'nw' => 'Number of words',
            'ns' => 'Number of symbols',
            'nd' => 'Number of digits',
        );
    }

    public function rules()
    {
        return array(
            array('verb', 'required'),
            array('verb', 'in', 'range' => array('GET')),
            array('np, mn, mx', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 99),
            array('ns, nd, nw', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 9),
        );
    }

    public function run()
    {
        $this->results = array();
        for ($i = 0; $i < $this->np; $i += 1) {
            $this->results[] = $this->generator->randomPassPhrase(
                $this->mn,
                $this->mn,
                $this->nw,
                $this->ns,
                $this->nd
            );
        }
        if ($this->results) {
            $this->schema = array('string passPhrase');
        }
    }
}

