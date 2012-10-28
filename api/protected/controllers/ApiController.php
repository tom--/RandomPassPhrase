<?php

class ApiController extends TApiController
{
    public function actionPhrase()
    {
        $this->constructQuery();
        $this->doRequest();
    }
}