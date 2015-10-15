<?php
/**
 * @author    Andrew Coulton <andrew@ingenerator.com>
 * @licence   BSD-3-Clause
 */

namespace Ingenerator\BehatTableAssert\TableParser\HTML;


use Behat\Gherkin\Node\TableNode;
use Ingenerator\BehatTableAssert\TableNode\PaddedTableNode;
use LibXMLError;

/**
 * Parses an HTML string for a <table> element into a TableNode. The table must have a single row
 * in the <thead> and a <tbody> element (though this does not have to contain any rows). You can
 * skip additional rows in the table by marking up like <tr data-behat-table="ignore">
 *
 * @package Ingenerator\BehatTableAssert\TableParser\HTML
 */
class HTMLStringTableParser
{

    /**
     * @param string $html
     *
     * @return \Ingenerator\BehatTableAssert\TableNode\PaddedTableNode
     */
    public function parse($html)
    {
        if ( ! (is_string($html) && $html)) {
            throw new \InvalidArgumentException('Expected an HTML string');
        }

        $html_table = $this->parseHTMLString($html);

        if ($html_table->getName() !== 'table') {
            throw new \InvalidArgumentException(
                'Expected a <table> but got '.$html_table->getName()
            );
        }

        $rows = $this->parseTable($html_table);

        return new PaddedTableNode($rows);
    }

    /**
     * @param string $html
     *
     * @return \SimpleXMLElement
     */
    protected function parseHTMLString($html)
    {
        $old_use_internal_errors = libxml_use_internal_errors(TRUE);
        try {
            $table = simplexml_load_string($html);
            if ($errors = libxml_get_errors()) {
                $this->throwInvalidHTMLException($html, $errors);
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($old_use_internal_errors);

        }

        return $table;
    }

    /**
     * @param string        $html
     * @param LibXMLError[] $errors
     */
    protected function throwInvalidHTMLException($html, $errors)
    {
        $msg = 'Invalid HTML string:';
        foreach ($errors as $error) {
            $msg .= strtr(
                "\n #code:message (@line:column)",
                array_map('trim', (array) $error)
            );
        }
        $msg .= "\n\n===HTML===\n$html";
        throw new \InvalidArgumentException($msg);
    }

    /**
     * @param \SimpleXMLElement $html_table
     *
     * @return array
     */
    protected function parseTable(\SimpleXMLElement $html_table)
    {
        $header = $this->parseRows($this->requireSingleChild($html_table, 'thead'));

        if (empty($header)) {
            throw new \InvalidArgumentException('No <tr> found in <thead>');
        } elseif (count($header) > 1) {
            throw new \InvalidArgumentException(
                'Multiple <tr> found in <thead> - you can mark additional rows with data-behat-table="ignore"'
            );
        }

        $body = $this->parseRows($this->requireSingleChild($html_table, 'tbody'));

        return array_merge($header, $body);
    }

    /**
     * @param \SimpleXMLElement $html_table
     * @param string            $tag_name
     *
     * @return \SimpleXMLElement
     */
    protected function requireSingleChild(\SimpleXMLElement $html_table, $tag_name)
    {
        if ( ! $child = $html_table->$tag_name) {
            throw new \InvalidArgumentException(
                'No <'.$tag_name.'> found in <'.$html_table->getName().'>'
            );
        }

        /** @var \SimpleXmlElement $child */

        if ($child->count() > 1) {
            throw new \InvalidArgumentException(
                'Multiple <'.$tag_name.'> found in <'.$html_table->getName().'>'
            );
        }

        return $child;
    }

    /**
     * @param \SimpleXMLElement $section
     *
     * @return array
     */
    protected function parseRows(\SimpleXMLElement $section)
    {
        $rows = [];
        foreach ($section->tr as $row) {
            if ( (string) $row['data-behat-table'] === 'ignore') {
                continue;
            }

            $rows[] = $this->findChildTextValues($row);
        }
        return $rows;
    }

    /**
     * @param \SimpleXMLElement $element
     *
     * @return string[]
     */
    protected function findChildTextValues(\SimpleXMLElement $element)
    {
        $row = [];
        foreach ($element->children() as $child) {
            /** @var \SimpleXMLElement $child */
            $row[] = trim(preg_replace('/\s+/', ' ', dom_import_simplexml($child)->textContent));

            $colspan = (int) $child['colspan'];
            for ($i = 1; $i < $colspan; $i++) {
                $row[] = '...';
            }
        }

        return $row;
    }
}
