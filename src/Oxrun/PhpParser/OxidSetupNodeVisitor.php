<?php

namespace Oxrun\PhpParser;

use PhpParser;
use PhpParser\Node;

/**
 * Class OxidSetupNodeVisitor
 * @package Oxrun\PhpParser
 */
class OxidSetupNodeVisitor extends \PhpParser\NodeVisitorAbstract
{

    protected $path;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param Node $node
     */
    public function leaveNode(Node $node)
    {

        if ($node instanceof PhpParser\Node\Expr\Assign) {
            if (isset($node->var->name) && $node->var->name == 'sqlDir') {
                $node->expr->value = $this->path . 'sql/';
            }
        }

        if ($node instanceof PhpParser\Node\Stmt\Return_) {
            if ($node->expr instanceof PhpParser\Node\Scalar\String_) {
                if ($node->expr->value == '../') {
                    $node->expr->value = $this->path . '../';
                }
            }
        }

        if ($node instanceof PhpParser\Node\Stmt\Class_) {
            if ($node->name == 'oxSetupController') {
                $prop = new \PhpParser\Node\Stmt\Property(
                    \PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED, array(
                        new \PhpParser\Node\Stmt\PropertyProperty('_oView')
                    )
                );
                array_unshift($node->stmts, $prop);
            }
        }

        if ($node instanceof PhpParser\Node\Expr\FuncCall) {
            if ($node->name->parts[0] == 'session_start') {
                $node->name->parts[0] = '@session_start';
            }
        }

    }
}