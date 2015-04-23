<?php

namespace Oxrun\Helper;

/**
 * Class ClassExtractor
 * @package Oxrun\Helper
 */
class ClassExtractor extends \FilterIterator
{

    protected $allowedExtensions = array(
        'php',
    );

    public function __construct($path)
    {
        parent::__construct(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::SELF_FIRST
            )
        );
    }

    public function accept()
    {
        if ($this->current()->isDir()) {
            return false;
        }

        if (realpath(__FILE__) == realpath($this->current()->getPathName())) {
            return false;
        }

        if (!is_readable($this->current()->getPathName())) {
            return false;
        }

        if (!in_array(pathinfo($this->current()->getFileName(), PATHINFO_EXTENSION), $this->allowedExtensions)) {
            return false;
        }

        if (strpos(pathinfo($this->current()->getFileName(), PATHINFO_BASENAME), 'ox') !== 0) {
            return false;
        }

        return true;
    }

}