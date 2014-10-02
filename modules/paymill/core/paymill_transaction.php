<?php

/**
 * paymill_fastcheckout
 *
 * @author     Copyright (c) 2013 PayIntelligent GmbH (http://www.payintelligent.de)
 * @copyright  Copyright (c) 2013 Paymill GmbH (https://www.paymill.com)
 */
class paymill_transaction extends oxBase
{
    /**
     * Current class name
     *
     * @var string
     */
    protected $_sClassName = 'paymill_transaction';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->init($this->_sClassName);
    }
}
