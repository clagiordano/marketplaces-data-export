<?php

namespace clagiordano\MarketplacesDataExport;

use PHPUnit_Framework_Test;

/**
 * Class ResultPrinter
 * @package clagiordano\MarketplacesDataExport
 */
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
{
    /** @var string $previousClassName */
    protected $previousClassName = null;
    /** @var string $className */
    protected $className = null;
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
//    protected function printDefects(array $defects, $type)
//    {
//        $count = count($defects);
//        if ($count == 0) {
//            return;
//        }
//        $i = 1;
//        foreach ($defects as $defect) {
//            $this->printDefect($defect, $i++);
//        }
//    }

    /**
     * {@inheritdoc}
     */
    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->className = get_class($test);

        parent::startTest($test);
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
    protected function getTestHeader()
    {
        $output = "";
        if ($this->previousClassName !== $this->className) {
            $output .= "\n";
            $output .= "\033[01;36m" . $this->className . "\033[0m" . ":\n";
            $this->previousClassName = $this->className;
        }

        return $output;
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

    /**
     * @param string $progress
     */
    protected function writeProgress($progress)
    {
        print $this->getTestHeader();
        $this->testStatus = $this->getStatusText($progress);
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
}