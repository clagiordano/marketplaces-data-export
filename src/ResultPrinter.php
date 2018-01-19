<?php

namespace clagiordano\MarketplacesDataExport;

use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;

/**
 * Class ResultPrinter
 * @package clagiordano\MarketplacesDataExport
 */
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
{
    /** @var float $executionTime */
    protected $executionTime = 9990.00;
    /** @var string $testStatus */
    protected $testStatus = null;

    /**
     * @param \PHPUnit_Framework_TestResult $result
     */
    public function printResult(\PHPUnit_Framework_TestResult $result)
    {
        $this->printFooter($result);
    }

    /**
     * {@inheritdoc}
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $this->executionTime = $time;

        parent::endTest($test, $time);

        $this->printProgress();
    }

    /**
     *
     */
    protected function printProgress()
    {
        printf(
            "  %5d %s (%.3fs)\n",
            0,
            $this->testStatus,
            $this->executionTime
        );
    }

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        print "\n\033[01;36m" . $suite->getName() . "\033[0m" . ":\n";

        parent::startTestSuite($suite);
    }

    /**
     * @param string $progress
     */
    protected function writeProgress($progress)
    {
        $this->testStatus = $this->getStatusText($progress);
    }

    /**
     * @param string $progress
     * @return string
     */
    protected function getStatusText($progress)
    {
        switch ($progress) {
            /**
             * Success
             */
            case '.':
                $status = "\033[01;32m" . mb_convert_encoding("\x27\x14", 'UTF-8', 'UTF-16BE') . "\033[0m";
                break;

            /**
             * Failed
             */
            case 'F':
            case "\033[41;37mF\033[0m":
                $status = "\033[01;31m" . mb_convert_encoding("\x27\x16", 'UTF-8', 'UTF-16BE') . "\033[0m";
                break;

            /**
             * Other cases
             */
            default:
                $status = $progress;
        }

        return $status;
    }
}