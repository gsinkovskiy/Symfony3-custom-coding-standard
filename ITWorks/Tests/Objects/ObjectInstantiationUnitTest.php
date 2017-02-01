<?php

/**
 * This file is part of the ITWorks-coding-standard (phpcs standard)
 *
 * PHP version 5
 *
 * @category PHP
 * @package  ITWorks-coding-standard
 * @author   Authors <ITWorks-coding-standard@escapestudios.github.com>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @link     https://github.com/escapestudios/ITWorks-coding-standard
 */

/**
 * Unit test class for the ObjectInstantiation sniff.
 *
 * A sniff unit test checks a .inc file for expected violations of a single
 * coding standard. Expected errors and warnings are stored in this class.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  ITWorks-coding-standard
 * @author   Authors <ITWorks-coding-standard@escapestudios.github.com>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @link     https://github.com/escapestudios/ITWorks-coding-standard
 */
class ITWorks_Tests_Objects_ObjectInstantiationUnitTest
    extends AbstractSniffUnitTest
{
    /**
     * Returns the lines where errors should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array<int, int>
     */
    public function getErrorList()
    {
        return array(
            4  => 1,
            5  => 1,
            6  => 1,
            7  => 1,
            8  => 1,
            9  => 1,
            10 => 1,
        );
    }

    /**
     * Returns the lines where warnings should occur.
     *
     * The key of the array should represent the line number and the value
     * should represent the number of errors that should occur on that line.
     *
     * @return array(int => int)
     */
    public function getWarningList()
    {
        return array();
    }
}
